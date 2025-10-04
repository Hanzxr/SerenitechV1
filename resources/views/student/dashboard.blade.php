@extends('layouts.student')

@section('content')
<div class="container mt-4">
    <div class="alert alert-primary">
        <h3>Student Dashboard</h3>
        <p>Welcome, {{ auth()->user()->name }}!</p>
    </div>


@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-x-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif








</div>
@endsection
