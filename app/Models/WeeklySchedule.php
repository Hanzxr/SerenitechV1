<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'day_of_week',
        'start_time',
        'end_time',
        'type',
    ];
}
