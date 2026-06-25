<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sku',
        'material',
        'color',
        'width',
        'height',
        'length',
        'weight',
        'assembly_required',
        'installation_available',
        'delivery_info',
        'status',
    ];

    protected $casts = [
        'assembly_required' => 'boolean',
        'installation_available' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%");
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function related(Product $product)
    {
        return static::where('category_id', $product->category_id)->where('id', '!=', $product->id);
    }
}
