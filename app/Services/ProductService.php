<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\CategoryRepositry;

class ProductService
{
    protected $categoryRepositry;

    /**
     * Create a new class instance.
     */
    public function __construct(CategoryRepositry $categoryRepositry)
    {
        $this->categoryRepositry = $categoryRepositry;
    }
}
