<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * change product count in cart
     *
     * @param Product $product
     * @param Cart $cart
     * @param int $count
     * @return bool
     */
    public function changeProduct(Product $product, Cart $cart, int $count) //: bool
    {
        DB::beginTransaction();
        try {
            $existingProduct = $cart->products()->where('product_id', $product->id)->first();
            if ($existingProduct) {
                $count = $existingProduct->pivot->count + $count;
                if ($count <= $product->quantity && $count >= 0)
                    $cart->products()->updateExistingPivot($product->id, [
                        'count' => $count,
                    ]);
                else
                    throw new \Exception("we dont have enough products");
            } else {
                $cart->products()->attach($product->id, ['count' => $count]);
                $cart->number = $cart->number + 1;
                $cart->save();
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
                $cart->products()->detach($product->id);
                $cart->number = $cart->number - 1;
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
            $cart->number = 0;
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
     * @param int $perPage
     * @param int $page
     */
    public function getCart(Cart $cart, int $perPage, int $page)
    {
        $products = $cart->products()
            ->select('products.id', 'products.name', 'products.image', 'products.category_id', 'products.price', 'products.market_id')
            ->paginate($perPage, ['*'], 'page', $page);

        $products->getCollection()->transform(function ($product) {
            $product->count = $product->pivot->count;
            $product->price = $product->price * $product->count;
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