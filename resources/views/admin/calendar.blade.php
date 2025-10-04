@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4"><i class="bi bi-calendar-event"></i> Counselor Calendar <small class="text-muted">(Availability)</small></h2>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Add Event Form --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <strong>Add Event</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('calendar.event.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="unavailable">❌</option>
                            <option value="class">📘</option>
                            <option value="task">📝</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-success mt-3"><i class="bi bi-plus-circle"></i> Add Event</button>
            </form>
        </div>
    </div>

    {{-- Event Table --}}
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-dark text-white">
            <strong>Scheduled Events</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped m-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td>{{ $event->title }}</td>
                            <td>{{ $event->date }}</td>
                            <td>
                                {{ $event->start_time ? $event->start_time . ' - ' . $event->end_time : 'All Day' }}
                            </td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">{{ $event->type }}</span>
                            </td>
                            <td>{{ $event->notes }}</td>
                            <td>
                                <form method="POST" action="{{ route('calendar.delete', $event->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Delete this event?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No events added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recurring Tasks --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <strong>Add Weekly Recurring Task</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('calendar.recurring.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Day</label>
                        <select name="day_of_week" class="form-select">
                            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="class">Class</option>
                            <option value="task">Task</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-success mt-4">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <strong>Weekly Recurring Tasks</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped m-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (App\Models\WeeklySchedule::where('user_id', auth()->id())->get() as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->day_of_week }}</td>
                            <td>{{ $task->start_time }} - {{ $task->end_time }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($task->type) }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('calendar.recurring.delete', $task->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete task?')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if(App\Models\WeeklySchedule::where('user_id', auth()->id())->count() == 0)
                        <tr>
                            <td colspan="5" class="text-center text-muted">No recurring tasks.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
