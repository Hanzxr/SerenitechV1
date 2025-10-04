@extends('layouts.student')

@section('content')
<div class="container">
    <h2>Select a Counselor</h2>
    <div class="row">
        @foreach($counselors as $counselor)
        <div class="col-md-4 mb-3">
            <div class="card p-3">
                <h5>{{ $counselor->name }}</h5>
                <p>{{ $counselor->email }}</p>
                <a href="{{ route('student.booking.form', $counselor->id) }}" class="btn btn-primary">Book</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
