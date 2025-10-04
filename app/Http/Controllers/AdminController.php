<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class AdminController extends Controller
{
    public function dashboard()
    {
        $posts = Post::latest()->take(5)->get();
        return view('admin.dashboard', compact('posts'));
    }
}
