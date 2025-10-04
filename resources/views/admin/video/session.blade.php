@extends('layouts.admin')

@section('content')
<div class="container">
    <h4>Video Session (Booking #{{ $booking->id }})</h4>
    <div id="jitsi-container" style="height: 80vh; width: 100%; border: 1px solid #ccc;"></div>
</div>
 <div class="card mb-3">
        <div class="card-body">
            <p><strong>Booking ID:</strong> {{ $booking->id ?? 'N/A' }}</p>
            <p><strong>Booking Counselor_id:</strong> {{ $booking->counselor_id ?? 'N/A' }} (logged-in: {{ auth()->id() }})</p>
            <p><strong>Booking status:</strong> {{ $booking->status ?? 'N/A' }}</p>
            <p><strong>Video session exists:</strong> {{ $booking->videoSession ? 'yes' : 'no' }}</p>
            @if($booking->videoSession)
                <p><strong>Video session status:</strong> {{ $booking->videoSession->status }}</p>
                <p><strong>Room name:</strong> {{ $booking->videoSession->room_name }}</p>
            @endif
        </div>
    </div>

<script src='https://jitsi.local:444/external_api.js'></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const domain = "jitsi.local:444"; // your local Jitsi
        const options = {
            roomName: "booking-{{ $booking->id }}", // unique per session
            width: "100%",
            height: "100%",
            parentNode: document.querySelector('#jitsi-container'),
            userInfo: {
                email: "{{ Auth::user()->email }}",
                displayName: "{{ Auth::user()->name }}"
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    });
</script>
@endsection
