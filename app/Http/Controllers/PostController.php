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
        ]);

        Post::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.posts')->with('success', 'Announcement posted successfully!');
    }

    // Admin: delete post
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.posts')->with('success', 'Announcement deleted successfully!');
    }

    // Student: view announcements
    public function showAnnouncements()
    {
        $posts = Post::latest()->get();
        return view('student.announcements', compact('posts'));
    }
}
