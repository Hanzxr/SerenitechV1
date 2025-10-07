<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\User;
use App\Models\CalendarEvent;
use App\Models\WeeklySchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        $preferred = Carbon::parse($request->date . ' ' . $request->time);

        BookingRequest::create([
            'student_id' => $request->student_id,
            'counselor_id' => auth()->id(),
            'preferred_time' => $preferred,
            'reason' => $request->type,
            'status' => 'approved', // auto-approved follow-up
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Follow-up scheduled.');
    }

    /**
     * Admin initiates reschedule for an existing booking:
     * - Saves rescheduled_time and marks reschedule_status as 'requested'
     * - Freezes booking status to 'pending' until student responds
     * - Uses $now to prevent proposing past times
     */
    public function requestReschedule(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'force' => 'nullable|boolean',
        ]);

        $booking = BookingRequest::findOrFail($id);

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cannot reschedule a cancelled booking.');
        }

        $rescheduled = Carbon::parse($request->date . ' ' . $request->time);
        $now = Carbon::now();

        if ($rescheduled->lte($now)) {
            return back()->with('error', 'Cannot propose a reschedule to a past date/time.');
        }

        // check conflict with admin calendar (non-blocking) — admin can use 'force' to override
        $timeToCheck = $rescheduled->format('H:i:s');
        $conflict = CalendarEvent::where('user_id', auth()->id())
            ->whereDate('date', $rescheduled->toDateString())
            ->where(function($q) use ($timeToCheck) {
                $q->where(function($s) use ($timeToCheck) {
                    $s->whereNotNull('start_time')
                      ->where('start_time', '<=', $timeToCheck)
                      ->where('end_time', '>=', $timeToCheck);
                })->orWhereNull('start_time'); // treat null as all-day/unavailable
            })->exists();

        // persist reschedule request
        $booking->rescheduled_time = $rescheduled;
        $booking->reschedule_status = 'requested';
        $booking->reschedule_reason = null;
        // freeze current status until student responds
        $booking->status = 'pending';
        $booking->save();

        $msg = 'Reschedule request sent to student.';
        if ($conflict && !$request->boolean('force')) {
            $msg .= ' (Conflict detected on your calendar — use force to override.)';
        }

        // TODO: send notification/email to student here.

        return back()->with('success', $msg);
    }

    /**
     * Admin can rebook a previously rejected booking: create a new pending booking record for the same student.
     */
    public function rebook(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
        ]);

        $oldBooking = BookingRequest::findOrFail($id);

        if ($oldBooking->status !== 'rejected') {
            return back()->with('error', 'Only rejected bookings can be rebooked by this flow.');
        }

        $preferred = Carbon::parse($request->date . ' ' . $request->time);

        BookingRequest::create([
            'student_id'    => $oldBooking->student_id,
            'counselor_id'  => $oldBooking->counselor_id ?? auth()->id(),
            'course'        => $oldBooking->course,
            'age'           => $oldBooking->age,
            'sex'           => $oldBooking->sex,
            'address'       => $oldBooking->address,
            'contact'       => $oldBooking->contact,
            'civil_status'  => $oldBooking->civil_status,
            'emergency_name'=> $oldBooking->emergency_name,
            'emergency_address'=> $oldBooking->emergency_address,
            'emergency_relationship'=> $oldBooking->emergency_relationship,
            'emergency_contact' => $oldBooking->emergency_contact,
            'emergency_occupation'=> $oldBooking->emergency_occupation,
            'reason'        => $oldBooking->reason,
            'preference'    => $oldBooking->preference,
            'preferred_time'=> $preferred,
            'status'        => 'pending',
            'reschedule_attempts' => 0
        ]);

        return back()->with('success', 'Booking rebooked successfully (pending student action).');
    }

    /**
     * Return admin's schedule for a particular date (used to preview conflicts).
     * Note: route should call this with a date param.
     */
    public function getSchedule($date)
    {
        // normalize date string
        $dateObj = Carbon::parse($date);
        $dayName = $dateObj->format('l');

        // Admin's own calendar events (auth()->id())
        $events = CalendarEvent::where('user_id', auth()->id())
            ->whereDate('date', $dateObj->toDateString())
            ->get(['id','title','date','start_time','end_time','notes']);

        // Weekly recurring tasks for that day
        $recurring = WeeklySchedule::where('user_id', auth()->id())
            ->where('day_of_week', $dayName)
            ->get(['id','title','start_time','end_time','day_of_week','type']);

        // Bookings that are pending/approved on that day for the admin (to show occupancy)
        $bookings = BookingRequest::where('counselor_id', auth()->id())
            ->whereDate('preferred_time', $dateObj->toDateString())
            ->whereIn('status', ['pending','approved'])
            ->get(['id','student_id','preferred_time','status']);

        return response()->json([
            'events' => $events,
            'recurring' => $recurring,
            'bookings' => $bookings,
        ]);
    }
}
