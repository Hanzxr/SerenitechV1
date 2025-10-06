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
            <div class="col-md-4">
                <input name="course" class="form-control" placeholder="Course" value="{{ old('course') }}" required>
            </div>
            <div class="col-md-4">
                <input name="age" type="number" class="form-control" placeholder="Age" value="{{ old('age') }}" required>
            </div>
            <div class="col-md-4">
                <select name="sex" class="form-select" required>
                    <option value="">Select Sex</option>
                    <option value="male" {{ old('sex') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('sex') == 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="col-md-6 mt-2">
                <input name="address" class="form-control" placeholder="Address" value="{{ old('address') }}" required>
            </div>
            <div class="col-md-6 mt-2">
                <input name="contact" type="number" class="form-control" placeholder="Contact No." value="{{ old('contact') }}" required>
            </div>
            <div class="col-md-6 mt-2">
                <select name="civil_status" class="form-select" required>
                    <option value="">Select Civil Status</option>
                    <option value="single" {{ old('civil_status') == 'single' ? 'selected' : '' }}>Single</option>
                    <option value="married" {{ old('civil_status') == 'married' ? 'selected' : '' }}>Married</option>
                    <option value="widowed" {{ old('civil_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                    <option value="separated" {{ old('civil_status') == 'separated' ? 'selected' : '' }}>Separated</option>
                </select>
            </div>
        </div>

        {{-- ✅ Emergency Contact --}}
        <h5 class="mt-4">Emergency Contact</h5>
        <div class="row">
            <div class="col-md-6">
                <input name="emergency_name" class="form-control" placeholder="Name" value="{{ old('emergency_name') }}" required>
            </div>
            <div class="col-md-6">
                <input name="emergency_address" class="form-control" placeholder="Address" value="{{ old('emergency_address') }}" required>
            </div>
            <div class="col-md-4 mt-2">
                <input name="emergency_relationship" class="form-control" placeholder="Relationship" value="{{ old('emergency_relationship') }}" required>
            </div>
            <div class="col-md-4 mt-2">
                <input name="emergency_contact" type="number" class="form-control" placeholder="Contact No." value="{{ old('emergency_contact') }}" required>
            </div>
            <div class="col-md-4 mt-2">
                <input name="emergency_occupation" class="form-control" placeholder="Occupation" value="{{ old('emergency_occupation') }}" required>
            </div>
        </div>

        {{-- ✅ Session Preference --}}
        <h5 class="mt-4">Session Preference</h5>
        <select name="preference" class="form-select mb-3" required>
            <option value="">Select Preference</option>
            <option value="online" {{ old('preference') == 'online' ? 'selected' : '' }}>💻 Online</option>
            <option value="face-to-face" {{ old('preference') == 'face-to-face' ? 'selected' : '' }}>🏫 Face-to-Face</option>
        </select>

        {{-- ✅ Counseling Details --}}
        <h5 class="mt-4">Counseling Details</h5>
        <textarea name="reason" class="form-control mb-3" placeholder="What do you want to talk about?" required>{{ old('reason') }}</textarea>

        {{-- ✅ Date Picker --}}
        <label for="date-picker" class="form-label">Select Date</label>
        <input type="date" id="date-picker" class="form-control mb-3" value="{{ old('preferred_time') ? \Carbon\Carbon::parse(old('preferred_time'))->toDateString() : '' }}" required>

        {{-- ✅ Counselor Schedule --}}
        <div class="mt-3">
            <h6>📅 Counselor’s Schedule for <span id="selected-date-text">—</span></h6>
            <ul id="schedule-list" class="list-group small mb-3"></ul>
        </div>

        {{-- ✅ Time Picker (10-min intervals) --}}
        <div id="time-section" style="display:none;">
            <label for="time-picker" class="form-label">Select Preferred Time</label>
            <input type="time" id="time-picker" class="form-control mb-3" step="600"
                value="{{ old('preferred_time') ? \Carbon\Carbon::parse(old('preferred_time'))->format('H:i') : '' }}">
        </div>

        {{-- ✅ Hidden Final Input --}}
        <input type="hidden" name="preferred_time" id="preferred_time" value="{{ old('preferred_time') }}">

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
    let selectedDate = dateInput.value || null;

    // 🔧 Convert 24h -> 12h format
    function formatTime(timeStr) {
        if (!timeStr) return "";
        let [hour, minute] = timeStr.split(':');
        hour = parseInt(hour, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
        return `${hour}:${minute} ${ampm}`;
    }

    function fetchSchedule(date) {
        let counselorId = "{{ $counselor->id }}";
        scheduleList.innerHTML = "<li class='list-group-item text-muted'>Loading schedule...</li>";

        fetch(`/student/counselor/${counselorId}/schedule/${date}`)
            .then(res => res.json())
            .then(data => {
                scheduleList.innerHTML = "";

                if (data.events.length === 0 && data.recurring.length === 0 && data.bookings.length === 0) {
                    scheduleList.innerHTML = `<li class="list-group-item text-success">✅ No conflicts — You can book anytime.</li>`;
                    return;
                }

                // ✅ Events
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

                // ✅ Booked slots
                data.bookings.forEach(bk => {
                    let time = new Date(bk.preferred_time);
                    scheduleList.innerHTML += `<li class="list-group-item list-group-item-danger">
                        <strong>Booked</strong><br>
                        ${time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                    </li>`;
                });
            });
    }

    // 🔹 Auto-show schedule if old date exists
    if (selectedDate) {
        timeSection.style.display = 'block';
        selectedDateText.textContent = selectedDate;
        fetchSchedule(selectedDate);
    }

    // 🔹 When user changes date
dateInput.addEventListener('change', function () {
    selectedDate = this.value;
    selectedDateText.textContent = selectedDate;
    timeSection.style.display = 'block';
    fetchSchedule(selectedDate);

    // ✅ Reset hidden preferred_time when date changes
    preferredInput.value = selectedDate + "T" + (timeInput.value || "");
});

    // 🔹 When user changes time
    timeInput.addEventListener('change', function () {
        if (!selectedDate) return;
        preferredInput.value = selectedDate + "T" + this.value;
    });
});

</script>
@endsection
