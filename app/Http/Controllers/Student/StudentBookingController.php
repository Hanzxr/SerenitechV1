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

    // parse preferred time (Carbon, app timezone)
    $preferred = Carbon::parse($request->preferred_time);
    $now = Carbon::now();

    // 1) No booking in the past: disallow preferred_time <= now
    if ($preferred->lte($now)) {
        return redirect()->back()
            ->withErrors(['preferred_time' => 'You cannot book a time in the past. Please choose a future time.'])
            ->withInput();
    }

    $date = $preferred->toDateString();       // YYYY-MM-DD
    $time = $preferred->format('H:i:s');      // HH:MM:SS

    // 2) Prevent student double-booking (existing student booking pending/approved)
    $hasBooking = BookingRequest::where('student_id', auth()->id())
        ->whereIn('status', ['pending','approved'])
        ->exists();

    if ($hasBooking) {
        return redirect()->back()->with('alreadyBooked', true)->withInput();
    }

    // 3) Check calendar events conflict (allow booking at event end time)
    $eventConflict = \App\Models\CalendarEvent::where('user_id', $request->counselor_id)
        ->whereDate('date', $date)
        ->where(function ($q) use ($time) {
            // conflict if start_time <= time < end_time OR whole-day (null start_time -> treat as full day)
            $q->where(function ($sub) use ($time) {
                $sub->where('start_time', '<=', $time)
                    ->where('end_time', '>', $time);
            })->orWhereNull('start_time');
        })
        ->exists();

    if ($eventConflict) {
        return redirect()->back()->withErrors(['preferred_time' => 'This time conflicts with the counselor’s schedule (event/unavailable).'])->withInput();
    }

    // 4) Check recurring tasks conflict (allow booking at the recurring end time)
    $dayOfWeek = $preferred->format('l'); // e.g. Monday
    $recurringConflict = \App\Models\WeeklySchedule::where('user_id', $request->counselor_id)
        ->where('day_of_week', $dayOfWeek)
        ->where('start_time', '<=', $time)
        ->where('end_time', '>', $time)   // strictly greater (so booking at end_time is allowed)
        ->exists();

    if ($recurringConflict) {
        return redirect()->back()->withErrors(['preferred_time' => 'This time conflicts with the counselor’s recurring schedule.'])->withInput();
    }

    // 5) Optional: ensure no identical booking exists (exact duplicate)
    $existingBooking = BookingRequest::where('counselor_id', $request->counselor_id)
        ->where('preferred_time', $request->preferred_time)
        ->whereIn('status', ['pending','approved'])
        ->exists();

    if ($existingBooking) {
        return redirect()->back()->withErrors(['preferred_time' => 'This exact time slot is already booked.'])->withInput();
    }

    // Save booking (unchanged)
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
// Student updates their own booking (only if pending)
   public function update(Request $request, $id)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required',
        'reason' => 'required|string|max:255',
    ]);

    $booking = BookingRequest::where('id', $id)
        ->where('student_id', auth()->id())
        ->whereIn('status', ['pending']) // only pending editable
        ->firstOrFail();

    $preferred = Carbon::parse($request->date . ' ' . $request->time);
    $now = Carbon::now();

    if ($preferred->lte($now)) {
        return redirect()->back()->withErrors(['date' => 'You cannot reschedule to a past date/time.'])->withInput();
    }

    $date = $preferred->toDateString();
    $time = $preferred->format('H:i:s');

    // check event conflict (allow exact end time)
    $eventConflict = \App\Models\CalendarEvent::where('user_id', $booking->counselor_id)
        ->whereDate('date', $date)
        ->where(function ($q) use ($time) {
            $q->where(function ($sub) use ($time) {
                $sub->where('start_time', '<=', $time)
                    ->where('end_time', '>', $time);
            })->orWhereNull('start_time');
        })
        ->exists();

    if ($eventConflict) {
        return redirect()->back()->withErrors(['preferred_time' => 'This time conflicts with the counselor’s schedule.'])->withInput();
    }

    // recurring conflict (allow booking at end_time)
    $dayOfWeek = $preferred->format('l');
    $recurringConflict = \App\Models\WeeklySchedule::where('user_id', $booking->counselor_id)
        ->where('day_of_week', $dayOfWeek)
        ->where('start_time', '<=', $time)
        ->where('end_time', '>', $time)
        ->exists();

    if ($recurringConflict) {
        return redirect()->back()->withErrors(['preferred_time' => 'This time conflicts with the counselor’s recurring schedule.'])->withInput();
    }

    // check other bookings (exclude current booking)
    $existingBooking = BookingRequest::where('counselor_id', $booking->counselor_id)
        ->where('preferred_time', $preferred)
        ->whereIn('status', ['pending','approved'])
        ->where('id', '!=', $booking->id)
        ->exists();

    if ($existingBooking) {
        return redirect()->back()->withErrors(['preferred_time' => 'This time slot is already taken.'])->withInput();
    }

    $booking->preferred_time = $preferred;
    $booking->reason = $request->reason;
    $booking->save();

    return redirect()->route('student.schedule.index')->with('success', 'Booking updated successfully.');
}

  /**
 * Student accepts a reschedule suggested by counselor/admin
 */
public function acceptReschedule($id)
{
    $booking = BookingRequest::where('id',$id)
                ->where('student_id', auth()->id())
                ->firstOrFail();

    if ($booking->reschedule_status !== 'requested') {
        return back()->with('error','No pending reschedule request found for this booking.');
    }

    // Accept: move rescheduled_time -> preferred_time
    $booking->preferred_time = $booking->rescheduled_time;
    $booking->rescheduled_time = null;
    $booking->reschedule_status = 'accepted';
    $booking->status = 'approved';
    $booking->reschedule_attempts = 0; // reset attempts on success optionally
    $booking->save();

    // notify admin/counselor if needed

    return back()->with('success','You accepted the proposed time. Booking approved.');
}

public function declineReschedule(Request $request, $id)
{
    $request->validate(['reason' => 'required|string|max:500']);

    $booking = BookingRequest::where('id',$id)
                ->where('student_id', auth()->id())
                ->firstOrFail();

    if ($booking->reschedule_status !== 'requested') {
        return back()->with('error','No pending reschedule to decline.');
    }

    // increment attempt
    $booking->reschedule_attempts = $booking->reschedule_attempts + 1;
    $booking->reschedule_status = 'declined';
    $booking->reschedule_reason = $request->reason;
    $booking->save();

    // After 3 declines -> auto-cancel
    if ($booking->reschedule_attempts >= 3) {
        $booking->status = 'cancelled';
        $booking->save();
        // notify student (pop-up) that booking is cancelled and needs new booking
        return back()->with('error','You declined the reschedule 3 times — booking auto-cancelled. Please book again.');
    }

    // notify admin/counselor about decline & reason
    return back()->with('success','Reschedule declined and reason saved. Counselor can propose again (attempt '.$booking->reschedule_attempts.'/3).');
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
