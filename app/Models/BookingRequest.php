<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
    'student_id',
    'counselor_id',
    'course',
    'age',
    'sex',
    'address',
    'contact',
    'civil_status',
    'emergency_name',
    'emergency_address',
    'emergency_relationship',
    'emergency_contact',
    'emergency_occupation',
    'reason',
    'preference',   // ✅ add this
    'status',
    'preferred_time',
       'rescheduled_time',
    'reschedule_status',
    'reschedule_reason',
    'reschedule_attempts',
];


    // Student relationship
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id');

    }

    // Counselor relationship
    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id', 'id');
    }

    public function videoSession()
{
    return $this->hasOne(VideoSession::class, 'booking_id');
}

}
