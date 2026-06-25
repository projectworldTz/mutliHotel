<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::published();

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $request->brand));
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $products = $query->paginate(12);
        $categories = Category::orderBy('name')->get();

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        return view('shop.show', ['product' => $product, 'related' => Product::related($product)->take(4)->get()]);
    }

    public function category(Category $category)
    {
        $products = Product::published()->where('category_id', $category->id)->paginate(12);

        return view('shop.index', ['products' => $products, 'categories' => Category::orderBy('name')->get(), 'selectedCategory' => $category]);
    }
}
