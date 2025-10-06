<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BookingRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;


class StudentBookingController extends Controller
{
    public function index()
    {
        $counselors = User::where('role', 'admin')->get();

        // check if the student already has a pending/approved booking
        $hasBooking = BookingRequest::where('student_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        return view('student.booking.counselors', compact('counselors', 'hasBooking'));
    }

    public function create($counselor_id)
    {
        // prevent opening booking form if student already has pending/approved booking
        $hasBooking = BookingRequest::where('student_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasBooking) {
            return redirect()->route('student.booking.counselors')
                ->with('error', 'You already have an active booking. Please complete or cancel it first.');
        }

        $counselor = User::findOrFail($counselor_id);
        return view('student.booking.form', compact('counselor'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course' => 'required',
            'age' => 'required|integer',
            'sex' => 'required',
            'address' => 'required',
            'contact' => 'required',
            'civil_status' => 'required',
            'emergency_name' => 'required',
            'emergency_address' => 'required',
            'emergency_relationship' => 'required',
            'emergency_contact' => 'required',
            'emergency_occupation' => 'required',
            'reason' => 'required',
            'preferred_time' => 'required|date',
            'preference' => 'required|in:online,face-to-face',
        ]);

        // ✅ Prevent double booking
        $hasBooking = BookingRequest::where('student_id', auth()->id())
            ->whereIn('status', ['pending','approved'])
            ->exists();

        if ($hasBooking) {
            return redirect()->back()->with('alreadyBooked', true)->withInput();
        }

        $preferred = \Carbon\Carbon::parse($request->preferred_time);
        $date = $preferred->toDateString();
        $time = $preferred->format('H:i:s');

        // 🔹 check conflicts
        $eventConflict = \App\Models\CalendarEvent::where('user_id', $request->counselor_id)
            ->whereDate('date', $date)
            ->where(function ($q) use ($time) {
                $q->where('start_time', '<=', $time)
                  ->where('end_time', '>=', $time);
            })
            ->exists();

        if ($eventConflict) {
            return redirect()->back()->with('error', 'This time conflicts with the counselor’s schedule.')->withInput();
        }

        $dayOfWeek = $preferred->format('l');
        $recurringConflict = \App\Models\WeeklySchedule::where('user_id', $request->counselor_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->exists();

        if ($recurringConflict) {
            return redirect()->back()->with('error', 'This time conflicts with the counselor’s recurring schedule.')->withInput();
        }

        // ✅ Save booking
        BookingRequest::create([
            'student_id' => auth()->id(),
            'counselor_id' => $request->counselor_id,
            'course' => $request->course,
            'age' => $request->age,
            'sex' => $request->sex,
            'address' => $request->address,
            'contact' => $request->contact,
            'civil_status' => $request->civil_status,
            'emergency_name' => $request->emergency_name,
            'emergency_address' => $request->emergency_address,
            'emergency_relationship' => $request->emergency_relationship,
            'emergency_contact' => $request->emergency_contact,
            'emergency_occupation' => $request->emergency_occupation,
            'reason' => $request->reason,
            'preference' => $request->preference,
            'preferred_time' => $request->preferred_time,
            'status' => 'pending',
        ]);

        return redirect()->route('student.dashboard')->with('success', 'Booking request submitted.');
    }

    public function getSchedule($counselor_id, $date)
    {
        $events = \App\Models\CalendarEvent::where('user_id', $counselor_id)
            ->whereDate('date', $date)
            ->get(['title','type','start_time','end_time']);

        $recurring = \App\Models\WeeklySchedule::where('user_id', $counselor_id)
            ->where('day_of_week', \Carbon\Carbon::parse($date)->format('l'))
            ->get(['title','type','start_time','end_time','day_of_week']);

        $bookings = BookingRequest::where('counselor_id', $counselor_id)
            ->whereDate('preferred_time', $date)
            ->whereIn('status',['pending','approved'])
            ->get(['preferred_time','reason']);

        return response()->json([
            'events' => $events,
            'recurring' => $recurring,
            'bookings' => $bookings,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'reason' => 'required|string|max:255',
        ]);

        $booking = BookingRequest::where('id', $id)
            ->where('student_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $preferred = \Carbon\Carbon::parse($request->date . ' ' . $request->time);
        $date = $preferred->toDateString();
        $time = $preferred->format('H:i:s');

        // 🔹 conflict checks
        $eventConflict = \App\Models\CalendarEvent::where('user_id', $booking->counselor_id)
            ->whereDate('date', $date)
            ->where(function ($q) use ($time) {
                $q->where('start_time', '<=', $time)
                  ->where('end_time', '>=', $time);
            })
            ->exists();

        if ($eventConflict) {
            return redirect()->back()->withErrors(['preferred_time' => 'This time conflicts with the counselor’s schedule.']);
        }

        $dayOfWeek = $preferred->format('l');
        $recurringConflict = \App\Models\WeeklySchedule::where('user_id', $booking->counselor_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->exists();

        if ($recurringConflict) {
            return redirect()->back()->withErrors(['preferred_time' => 'This time conflicts with the counselor’s recurring schedule.']);
        }

        $existingBooking = BookingRequest::where('counselor_id', $booking->counselor_id)
            ->whereDate('preferred_time', $date)
            ->whereTime('preferred_time', $time)
            ->whereIn('status', ['pending','approved'])
            ->where('id', '!=', $booking->id)
            ->exists();

        if ($existingBooking) {
            return redirect()->back()->withErrors(['preferred_time' => 'This time slot is already taken.']);
        }

        // 🔹 Update
        $booking->preferred_time = $preferred;
        $booking->reason = $request->reason;
        $booking->save();

        return redirect()->route('student.schedule.index')->with('success', 'Booking updated successfully.');
    }

    public function cancel($id)
    {
        $booking = BookingRequest::where('id', $id)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        // allow cancel only if booking is pending
        if ($booking->status === 'pending') {
            $booking->status = 'cancelled';
            $booking->save();
            return redirect()->route('student.schedule.index')
                ->with('success', 'Booking cancelled successfully.');
        }

        return redirect()->route('student.schedule.index')
            ->with('error', 'You can only cancel pending bookings.');
    }

  /**
 * Student accepts a reschedule suggested by counselor/admin
 */
public function acceptReschedule($id)
{
    $booking = BookingRequest::where('id', $id)
        ->where('student_id', auth()->id())
        ->where('reschedule_status', 'requested')
        ->firstOrFail();

    // Apply rescheduled time
    $booking->preferred_time = $booking->rescheduled_time;
    $booking->rescheduled_time = null;
    $booking->reschedule_status = 'accepted';
    $booking->reschedule_attempts = 0;
    $booking->reschedule_reason = null;
    $booking->status = 'approved';
    $booking->save();

    return redirect()->route('student.schedule.index')->with('success', 'You accepted the new schedule.');
}

/**
 * Student declines the reschedule (must provide reason)
 */
public function declineReschedule(Request $request, $id)
{
    $request->validate([
        'reason' => 'required|string|max:500',
    ]);

    $booking = BookingRequest::where('id', $id)
        ->where('student_id', auth()->id())
        ->where('reschedule_status', 'requested')
        ->firstOrFail();

    $booking->reschedule_status = 'declined';
    $booking->reschedule_reason = $request->reason;
    $booking->save();

    // If attempts reached >=3 then cancel the booking automatically
    if ($booking->reschedule_attempts >= 3) {
        $booking->status = 'cancelled';
        $booking->save();
        return redirect()->route('student.schedule.index')->with('error', 'Reschedule declined. This booking has been cancelled after multiple reschedule attempts. Please book again.');
    }

    return redirect()->route('student.schedule.index')->with('error', 'Reschedule declined. The counselor can propose another time.');
}

public function counterOffer(Request $request, $id)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required',
        'reason' => 'required|string|max:255'
    ]);

    $booking = BookingRequest::where('id', $id)
        ->where('student_id', auth()->id())
        ->firstOrFail();

    $booking->rescheduled_time = $request->date . ' ' . $request->time;
    $booking->reschedule_status = 'counter';
    $booking->reschedule_reason = $request->reason;
    $booking->save();

    return back()->with('success', 'Counter-offer sent to counselor.');
}

}
