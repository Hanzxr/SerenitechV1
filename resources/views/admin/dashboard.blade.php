@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="alert alert-warning">
        <h3>Admin Dashboard</h3>
        <p>Welcome, {{ auth()->user()->name }}!</p>

        <a href="{{ route('admin.posts') }}" class="btn btn-info mt-3">
    View Announcements
</a>

    </div>
</div>
@endsection
