@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">📢 Announcements</h2>

    {{-- Success message --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Create Button --}}
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> New Announcement
    </a>

    {{-- Announcements List --}}
    @if ($posts->isEmpty())
        <p class="text-muted">No announcements yet.</p>
    @else
        <div class="list-group">
            @foreach ($posts as $post)
                <div class="list-group-item mb-2">
                    <h5>{{ $post->title }}</h5>
                    <p>{{ $post->content }}</p>

                    {{-- Display Image --}}
                    @if ($post->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $post->image) }}" alt="Post Image" class="img-fluid rounded">
                        </div>
                    @endif

                    {{-- Display Video --}}
                    @if ($post->video)
                        <div class="mb-2">
                            <video controls class="w-100 rounded">
                                <source src="{{ asset('storage/' . $post->video) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    @endif

                    <small class="text-muted">
                        Posted on {{ $post->created_at->format('F j, Y g:i A') }}
                    </small>

                    {{-- Delete Button --}}
                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection


