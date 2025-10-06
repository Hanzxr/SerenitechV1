<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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


    // Admin initiates reschedule
public function requestReschedule(Request $request, $id)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required'
    ]);

    $booking = BookingRequest::findOrFail($id);

    // can only reschedule pending/approved/rejected
    if (!in_array($booking->status, ['pending','approved','rejected'])) {
        return back()->with('error','This booking cannot be rescheduled.');
    }

    $booking->rescheduled_time = $request->date.' '.$request->time;
    $booking->reschedule_status = 'requested';
    $booking->status = 'pending'; // freeze until student responds
    $booking->save();

    return back()->with('success','Reschedule request sent to student.');
}

// Student accepts reschedule
public function acceptReschedule($id)
{
    $booking = BookingRequest::where('id',$id)
                ->where('student_id',auth()->id())
                ->firstOrFail();

    $booking->preferred_time = $booking->rescheduled_time;
    $booking->rescheduled_time = null;
    $booking->reschedule_status = 'accepted';
    $booking->status = 'approved';
    $booking->save();

    return back()->with('success','Reschedule accepted.');
}

// Student declines reschedule
public function declineReschedule(Request $request, $id)
{
    $request->validate([
        'reason' => 'required|string|max:255'
    ]);

    $booking = BookingRequest::where('id',$id)
                ->where('student_id',auth()->id())
                ->firstOrFail();

    $booking->reschedule_status = 'declined';
    $booking->reschedule_reason = $request->reason;
    $booking->save();

    return back()->with('error','Reschedule declined, reason saved.');
}

// Admin rebooks after rejection
public function rebook(Request $request, $id)
{
    $request->validate([
        'date' => 'required|date',
        'time' => 'required'
    ]);

    $oldBooking = BookingRequest::findOrFail($id);

    // only allowed if old was rejected
    if ($oldBooking->status !== 'rejected') {
        return back()->with('error','Only rejected bookings can be rebooked.');
    }

    BookingRequest::create([
        'student_id' => $oldBooking->student_id,
        'counselor_id' => auth()->id(),
        'preferred_time' => $request->date.' '.$request->time,
        'reason' => $oldBooking->reason,
        'status' => 'pending'
    ]);

    return back()->with('success','Booking rebooked successfully.');
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
