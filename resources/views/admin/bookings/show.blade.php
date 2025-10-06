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

    {{-- Status Control --}}
    <div class="mb-3">
        @if ($booking->status === 'cancelled')
            <div class="alert alert-secondary">This booking has been cancelled by the student. No further changes allowed.</div>
        @else
            <form id="statusForm" method="POST" action="{{ route('bookings.status.update', $booking->id) }}">
                @csrf
                <button type="button" data-status="approved" class="btn btn-success status-btn">Approve</button>
                <button type="button" data-status="rejected" class="btn btn-danger status-btn">Reject</button>
                <button type="button" data-status="pending" class="btn btn-warning status-btn">Pending</button>
                <input type="hidden" name="status" id="statusInput">

                {{-- Reschedule --}}
@if (!in_array($booking->status, ['cancelled']))
<div class="mt-3">
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
        Reschedule
    </button>
</div>

<!-- Modal -->
<!-- Rebook Modal -->
<div class="modal fade" id="rebookModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('bookings.rebook', $booking->id) }}">
        @csrf
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Rebook Student</h5></div>
            <div class="modal-body">
                <label>Date</label>
                <input type="date" name="date" class="form-control mb-2" required>
                <label>Time</label>
                <input type="time" name="time" class="form-control mb-2" required>
                <div class="mt-3">
    <h6>📅 My Schedule on selected date</h6>
    <ul id="admin-schedule-list" class="list-group small mb-3"></ul>
</div>
                <label>Reason (optional)</label>
                <textarea name="reason" class="form-control mb-2"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Rebook</button>
            </div>
        </div>
    </form>
  </div>
</div>

<button class="btn btn-outline-secondary mt-2" data-bs-toggle="modal" data-bs-target="#rebookModal">
    Rebook
</button>

@endif

            </form>
        @endif
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
{{-- Fetch and display admin's schedule on selected date --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    let dateInput = document.querySelector('#rescheduleModal input[name="date"], #rebookModal input[name="date"]');
    let scheduleList = document.getElementById('admin-schedule-list');

    if(dateInput){
        dateInput.addEventListener('change', function(){
            let date = this.value;
            fetch(`/admin/schedule/${date}`)
                .then(res => res.json())
                .then(data => {
                    scheduleList.innerHTML = "";
                    if (data.length === 0) {
                        scheduleList.innerHTML = `<li class="list-group-item text-success">✅ Free all day</li>`;
                        return;
                    }
                    data.forEach(ev => {
                        scheduleList.innerHTML += `<li class="list-group-item">
                            <strong>${ev.title}</strong><br>${ev.start_time} - ${ev.end_time}
                        </li>`;
                    });
                });
        });
    }
});
</script>
@endsection
