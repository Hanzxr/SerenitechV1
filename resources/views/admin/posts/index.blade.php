@extends('layouts.admin')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">📢 Announcements</h2>

    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary mb-3">+ Create Announcement</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($posts->isEmpty())
        <p>No announcements yet.</p>
    @else
        <div class="list-group">
            @foreach($posts as $post)
                <div class="list-group-item">
                    <h5>{{ $post->title }}</h5>
                    <p>{{ $post->content }}</p>
                    <small class="text-muted">Posted on {{ $post->created_at->format('M d, Y h:i A') }}</small>
                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
