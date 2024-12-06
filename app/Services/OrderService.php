<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $productService, $cartService;

    public function __construct(ProductService $productService, CartService $cartService)
    {
        $this->productService = $productService;
        $this->cartService = $cartService;
    }

    /**
     * create order
     *
     * @param $data
     * @param Cart $cart
     * @param int $user_id
     * @return bool
     */
    public function createOrder($data, Cart $cart, int $user_id): bool
    {
        DB::beginTransaction();
        try {
            $rejected = false;
            $products = $this->cartService->getCart($cart);
            $quantity = [];
            $globalOrder = Order::create([
                'user_id' => $user_id,
                'status_id' => 1,
                'location_id' => $data['location_id'],
                'total_cost' => $cart->total_cost,
                'date' => $data['date'],
                'count' => $cart->count
            ]);
            if (! $this->cartService->clearCart($cart)) {
                throw new \Exception("cart clear failed");
            }
            $marketsProduct = [];
            foreach ($products as $value) {
                $product = $this->productService->findById($value['id']);
                if (! $product) {
                    throw new \Exception("product not found");
                }
                if ($product->quantity < $value['count']) {
                    $rejected = true;
                }
                $market_id = $product->market_id;

                if (!isset($marketsProduct[$market_id])) {
                    $marketsProduct[$market_id] = [];
                }

                $marketsProduct[$market_id][] = $product;
                $quantity[$product->id] = $value['count'];
            }
            if ($rejected) {
                $globalOrder->status_id = 4;
                $globalOrder->save();
            }
            foreach ($marketsProduct as $market_id => $products) {
                $price = 0;
                $marketOrder = Order::create([
                    'user_id' => $user_id,
                    'market_id' => $market_id,
                    'status_id' => ($rejected ? 4 : 1),
                    'location_id' => $data['location_id'],
                    'date' => $data['date'],
                    'count' => count($products),
                    'global_order_id' => $globalOrder->id
                ]);
                foreach ($products as $product) {
                    $price += $product->price * $quantity[$product->id];
                    $marketOrder->products()->attach($product->id, ['quantity' => $quantity[$product->id]]);
                    if (! $rejected) {
                        $product->quantity -= $quantity[$product->id];
                        $product->number_of_purchases++;
                        $product->save();
                    }
                }
                $marketOrder->total_cost = $price;
                $marketOrder->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * edit order
     *
     * @param $data
     * @param int $user_id
     * @param Order $order
     * @return bool
     */
    public function editOrder($data, int $user_id, Order $order): bool
    {
        DB::beginTransaction();
        try {
            $marketOrders = Order::where('global_order_id', $order->id)->get();
            $order->update($data);
            foreach ($marketOrders as $marketOrder)
                $marketOrder->update($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * delete product in order
     *
     * @param Order $order
     * @param Product $product
     * @return bool
     */
    public function deleteProduct(Order $order, Product $product): bool
    {
        DB::beginTransaction();
        try {
            $order->count--;
            $marketOrder = Order::where('global_order_id', $order->id)
                ->where('market_id', $product->market_id)->get()->first();
            $product->number_of_purchases--;
            $quantity = DB::table('order_product')
                ->where('order_id', $marketOrder->id)
                ->where('product_id', $product->id)
                ->value('quantity');
            $marketOrder->products()->detach($product->id);
            $marketOrder->count--;
            $order->total_cost -= $quantity * $product->price;
            $product->quantity += $quantity;
            $order->save();
            $product->save();
            $marketOrder->save();
            if ($marketOrder->count == 0)
                $marketOrder->delete();
            if ($order->count == 0)
                $order->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * cancel order
     *
     * @param Order $order
     * @return bool
     */
    public function cancelOrder(Order $order) //: bool
    {
        DB::beginTransaction();
        try {
            if ($order->global_order_id)
                $products = $this->getMarketOrder($order);
            else
                $products = $this->getGlobalOrder($order);
            foreach ($products as $value) {
                $product = $this->productService->findById($value['id']);
                $product->quantity += $value['pivot']['quantity'];
                $product->number_of_purchases--;
                $product->save();
            }
            $data = [
                'status_id' => 5
            ];
            if (! $order->global_order_id) {
                $marketOrders = Order::where('global_order_id', $order->id)->get();
                foreach ($marketOrders as $marketOrder)
                    $marketOrder->update($data);
            }
            $order->update($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * get products of market Order
     *
     * @param Order $order
     * @param bool $withMarket
     */
    public function getMarketOrder(Order $order, bool $withMarket = false)
    {
        if (! $withMarket)
            return $order->products()
                ->select(
                    'products.id',
                    'products.name',
                    'products.image',
                    'products.category_id',
                    'products.price',
                    'products.market_id'
                )->get();
        else
            return $order->products()
                ->with('market')
                ->select(
                    'products.id',
                    'products.name',
                    'products.image',
                    'products.category_id',
                    'products.price',
                    'products.market_id'
                )->get();
    }

    /**
     * get global order
     *
     * @param Order $order
     * @param bool $withMarket
     */
    public function getGlobalOrder(Order $order, bool $withMarket = false)
    {
        $products = [];
        $marketOrders = Order::with('products')
            ->where('global_order_id', $order->id)->get();
        foreach ($marketOrders as $marketOrder) {
            if (! $withMarket)
                $order = $marketOrder->products()
                    ->select(
                        'products.id',
                        'products.name',
                        'products.image',
                        'products.category_id',
                        'products.price',
                        'products.market_id'
                    )->get();
            else
                $order = $marketOrder->products()
                    ->with('market')
                    ->select(
                        'products.id',
                        'products.name',
                        'products.image',
                        'products.category_id',
                        'products.price',
                        'products.market_id'
                    )->get();
            foreach ($order as $product)
                $products[] = $product;
        }
        return $products;
    }

    /**
     * get order
     * @param Order $order
     */
    public function getOrder(Order $order)
    {
        if ($order->global_order_id)
            $products = $this->getMarketOrder($order, true);
        else {
            $products = $this->getGlobalOrder($order, true);
        }
        $products = collect($products);

        $products->transform(function ($product) {
            $product->count = $product->pivot->quantity;
            $product->price = $product->price * $product->count;
            $product->market_name = $product->market->name;
            unset($product->market);
            unset($product->pivot);
            return $product;
        });

        return $products;
    }

    /**
     * get orders by status
     * @param int $status_id
     * @param bool $user
     */
    public function getOrdersByStatus(int $status_id, bool $user)
    {
        $orders = Order::where('status_id', $status_id)
            ->whereNull('global_order_id')
            ->get();

        return $orders;
    }

    /**
     * get orders for user
     * @param User $user
     */
    public function getOrders(User $user)
    {
        $orders = $user->orders()
            ->whereNull('global_order_id')
            ->get();

        return $orders;
    }
}
