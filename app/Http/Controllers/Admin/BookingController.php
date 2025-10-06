<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index()
    {
        $requests = BookingRequest::with('student')
            ->orderBy('preferred_time')
            ->get();

        return view('admin.bookings.index', compact('requests'));
    }

    public function show($id)
    {
        $booking = BookingRequest::with('student')->findOrFail($id);
        $student = $booking->student;

        // show only upcoming approved requests (excluding current one)
        $upcomingRequests = BookingRequest::where('student_id', $student->id)
            ->where('id', '!=', $booking->id)
            ->where('status', 'approved')
            ->orderBy('preferred_time')
            ->get();

        return view('admin.bookings.show', compact('student', 'booking', 'upcomingRequests'));
    }

  public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,approved,rejected',
    ]);

    $booking = BookingRequest::findOrFail($id);

    // Prevent editing if student already cancelled
    if ($booking->status === 'cancelled') {
        return back()->with('error', 'This booking was cancelled by the student.');
    }

    $booking->status = $request->input('status');
    $booking->save();

    return back()->with('success', 'Booking status updated to ' . ucfirst($request->status));
}


    public function saveNotes(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $booking = BookingRequest::findOrFail($id);

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cannot add notes to a cancelled booking.');
        }

        $booking->notes = $request->notes;
        $booking->save();

        return back()->with('success', 'Session notes saved.');
    }

    public function scheduleFollowUp(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time' => 'required',
            'type' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        BookingRequest::create([
            'student_id' => $request->student_id,
            'counselor_id' => auth()->id(),
            'preferred_time' => $request->date . ' ' . $request->time,
            'reason' => $request->type,
            'status' => 'approved', // auto-approved follow-up
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Follow-up scheduled.');
    }


   /**
 * Admin requests a reschedule for a booking.
 * This sets rescheduled_time, marks reschedule_status=requested and sets booking->status = pending
 * Admin can re-request while status=requested (it updates rescheduled_time and increments attempt count).
 */
public function requestReschedule(Request $request, $id)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required',
    ]);

    $booking = BookingRequest::findOrFail($id);

    if ($booking->status === 'cancelled') {
        return back()->with('error', 'Cannot reschedule a cancelled booking.');
    }

    // Build datetime string
    $resched = Carbon::parse($request->date . ' ' . $request->time);

    // If already requested, increment attempts (but not beyond 3)
    if ($booking->reschedule_status === 'requested') {
        if ($booking->reschedule_attempts >= 2) {
            // third request would be attempt 3
            $booking->reschedule_attempts++;
            // If it reaches 3 attempts, we will mark canceled on next student decline (handled at student side)
        } else {
            $booking->reschedule_attempts++;
        }
    } else {
        $booking->reschedule_attempts = 1;
    }

    $booking->rescheduled_time = $resched;
    $booking->reschedule_status = 'requested';
    // Freeze current status to pending so student must accept or decline
    $booking->status = 'pending';
    $booking->save();

    return back()->with('success', 'Reschedule requested — student will be notified.');
}

/**
 * Admin rebooks (create new booking for that student).
 * This bypasses event/conflict checks because admin intentionally overrides.
 */
public function rebook(Request $request, $id)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required',
    ]);

    $oldBooking = BookingRequest::findOrFail($id);

    if ($oldBooking->status !== 'rejected') {
        return back()->with('error', 'Only rejected bookings can be rebooked.');
    }

    // Create a new booking for the student with counselor = current admin
    $preferred = Carbon::parse($request->date . ' ' . $request->time);

    $new = BookingRequest::create([
        'student_id' => $oldBooking->student_id,
        'counselor_id' => auth()->id(),
        'preferred_time' => $preferred,
        'reason' => $oldBooking->reason,
        'status' => 'pending',
        // copy other optional student details if you want:
        'course' => $oldBooking->course,
        'age' => $oldBooking->age,
        'sex' => $oldBooking->sex,
        'address' => $oldBooking->address,
        'contact' => $oldBooking->contact,
        'civil_status' => $oldBooking->civil_status,
        'emergency_name' => $oldBooking->emergency_name,
        'emergency_address' => $oldBooking->emergency_address,
        'emergency_relationship' => $oldBooking->emergency_relationship,
        'emergency_contact' => $oldBooking->emergency_contact,
        'emergency_occupation' => $oldBooking->emergency_occupation,
    ]);

    return back()->with('success', 'Booking rebooked for student.');
}

// BookingController.php
public function getSchedule($date)
{
    $events = DB::table('events')
        ->whereDate('date', $date)
        ->get();

    $recurring = DB::table('recurring_tasks')
        ->where('day_of_week', date('l', strtotime($date)))
        ->get();

    $bookings = BookingRequest::whereDate('preferred_time', $date)
        ->where('status', 'approved')
        ->get();

    return response()->json([
        'events' => $events,
        'recurring' => $recurring,
        'bookings' => $bookings,
    ]);
}

}
