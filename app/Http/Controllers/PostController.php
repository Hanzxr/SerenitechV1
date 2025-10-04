<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    // Admin: show all posts
    public function index()
    {
        $posts = Post::latest()->get();
        return view('admin.posts.index', compact('posts'));
    }

    // Admin: show form to create post
    public function create()
    {
        return view('admin.posts.create');
    }

    // Admin: store post
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048', // max 2MB
            'video' => 'nullable|mimetypes:video/mp4,video/avi,video/mov|max:10240', // max 10MB
        ]);

        $data = [
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
        ];

        // Store image
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts/images', 'public');
        }

        // Store video
        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('posts/videos', 'public');
        }

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Announcement posted successfully!');
    }

    // Admin: delete post
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Announcement deleted successfully!');
    }

    // Student: view announcements
    public function studentView()
    {
        $posts = Post::latest()->get();
        return view('student.announcements', compact('posts'));
    }
}
