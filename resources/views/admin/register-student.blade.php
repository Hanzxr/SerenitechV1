@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Register New Student</h3>

    <form action="{{ route('admin.student.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name:</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>PSU ID (e.g. 22-AC-0001):</label>
            <input type="text" name="psu_id" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Course:</label>
            <input type="text" name="course" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Confirm Password:</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Register Student</button>
    </form>
</div>
@endsection
