@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">📢 New Announcement</h2>

    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea name="content" rows="5" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Image (optional)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="video" class="form-label">Video (optional)</label>
            <input type="file" name="video" class="form-control" accept="video/*">
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-send"></i> Post Announcement
        </button>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
