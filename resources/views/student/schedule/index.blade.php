@extends('layouts.student')

@section('content')
<div class="container">
    <h3 class="mb-4">📅 My Schedule</h3>

    @if($bookings->isEmpty())
        <div class="alert alert-info text-center">
            No scheduled sessions yet.
        </div>
    @else
        <div class="list-group">
            @foreach($bookings as $session)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ \Carbon\Carbon::parse($session->preferred_time)->format('M d, Y (g:i A)') }}</strong><br>
                        <small class="text-muted">{{ $session->reason }}</small><br>
                        <span class="badge bg-{{ $session->status === 'approved' ? 'success' : ($session->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </div>

                    <div>
                       @if ($session->status === 'approved' && $session->videoSession && $session->videoSession->status === 'ongoing')
                             <a href="{{ route('video.join', $session->id) }}" class="btn btn-success btn-sm">
                                     Join Session
                                </a>
                            @elseif ($session->status === 'approved')
                          <span class="text-muted">Waiting for counselor</span>
                                    @else
                        <span class="text-muted">Pending</span>
                @endif

                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
