<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingRequest;

class ScheduleController extends Controller
{
    public function index()
    {
        $studentPsuId = Auth::user()->id; // or student_id depending on your schema

        $bookings = BookingRequest::where('student_id', $studentPsuId)
                    ->orderBy('preferred_time', 'desc')
                    ->get();

        return view('student.schedule.index', compact('bookings'));
    }

}
