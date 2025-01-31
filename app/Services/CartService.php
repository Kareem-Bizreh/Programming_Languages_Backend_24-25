<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Repositories\CategoryRepositry;
use Illuminate\Support\Facades\DB;

class CartService
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
     * change product count in cart
     *
     * @param Product $product
     * @param Cart $cart
     * @param int $count
     * @return bool
     */
    public function changeProduct(Product $product, Cart $cart, int $count): bool
    {
        DB::beginTransaction();
        try {
            $existingProduct = $cart->products()->where('product_id', $product->id)->first();
            if ($existingProduct) {
                $newCount = $existingProduct->pivot->count + $count;
                if ($newCount <= $product->quantity && $newCount >= 1) {
                    $cart->products()->updateExistingPivot($product->id, [
                        'count' => $newCount,
                    ]);
                    $cart->total_cost += $count * $product->price;
                    $cart->save();
                } else
                    throw new \Exception("we dont have enough products");
            } else {
                if ($count <= $product->quantity && $count >= 1) {
                    $cart->products()->attach($product->id, ['count' => $count]);
                    $cart->count = $cart->count + 1;
                    $cart->total_cost += $count * $product->price;
                    $cart->save();
                } else
                    throw new \Exception("we dont have enough products");
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * delete product from cart
     *
     * @param Product $product
     * @param Cart $cart
     * @return bool
     */
    public function deleteProduct(Product $product, Cart $cart): bool
    {
        DB::beginTransaction();
        try {
            $existingProduct = $cart->products()->where('product_id', $product->id)->first();
            if ($existingProduct) {
                $cart->total_cost -= $existingProduct->pivot->count * $product->price;
                $cart->products()->detach($product->id);
                $cart->count = $cart->count - 1;
                $cart->save();
            } else {
                throw new \Exception("nothing to delete");
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * clear cart
     * @param Cart $cart
     * @return bool
     */
    public function clearCart(Cart $cart): bool
    {
        DB::beginTransaction();
        try {
            $cart->products()->detach();
            $cart->count = 0;
            $cart->total_cost = 0;
            $cart->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * get cart
     * @param Cart $cart
     * @param string $lang
     */
    public function getCart(Cart $cart, string $lang = 'en')
    {
        $products = $cart->products()
            ->with('market')
            ->select(
                'products.id',
                "products.name_{$lang} as name",
                'products.category_id',
                'products.image',
                'products.price',
                'products.market_id'
            )
            ->get();

        $products->transform(function ($product) use ($lang) {
            $product->count = $product->pivot->count;
            $product->total_cost = $product->price * $product->count;
            $product->market_name = $product->market['name_' . $lang];
            $product->category = $this->categoryRepositry->getById($product->category_id, $lang)->name;
            unset($product->market);
            unset($product->market_id);
            unset($product->category_id);
            unset($product->pivot);
            return $product;
        });

        return $products;
    }
}
