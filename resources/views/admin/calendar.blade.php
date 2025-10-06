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
                            <option value="unavailable">Unavailable</option>
                            <option value="class">Class</option>
                            <option value="task">Task</option>
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

    {{-- Event Carousel --}}
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-dark text-white">
            <strong>Scheduled Events</strong>
        </div>
        <div class="card-body">
            @if($events->count() > 0)
                <div id="eventCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ($events as $key => $event)
                            <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                <div class="p-4 border rounded bg-light">
                                    <h5>
                                        {{ $event->title }}
                                        <span class="badge bg-secondary">{{ ucfirst($event->type) }}</span>
                                    </h5>
                                    <p class="mb-1">
                                        📅 {{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}
                                    </p>
                                    <p class="mb-1">
                                        ⏰
                                        @if($event->start_time)
                                            {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} -
                                            {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                        @else
                                            All Day
                                        @endif
                                    </p>
                                    @if($event->notes)
                                        <p class="mb-1">📝 {{ $event->notes }}</p>
                                    @endif

                                    <form method="POST" action="{{ route('calendar.delete', $event->id) }}" class="mt-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Delete this event?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            @else
                <p class="text-muted text-center">No events added.</p>
            @endif
        </div>
    </div>

    {{-- Recurring Tasks grouped by day --}}
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <strong>Weekly Recurring Tasks</strong>
        </div>
        <div class="card-body">
            @php
                $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                $tasks = \App\Models\WeeklySchedule::where('user_id', auth()->id())->get()->groupBy('day_of_week');
            @endphp

            <div class="row">
                @foreach ($days as $day)
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-bold">{{ $day }}</h6>
                            <ul class="list-group list-group-flush">
                                @if(isset($tasks[$day]))
                                    @foreach($tasks[$day] as $task)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <strong>{{ $task->title }}</strong><br>
                                                <small>
                                                    {{ \Carbon\Carbon::parse($task->start_time)->format('g:i A') }} -
                                                    {{ \Carbon\Carbon::parse($task->end_time)->format('g:i A') }}
                                                </small>
                                            </span>
                                            <form method="POST" action="{{ route('calendar.recurring.delete', $task->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete task?')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="list-group-item text-muted">No tasks</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Add recurring task form --}}
            <div class="mt-4">
                <h6><i class="bi bi-plus-circle"></i> Add Weekly Task</h6>
                <form method="POST" action="{{ route('calendar.recurring.store') }}">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Day</label>
                            <select name="day_of_week" class="form-select">
                                @foreach($days as $day)
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
                            <button type="submit" class="btn btn-success">Add</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
