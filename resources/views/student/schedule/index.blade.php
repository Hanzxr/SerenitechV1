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
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ \Carbon\Carbon::parse($session->preferred_time)->format('M d, Y (g:i A)') }}</strong><br>
                            <small class="text-muted">{{ $session->reason }}</small><br>
                            <span class="badge bg-{{ $session->status === 'approved' ? 'success' : ($session->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </div>

                        <div>
                            @if ($session->status === 'pending')
                                <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#editForm{{ $session->id }}">Edit</button>

                                {{-- Cancel with confirmation --}}
                                <form id="cancelForm{{ $session->id }}" action="{{ route('student.booking.cancel', $session->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmCancel({{ $session->id }})">
                                        Cancel
                                    </button>
                                </form>

                            @elseif ($session->status === 'approved' && $session->videoSession && $session->videoSession->status === 'ongoing')
                                <a href="{{ route('video.join', $session->id) }}" class="btn btn-success btn-sm">Join Session</a>
                            @elseif ($session->status === 'approved')
                                <span class="text-muted">Waiting for counselor</span>
                            @else
                                <span class="text-muted">{{ ucfirst($session->status) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- 🔽 Collapsible edit form --}}
                    <div class="collapse mt-3" id="editForm{{ $session->id }}">
                        <form action="{{ route('student.booking.update', $session->id) }}" method="POST" class="border rounded p-3 bg-light">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Reschedule Date</label>
                                    <input type="date" id="edit-date-{{ $session->id }}" name="date" class="form-control"
                                           value="{{ \Carbon\Carbon::parse($session->preferred_time)->toDateString() }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Time</label>
                                    <input type="time" id="edit-time-{{ $session->id }}" name="time" class="form-control" step="600"
                                           value="{{ \Carbon\Carbon::parse($session->preferred_time)->format('H:i') }}" required>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label">Reason</label>
                                    <textarea name="reason" class="form-control" required>{{ $session->reason }}</textarea>
                                </div>
                            </div>

                            {{-- 🔹 Counselor schedule preview --}}
                            <div class="mt-3">
                                <h6>📅 Counselor’s Schedule</h6>
                                <ul id="schedule-list-{{ $session->id }}" class="list-group small mb-3"></ul>
                            </div>

                            <button type="submit" class="btn btn-success btn-sm mt-2">Save Changes</button>
                        </form>
                    </div>

                    {{-- 🔄 Reschedule from admin --}}
                    @if ($session->reschedule_status === 'requested')
                        <div class="alert alert-info mt-3">
                            Counselor suggested new schedule:
                            <strong>{{ \Carbon\Carbon::parse($session->rescheduled_time)->format('M d, Y g:i A') }}</strong>
                        </div>
                        <form method="POST" action="{{ route('student.reschedule.accept',$session->id) }}">
                            @csrf
                            <button class="btn btn-success btn-sm">Accept</button>
                        </form>

                        <form method="POST" action="{{ route('student.reschedule.decline',$session->id) }}" class="mt-2">
                            @csrf
                            <textarea name="reason" placeholder="Why decline?" required class="form-control mb-2"></textarea>
                            <button class="btn btn-danger btn-sm">Decline</button>
                        </form>
                    @endif
                </div>

                {{-- Schedule fetcher --}}
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    let dateInput = document.getElementById('edit-date-{{ $session->id }}');
                    let scheduleList = document.getElementById('schedule-list-{{ $session->id }}');

                    function fetchSchedule(date) {
                        let counselorId = "{{ $session->counselor_id }}";
                        scheduleList.innerHTML = "<li class='list-group-item text-muted'>Loading schedule...</li>";

                        fetch(`/student/counselor/${counselorId}/schedule/${date}`)
                            .then(res => res.json())
                            .then(data => {
                                scheduleList.innerHTML = "";
                                if (data.events.length === 0 && data.recurring.length === 0 && data.bookings.length === 0) {
                                    scheduleList.innerHTML = `<li class="list-group-item text-success">✅ No conflicts — You can book anytime.</li>`;
                                    return;
                                }

                                data.events.forEach(ev => {
                                    scheduleList.innerHTML += `<li class="list-group-item list-group-item-warning">
                                        <strong>${ev.title}</strong><br>${ev.start_time} - ${ev.end_time}
                                    </li>`;
                                });

                                data.recurring.forEach(task => {
                                    scheduleList.innerHTML += `<li class="list-group-item list-group-item-info">
                                        <strong>${task.title}</strong><br>${task.start_time} - ${task.end_time} (every ${task.day_of_week})
                                    </li>`;
                                });

                                data.bookings.forEach(bk => {
                                    let time = new Date(bk.preferred_time);
                                    scheduleList.innerHTML += `<li class="list-group-item list-group-item-danger">
                                        <strong>Booked</strong><br>
                                        ${time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                    </li>`;
                                });
                            });
                    }

                    if (dateInput) {
                        fetchSchedule(dateInput.value);
                        dateInput.addEventListener('change', function () {
                            fetchSchedule(this.value);
                        });
                    }
                });

                function confirmCancel(id) {
                    if (confirm("Are you sure you want to cancel this booking?")) {
                        document.getElementById('cancelForm' + id).submit();
                    }
                }
                </script>
            @endforeach
        </div>
    @endif
</div>
@endsection
