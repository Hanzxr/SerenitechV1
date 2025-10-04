@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="alert alert-primary">
        <h3>Admin Dashboard</h3>
        <p>Welcome, {{ auth()->user()->name }}!</p>
    </div>

    {{-- Admin Quick Actions --}}
    <div class="mt-4 d-flex flex-wrap gap-2">
         <div class="mt-4 d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">📋 Manage Posts</a>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-success">➕ Create Post</a>
        <a href="{{ route('admin.student.create') }}" class="btn btn-warning">👨‍🎓 Add Student</a>
        <a href="{{ route('admin.upload.page') }}" class="btn btn-info">📤 Upload Students</a>
        <a href="{{ route('admin.download.template') }}" class="btn btn-secondary">📄 Download CSV</a>
        <a href="{{ route('calendar.index') }}" class="btn btn-dark">📅 Calendar</a>
        <a href="{{ route('bookings.index') }}" class="btn btn-light">📌 Bookings</a>
        <a href="{{ route('video.start', ['booking' => 1]) }}" class="btn btn-danger">🎥 Video Session</a>
    </div>

    {{-- Recent Announcements --}}
    <div class="mt-5">
        <h4>Recent Announcements</h4>
        <hr>
        @if(isset($posts) && $posts->isNotEmpty())
            @foreach($posts as $post)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $post->title }}</h5>
                        <p class="card-text">{{ Str::limit($post->content, 150) }}</p>
                        <small class="text-muted">Posted on {{ $post->created_at->format('M d, Y') }}</small>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-muted">No announcements yet.</p>
        @endif
    </div>
</div>
@endsection
