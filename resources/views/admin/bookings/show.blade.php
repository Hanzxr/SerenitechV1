@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>{{ $student->name }}</h4>
        <div>
            <a href="#" class="btn btn-secondary">Edit</a>
            <button class="btn btn-dark">Client Info</button>
        </div>
    </div>

    {{-- ✅ Student Full Info --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <p><strong>PSU ID:</strong> {{ $student->psu_id }}</p>
            <p><strong>Age:</strong> {{ $booking->age }}</p>
            <p><strong>Sex:</strong> {{ $booking->sex }}</p>
            <p><strong>Course:</strong> {{ $booking->course }}</p>
        </div>
        <div class="col-md-4">
            <p><strong>Address:</strong> {{ $booking->address }}</p>
            <p><strong>Contact:</strong> {{ $booking->contact }}</p>
            <p><strong>Civil Status:</strong> {{ $booking->civil_status }}</p>
        </div>
        <div class="col-md-4">
            <h6>Emergency Contact</h6>
            <p><strong>Name:</strong> {{ $booking->emergency_name }}</p>
            <p><strong>Relationship:</strong> {{ $booking->emergency_relationship }}</p>
            <p><strong>Contact:</strong> {{ $booking->emergency_contact }}</p>
            <p><strong>Address:</strong> {{ $booking->emergency_address }}</p>
            <p><strong>Occupation:</strong> {{ $booking->emergency_occupation }}</p>
        </div>
    </div>

    {{-- ✅ Status Control --}}
    <div class="mb-3">
        <form method="POST" action="{{ route('bookings.status.update', $booking->id) }}">
            @csrf
            <button name="status" value="approved" class="btn btn-success">Approve</button>
            <button name="status" value="rejected" class="btn btn-danger">Reject</button>
            <button name="status" value="pending" class="btn btn-warning">Pending</button>
        </form>
    </div>

    {{-- ✅ Current Booking --}}
    <h5>Current Booking</h5>
    <table class="table">
        <tr>
            <td>{{ \Carbon\Carbon::parse($booking->preferred_time)->toFormattedDateString() }}</td>
            <td>{{ \Carbon\Carbon::parse($booking->preferred_time)->format('g:i A') }}</td>
            <td>{{ $booking->reason }}</td>
            <td>
    @if ($booking->status === 'approved')
        <a href="{{ route('video.start', $booking->id) }}" class="btn btn-dark btn-sm">
            Start Session
        </a>
    @else
        <span class="text-muted">Waiting</span>
    @endif
</td>

            <td>{{ ucfirst($booking->status) }}</td>
        </tr>
    </table>

    {{-- ✅ Upcoming Sessions --}}
    <h5>Upcoming Requests</h5>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($upcomingRequests as $session)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($session->preferred_time)->toFormattedDateString() }}</td>
                    <td>{{ \Carbon\Carbon::parse($session->preferred_time)->format('g:i A') }}</td>
                    <td>{{ $session->reason }}</td>
                    <td>{{ ucfirst($session->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No upcoming approved requests</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ✅ Notes --}}
    <h5 class="mt-4">Session Notes</h5>
    <form method="POST" action="{{ route('bookings.notes.save', $booking->id) }}">
        @csrf
        <textarea class="form-control mb-2" name="notes" placeholder="Enter session notes..." rows="4"></textarea>
        <button class="btn btn-secondary">Attach</button>
        <button class="btn btn-dark">Save Notes</button>
    </form>

    {{-- ✅ Follow-up --}}
    <h5 class="mt-4">Schedule Follow-up</h5>
    <form method="POST" action="{{ route('calendar.store') }}">
        @csrf
        <div class="row mb-2">
            <div class="col-md-4">
                <label>Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Time</label>
                <input type="time" name="time" class="form-control" required>
            </div>
        </div>
        <div class="mb-2">
            <label>Session Type</label>
            <select class="form-control" name="type">
                <option>Individual Therapy</option>
                <option>Follow-up</option>
                <option>Counseling</option>
            </select>
        </div>
        <div class="mb-2">
            <label>Notes</label>
            <textarea class="form-control" name="notes" placeholder="Add notes for follow-up..."></textarea>
        </div>
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <button class="btn btn-dark">Schedule Follow-up</button>
    </form>
</div>
@endsection
