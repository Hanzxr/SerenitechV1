<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoSession extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id','room_name','status'];

    public function booking()
    {
        return $this->belongsTo(\App\Models\BookingRequest::class, 'booking_id');
    }
}
