@extends('layouts.admin')

@section('content')
<div class="container">
    <h3 class="mb-4">Booking Requests</h3>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Student</th>
                <th>Date</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $request)
            <tr>
                <td>{{ $request->student->name }}</td>
                <td>{{ \Carbon\Carbon::parse($request->preferred_time)->toDateString() }}</td>
                <td>{{ \Carbon\Carbon::parse($request->preferred_time)->format('g:i A') }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ ucfirst($request->status) }}</td>
                <td>
                    <a href="{{ route('bookings.show', $request->id) }}" class="btn btn-sm btn-primary">Details</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
