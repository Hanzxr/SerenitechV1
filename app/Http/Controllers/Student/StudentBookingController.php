<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BookingRequest;
use Illuminate\Support\Facades\Auth;

class StudentBookingController extends Controller
{
    public function index()
    {
        $counselors = User::where('role', 'admin')->get();
        return view('student.booking.counselors', compact('counselors'));
    }

    public function create($counselor_id)
    {
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
        'preference' => 'required|in:online,face-to-face', // ✅ add this
    ]);

    $existingBooking = BookingRequest::where('counselor_id', $request->counselor_id)
        ->where('preferred_time', $request->preferred_time)
        ->whereIn('status', ['pending', 'approved'])
        ->exists();

    if ($existingBooking) {
        return redirect()->back()
            ->withErrors(['preferred_time' => 'This time slot is already booked. Please choose another time.'])
            ->withInput();
    }

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
        'preference' => $request->preference, // ✅ add this
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

}
