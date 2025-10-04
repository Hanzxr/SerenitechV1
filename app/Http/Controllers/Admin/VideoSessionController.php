<?php
namespace App\Http\Controllers\Admin;

use App\Models\BookingRequest;
use App\Models\VideoSession;
use App\Services\JitsiTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class VideoSessionController extends Controller
{
    protected JitsiTokenService $jitsi;

    public function __construct(JitsiTokenService $jitsi)
    {
        $this->jitsi = $jitsi;
    }

    // Admin (counselor) starts session
    public function start($bookingId)
    {
        $booking = BookingRequest::findOrFail($bookingId);

        // ensure current user is the assigned counselor (or admin)
        if ($booking->counselor_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'approved') {
            return back()->with('error', 'Booking is not approved yet.');
        }

        // Create or reuse existing video session
        $session = VideoSession::firstOrCreate(
            ['booking_id' => $booking->id],
            ['room_name' => 'booking-' . $booking->id . '-' . Str::random(6), 'status' => 'ongoing']
        );

        $session->status = 'ongoing';
        $session->save();

        // generate moderator token for counselor
        $token = $this->jitsi->generateToken($session->room_name, [
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'moderator' => true,
        ]);

        return view('admin.video.session', compact('session', 'token', 'booking'));
    }

    // Student joins session
    public function join($bookingId)
    {
        $session = VideoSession::where('booking_id', $bookingId)->where('status', 'ongoing')->firstOrFail();
        $booking = $session->booking;

        // ensure logged-in student owns the booking
        if ($booking->student_id !== Auth::id()) {
            abort(403);
        }

        $token = $this->jitsi->generateToken($session->room_name, [
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'moderator' => false,
        ]);

        return view('student.video.session', compact('session','token','booking'));
    }

    // Optionally, end session
    public function end($id)
    {
        $session = VideoSession::findOrFail($id);
        // ensure auth checks...
        $session->status = 'ended';
        $session->save();
        return redirect()->back()->with('success','Session ended.');
    }
}
