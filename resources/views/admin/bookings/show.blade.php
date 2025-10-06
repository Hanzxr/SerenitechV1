@extends('layouts.admin')

@section('content')
<div class="container">

    {{-- Student Info Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>{{ $student->name }}</h4>
        <div>
            <a href="#" class="btn btn-secondary btn-sm">Edit</a>
            <button class="btn btn-dark btn-sm">Client Info</button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

  {{-- Status control (replace existing block) --}}
<div class="mb-3">
    @if ($booking->status === 'cancelled')
        <div class="alert alert-secondary">This booking was cancelled by the student. No changes allowed.</div>
    @else
        <form id="statusForm" method="POST" action="{{ route('bookings.status.update', $booking->id) }}">
            @csrf
            <input type="hidden" name="status" id="statusInput">
            <button type="button" data-status="approved" class="btn btn-success status-btn">Approve</button>
            <button type="button" data-status="rejected" class="btn btn-danger status-btn">Reject</button>
            <button type="button" data-status="pending" class="btn btn-warning status-btn">Pending</button>
        </form>

        {{-- Reschedule modal trigger --}}
        <div class="mt-3 d-inline-block">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#rescheduleModal">Reschedule</button>
        </div>

        {{-- Rebook (for rejected bookings) - admin can create a new booking for the student --}}
        <div class="mt-3 d-inline-block">
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#rebookModal">Rebook</button>
        </div>
    @endif
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('bookings.reschedule',$booking->id) }}">
        @csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Reschedule Booking</h5></div>
            <div class="modal-body">
                <label>Date</label>
                <input type="date" name="date" class="form-control mb-2" required>
                <label>Time</label>
                <input type="time" name="time" class="form-control mb-2" required>
                <small class="text-muted">This will send a reschedule request to the student (they must accept/decline).</small>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Send Request</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- Rebook Modal -->
<div class="modal fade" id="rebookModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('bookings.rebook',$booking->id) }}">
        @csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Rebook (create new request for student)</h5></div>
            <div class="modal-body">
                <label>Date</label>
                <input type="date" name="date" class="form-control mb-2" required>
                <label>Time</label>
                <input type="time" name="time" class="form-control mb-2" required>
                <small class="text-muted">Admin rebook will create a new booking (status = pending). This overrides schedule conflicts.</small>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Rebook</button>
            </div>
        </div>
    </form>
  </div>
</div>




    {{-- Current Booking --}}
    <h5>Current Booking</h5>
    <table class="table">
        <tr>
            <td>{{ \Carbon\Carbon::parse($booking->preferred_time)->toFormattedDateString() }}</td>
            <td>{{ \Carbon\Carbon::parse($booking->preferred_time)->format('g:i A') }}</td>
            <td>{{ $booking->reason }}</td>
            <td>
                @if ($booking->status === 'approved')
                    <a href="{{ route('video.start', $booking->id) }}" class="btn btn-dark btn-sm">Start Session</a>
                @else
                    <span class="text-muted">Waiting</span>
                @endif
            </td>
            <td>
                <span class="badge bg-{{ $booking->status === 'approved' ? 'success' : ($booking->status === 'rejected' ? 'danger' : ($booking->status === 'cancelled' ? 'secondary' : 'warning')) }}">
                    {{ ucfirst($booking->status) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- Upcoming Sessions --}}
    <h5>Upcoming Requests</h5>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th><th>Time</th><th>Reason</th><th>Status</th>
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

    {{-- Session Notes --}}
    <h5 class="mt-4">Session Notes</h5>
    @if ($booking->status !== 'cancelled')
        <form method="POST" action="{{ route('bookings.notes.save', $booking->id) }}">
            @csrf
            <textarea class="form-control mb-2" name="notes" placeholder="Enter session notes..." rows="4"></textarea>
            <button class="btn btn-dark">Save Notes</button>
        </form>
    @else
        <p class="text-muted">Notes cannot be added to cancelled bookings.</p>
    @endif

    {{-- Follow-up --}}
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

{{-- SweetAlert for status change --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let status = this.dataset.status;
        Swal.fire({
            title: 'Are you sure?',
            text: "Change status to " + status + "?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('statusInput').value = status;
                document.getElementById('statusForm').submit();
            }
        });
    });
});
</script>

{{-- JS for status confirmations --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let status = this.dataset.status;
        Swal.fire({
            title: 'Confirm',
            text: 'Change status to ' + status + '?',
            icon: 'question',
            showCancelButton: true
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('statusInput').value = status;
                document.getElementById('statusForm').submit();
            }
        });
    });
});
</script>


@endsection
