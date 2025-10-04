<!-- resources/views/admin/upload-students.blade.php -->
@extends('layouts.admin')



@section('content')
<div class="container">

    <a href="{{ route('admin.download.template') }}" class="btn btn-secondary mb-3">
        📥 Download CSV Template
    </a>
    
    <h2>Upload Student Accounts</h2>
    <form action="{{ route('admin.upload.students') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="csv_file" class="form-label">Select CSV File</label>
            <input type="file" class="form-control" name="csv_file" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
@endsection
