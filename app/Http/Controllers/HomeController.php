<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Product::published()->take(6)->get();
        $categories = Category::orderBy('name')->take(4)->get();

        return view('home', compact('featured', 'categories'));
    }
}
