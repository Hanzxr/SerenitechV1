@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="alert alert-warning">
        <h3>Admin Dashboard</h3>
        <p>Welcome, {{ auth()->user()->name }}!</p>
    </div>
</div>
@endsection
