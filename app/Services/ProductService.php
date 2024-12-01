<?php

namespace App\Services;

use App\Models\Manager;
use App\Models\Product;
use App\Repositories\CategoryRepositry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    protected $categoryRepositry;
    public $marketService;

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
     * delete image for product
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
     * create product
     *
     * @param array $data
     * @param Manager $manager
     */
    public function createProduct(array $data, Manager $manager)
    {
        DB::beginTransaction();
        try {
            $data['market_id'] = $manager->market->id;
            $product = Product::create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
        return $product;
    }

    /**
     * Update product information.
     *
     * @param Product $product
     * @param array $data
     * @return Product|null
     * @throws ModelNotFoundException
     */
    public function editProduct(Product $product, array $data)
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

    /**
     * delete product
     *
     * @param Product $product
     * @return bool
     */
    public function deleteProduct(Product $product): bool
    {
        DB::beginTransaction();
        try {
            $imagePath = $product->image;
            $product->delete();
            if ($imagePath && Storage::disk('public')->exists($imagePath))
                Storage::disk('public')->delete($imagePath);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }
}
