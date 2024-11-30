<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\CategoryRepositry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    /**
     * find product by id
     *
     * @param int $id
     * @return Product
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Product
    {
        return Product::findOrFail($id);
    }

    /**
     * upload Image for product
     *
     * @param Product $product
     * @param $image
     * @return bool
     */
    public function uploadImage(Product $product, $image): bool
    {
        DB::beginTransaction();
        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $image->store('images/products', 'public');
            $product->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * delete image for user
     *
     * @param Product $product
     * @return bool
     */
    public function deleteImage(Product $product): bool
    {
        DB::beginTransaction();
        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
                $product->image = null;
                $product->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * create Product
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        DB::beginTransaction();
        try {
            Product::create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * Update product information.
     *
     * @param Product $product
     * @param $data
     * @return Product|null
     * @throws ModelNotFoundException
     */
    public function updateUser(Product $product, $data)
    {
        DB::beginTransaction();
        try {
            $product->update($data);
            $product->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
        return $product;
    }
}