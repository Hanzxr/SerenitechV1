@extends('layouts.admin')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">📝 Create Announcement</h2>

    <form action="{{ route('admin.posts.store') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Content</label>
            <textarea name="content" rows="5" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-success">Post Announcement</button>
        <a href="{{ route('admin.posts') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
