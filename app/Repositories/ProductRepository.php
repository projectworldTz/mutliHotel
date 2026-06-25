<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;

class ProductRepository
{
    public function allPublished($perPage = 12)
    {
        return Product::published()->with(['category', 'brand'])->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->with(['category', 'brand', 'images'])->first();
    }

    public function related(Product $product, $limit = 4)
    {
        return Product::related($product)->take($limit)->get();
    }

    public function categories()
    {
        return Category::orderBy('name')->get();
    }
}
