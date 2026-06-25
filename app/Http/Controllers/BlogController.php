<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()->with('category')->latest()->paginate(6);
        $categories = BlogCategory::orderBy('name')->get();

        return view('blog.index', compact('posts', 'categories'));
    }

    public function show(BlogPost $post)
    {
        return view('blog.show', compact('post'));
    }
}
