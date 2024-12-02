<?php

namespace App\Services;

use App\Models\Manager;
use App\Models\Product;
use App\Models\User;
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
            $imagePath = $product->getAttributes()['image'];
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
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
            $imagePath = $product->getAttributes()['image'];
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                $product->image = null;
                $product->save();
            } else
                throw new \Exception("no image to delete");
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
            $imagePath = $product->getAttributes()['image'];
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

    /**
     * get all products with their id, name, market name, image, category and price
     *
     * @param int $perPage
     * @param int $page
     */
    public function getProducts(int $perPage, int $page)
    {
        $products = Product::with('market')
            ->select('id', 'name', 'image', 'category_id', 'price', 'market_id')
            ->paginate($perPage, ['*'], 'page', $page);

        $products->getCollection()->transform(function ($product) {
            $product->market_name = $product->market->name;
            unset($product->market);
            return $product;
        });

        return [
            'currentPageItems' => $products->items(),
            'total' => $products->total(),
            'perPage' => $products->perPage(),
            'currentPage' => $products->currentPage(),
            'lastPage' => $products->lastPage(),
        ];
    }

    /**
     * get all products by category with their id, name, market name, image and price
     *
     * @param int $perPage
     * @param int $page
     * @param int $category_id
     */
    public function getProductsByCategory(int $perPage, int $page, int $category_id)
    {
        $products = Product::with('market')
            ->where('category_id', $category_id)
            ->select('id', 'name', 'category_id', 'image', 'price', 'market_id')
            ->paginate($perPage, ['*'], 'page', $page);

        $products->getCollection()->transform(function ($product) {
            $product->market_name = $product->market->name;
            unset($product->market);
            return $product;
        });

        return [
            'currentPageItems' => $products->items(),
            'total' => $products->total(),
            'perPage' => $products->perPage(),
            'currentPage' => $products->currentPage(),
            'lastPage' => $products->lastPage(),
        ];
    }

    /**
     * get products by name
     *
     * @param int $perPage
     * @param int $page
     * @param string $name
     */
    public function getProductsByName(int $perPage, int $page, string $name)
    {
        $products = Product::with('market')
            ->where('name', $name)
            ->select('id', 'name',  'category_id', 'image',  'price', 'market_id')
            ->paginate($perPage, ['*'], 'page', $page);

        $products->getCollection()->transform(function ($product) {
            $product->market_name = $product->market->name;
            unset($product->market);
            return $product;
        });

        return [
            'currentPageItems' => $products->items(),
            'total' => $products->total(),
            'perPage' => $products->perPage(),
            'currentPage' => $products->currentPage(),
            'lastPage' => $products->lastPage(),
        ];
    }

    /**
     * check if this product from favorites
     *
     * @param int $product_id
     * @param int $user_id
     * @return bool
     */
    public function isFavorite(int $product_id, int $user_id): bool
    {
        return (DB::table('favorites')
            ->where('user_id', '=', $user_id)
            ->where('product_id', '=', $product_id)
            ->get()->first() != null);
    }

    /**
     * change status of favorite for user of some product
     *
     * @param User $user
     * @param int $product_id
     * @param bool $exists
     * @return bool
     */
    public function toggleFavorite(User $user, int $product_id, bool $exists): bool
    {
        DB::beginTransaction();
        try {
            if ($exists)
                $user->favorites()->detach($product_id);
            else
                $user->favorites()->attach($product_id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * get favorite products for user
     *
     * @param User $user
     * @param int $perPage
     * @param int $page
     */
    public function getFavoriteProducts(User $user, int $perPage, int $page)
    {
        $products = Product::with('market')
            ->whereHas('favoritedBy', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->select('id', 'name', 'image', 'category_id', 'price', 'market_id')
            ->paginate($perPage, ['*'], 'page', $page);

        $products->getCollection()->transform(function ($product) {
            $product->market_name = $product->market->name;
            unset($product->market);
            return $product;
        });

        return [
            'currentPageItems' => $products->items(),
            'total' => $products->total(),
            'perPage' => $products->perPage(),
            'currentPage' => $products->currentPage(),
            'lastPage' => $products->lastPage(),
        ];
    }
}
