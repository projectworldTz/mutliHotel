<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService
{
    public function __construct(private ProductRepository $repository)
    {
    }

    public function getCatalog($perPage = 12)
    {
        return $this->repository->allPublished($perPage);
    }

    public function getProductBySlug(string $slug)
    {
        return $this->repository->findBySlug($slug);
    }

    public function getCategories()
    {
        return $this->repository->categories();
    }

    public function getRelated($product)
    {
        return $this->repository->related($product);
    }
}
