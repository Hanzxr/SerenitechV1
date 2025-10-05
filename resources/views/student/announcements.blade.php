@extends('layouts.student')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">📢 Latest Announcements</h2>

    @if ($posts->isEmpty())
        <p class="text-muted">No announcements available.</p>
    @else
        <div class="list-group">
            @foreach ($posts as $post)
                <div class="list-group-item mb-3">
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
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
