@extends('layouts.student')

@section('content')
<div class="container bg-light p-4 rounded">
    <h4>Booking with Counselor: {{ $counselor->name }}</h4>
    <form action="{{ route('student.booking.store') }}" method="POST">
        @csrf
        <input type="hidden" name="counselor_id" value="{{ $counselor->id }}">

        {{-- ✅ Personal Information --}}
        <h5>Personal Information</h5>
        <div class="row">
            <div class="col-md-4"><input name="course" class="form-control" placeholder="Course" required></div>
            <div class="col-md-4"><input name="age" type="number" class="form-control" placeholder="Age" required></div>
            <div class="col-md-4">
                <select name="sex" class="form-select" required>
                    <option value="">Select Sex</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="col-md-6 mt-2"><input name="address" class="form-control" placeholder="Address" required></div>
            <div class="col-md-6 mt-2"><input name="contact" type="number" class="form-control" placeholder="Contact No." required></div>
            <div class="col-md-6 mt-2">
                <select name="civil_status" class="form-select" required>
                    <option value="">Select Civil Status</option>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="widowed">Widowed</option>
                    <option value="separated">Separated</option>
                </select>
            </div>
        </div>

        {{-- ✅ Emergency Contact --}}
        <h5 class="mt-4">Emergency Contact</h5>
        <div class="row">
            <div class="col-md-6"><input name="emergency_name" class="form-control" placeholder="Name" required></div>
            <div class="col-md-6"><input name="emergency_address" class="form-control" placeholder="Address" required></div>
            <div class="col-md-4 mt-2"><input name="emergency_relationship" class="form-control" placeholder="Relationship" required></div>
            <div class="col-md-4 mt-2"><input name="emergency_contact" type="number" class="form-control" placeholder="Contact No." required></div>
            <div class="col-md-4 mt-2"><input name="emergency_occupation" class="form-control" placeholder="Occupation" required></div>
        </div>

        {{-- ✅ Session Preference --}}
        <h5 class="mt-4">Session Preference</h5>
        <select name="preference" class="form-select mb-3" required>
            <option value="online">💻 Online</option>
            <option value="face-to-face">🏫 Face-to-Face</option>
        </select>

        {{-- ✅ Counseling Details --}}
        <h5 class="mt-4">Counseling Details</h5>
        <textarea name="reason" class="form-control mb-3" placeholder="What do you want to talk about?" required></textarea>

        {{-- ✅ Date Picker --}}
        <label for="date-picker" class="form-label">Select Date</label>
        <input type="date" id="date-picker" class="form-control mb-3" required>

        {{-- ✅ Counselor Schedule --}}
        <div class="mt-3">
            <h6>📅 Counselor’s Schedule for <span id="selected-date-text">—</span></h6>
            <ul id="schedule-list" class="list-group small mb-3"></ul>
        </div>

        {{-- ✅ Time Picker (10-min intervals) --}}
        <div id="time-section" style="display:none;">
            <label for="time-picker" class="form-label">Select Preferred Time</label>
            <input type="time" id="time-picker" class="form-control mb-3" step="600">
        </div>

        {{-- ✅ Hidden Final Input --}}
        <input type="hidden" name="preferred_time" id="preferred_time">

        <button class="btn btn-success">Submit Booking</button>
    </form>
</div>

{{-- ✅ Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('date-picker');
    const timeInput = document.getElementById('time-picker');
    const preferredInput = document.getElementById('preferred_time');
    const scheduleList = document.getElementById('schedule-list');
    const selectedDateText = document.getElementById('selected-date-text');
    const timeSection = document.getElementById('time-section');
    let selectedDate = null;

    // 🔧 Convert 24h -> 12h format
    function formatTime(timeStr) {
        if (!timeStr) return "";
        let [hour, minute] = timeStr.split(':');
        hour = parseInt(hour, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
        return `${hour}:${minute} ${ampm}`;
    }

    // When student picks a date
    dateInput.addEventListener('change', function () {
        selectedDate = this.value;
        selectedDateText.textContent = selectedDate;
        timeSection.style.display = 'block';
        scheduleList.innerHTML = "<li class='list-group-item text-muted'>Loading schedule...</li>";

        let counselorId = "{{ $counselor->id }}";

        fetch(`/student/counselor/${counselorId}/schedule/${selectedDate}`)
            .then(res => res.json())
            .then(data => {
                scheduleList.innerHTML = "";

                if (data.events.length === 0 && data.recurring.length === 0 && data.bookings.length === 0) {
                    scheduleList.innerHTML = `<li class="list-group-item text-success">✅ No conflicts — You can book anytime.</li>`;
                    return;
                }

                // ✅ Events (without notes)
                data.events.forEach(ev => {
                    scheduleList.innerHTML += `<li class="list-group-item list-group-item-warning">
                        <strong>${ev.title}</strong> (${ev.type})<br>
                        ${formatTime(ev.start_time)} - ${formatTime(ev.end_time)}
                    </li>`;
                });

                // ✅ Recurring tasks
                data.recurring.forEach(task => {
                    scheduleList.innerHTML += `<li class="list-group-item list-group-item-info">
                        <strong>${task.title}</strong> (${task.type})<br>
                        ${formatTime(task.start_time)} - ${formatTime(task.end_time)} (every ${task.day_of_week})
                    </li>`;
                });

                // ✅ Already booked slots
                data.bookings.forEach(bk => {
                    let time = new Date(bk.preferred_time);
                    scheduleList.innerHTML += `<li class="list-group-item list-group-item-danger">
                        <strong>Booked</strong><br>
                        ${time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                    </li>`;
                });
            });
    });

    // When student picks a time
    timeInput.addEventListener('change', function () {
        if (!selectedDate) return;
        preferredInput.value = selectedDate + "T" + this.value;
    });
});
</script>
@endsection
