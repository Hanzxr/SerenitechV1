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
                    <small class="text-muted">
                        Posted on {{ $post->created_at->format('F j, Y g:i A') }}
                    </small>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
