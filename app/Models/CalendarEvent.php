<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'type',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
