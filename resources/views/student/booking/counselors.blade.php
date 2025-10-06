@extends('layouts.student')

@section('content')
  @php
$hasBooking = \App\Models\BookingRequest::where('student_id', auth()->id())
    ->whereIn('status', ['pending','approved'])
    ->exists();
@endphp

<div class="container">
    <h2>Select a Counselor</h2>
    <div class="row">
        @foreach($counselors as $counselor)
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5>{{ $counselor->name }}</h5>
                <p>{{ $counselor->email }}</p>

                @if ($hasBooking)
                    <button class="btn btn-secondary" disabled>Booking Locked</button>
                @else
                    <a href="{{ route('student.booking.form', $counselor->id) }}" class="btn btn-primary">Book</a>
                @endif

            </div>
        </div>
        @endforeach
    </div>
</div>

</div>
@endsection
