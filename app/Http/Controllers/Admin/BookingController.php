<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $requests = BookingRequest::with('student')->orderBy('preferred_time')->get();
        return view('admin.bookings.index', compact('requests'));
    }

    public function show($id)
    {

        $booking = BookingRequest::with('student')->findOrFail($id);
        $student = $booking->student;
        $upcomingRequests = BookingRequest::where('student_id', $student->id)
                                ->where('status', 'approved')
                                ->orderBy('preferred_time')->get();
        return view('admin.bookings.show', compact('student', 'booking', 'upcomingRequests'));
    }

    public function updateStatus(Request $request, $id)
    {

         $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $booking = BookingRequest::findOrFail($id);
        $booking->status = $request->input('status');
        $booking->save();

        return back()->with('success', 'Booking status updated.');
    }

    public function saveNotes(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        $booking = BookingRequest::findOrFail($id);
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
            'type' => 'required',
            'notes' => 'nullable',
        ]);

        BookingRequest::create([
            'student_id' => $request->student_id,
            'counselor_id' => auth()->id(),
            'preferred_time' => $request->date . ' ' . $request->time,
            'reason' => $request->type,
            'status' => 'approved',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Follow-up scheduled.');
    }
}
