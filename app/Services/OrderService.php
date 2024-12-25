<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\CategoryRepositry;
use App\Repositories\StatusRepositry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $productService, $cartService, $statusRepositry, $categoryRepositry, $locationService;

    public function __construct(
        ProductService $productService,
        CartService $cartService,
        StatusRepositry $statusRepositry,
        CategoryRepositry $categoryRepositry,
        LocationService $locationService
    ) {
        $this->productService = $productService;
        $this->cartService = $cartService;
        $this->statusRepositry = $statusRepositry;
        $this->categoryRepositry = $categoryRepositry;
        $this->locationService = $locationService;
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
     * @param int $status_id
     * @return bool
     */
    public function cancelOrder(Order $order, int $status_id): bool
    {
        DB::beginTransaction();
        try {
            if ($order->global_order_id)
                $products = $this->getMarketOrder($order, 'en');
            else
                $products = $this->getGlobalOrder($order, 'en');
            foreach ($products as $value) {
                $product = $this->productService->findById($value['id']);
                $product->quantity += $value['pivot']['quantity'];
                $product->number_of_purchases--;
                $product->save();
            }
            $data = [
                'status_id' => $status_id
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
     * @param string $lang
     * @param bool $withMarket
     */
    public function getMarketOrder(Order $order, string $lang, bool $withMarket = false)
    {
        if (! $withMarket)
            return $order->products()
                ->select(
                    'products.id',
                    "products.name_{$lang} as name",
                    'products.category_id',
                    'products.price',
                    'products.market_id'
                )->get();
        else
            return $order->products()
                ->with('market')
                ->select(
                    'products.id',
                    "products.name_{$lang} as name",
                    'products.category_id',
                    'products.price',
                    'products.market_id'
                )->get();
    }

    /**
     * get global order
     *
     * @param Order $order
     * @param string $lang
     * @param bool $withMarket
     */
    public function getGlobalOrder(Order $order, string $lang, bool $withMarket = false)
    {
        $products = [];
        $marketOrders = Order::with('products')
            ->where('global_order_id', $order->id)->get();
        foreach ($marketOrders as $marketOrder) {
            if (! $withMarket)
                $order = $marketOrder->products()
                    ->select(
                        'products.id',
                        "products.name_{$lang} as name",
                        'products.category_id',
                        'products.price',
                        'products.market_id'
                    )->get();
            else
                $order = $marketOrder->products()
                    ->with('market')
                    ->select(
                        'products.id',
                        "products.name_{$lang} as name",
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
     * @param string $lang
     */
    public function getOrder(Order $order, string $lang)
    {
        if ($order->global_order_id)
            $products = $this->getMarketOrder($order, $lang, true);
        else {
            $products = $this->getGlobalOrder($order, $lang, true);
        }
        $markets = [];
        foreach ($products as $product) {
            $product->count = $product->pivot->quantity;
            $product->total = $product->price * $product->count;
            $product->category = $this->categoryRepositry->getById($product->category_id, $lang)->name;
            if (!isset($markets[$product->id]))
                $markets[$product->market_id] = [
                    'name' => $product->market['name_' . $lang],
                    'products' => []
                ];
            $markets[$product->market_id]['products'][] = $product;
            unset($product->market);
            unset($product->market_id);
            unset($product->category_id);
            unset($product->pivot);
        }

        return collect($markets);
    }

    /**
     * get orders by status
     * @param int $status_id
     * @param int $user_id
     * @param string $lang
     */
    public function getOrdersByStatus(int $status_id, int $user_id, string $lang)
    {
        $orders = Order::with('location')
            ->where('status_id', $status_id)
            ->where('user_id', $user_id)
            ->whereNull('global_order_id')
            ->get();

        $orders->transform(function ($order) use ($lang) {
            $order->delivery_cost = $order->location->cost;
            $order->products_cost = $order->total_cost;
            unset($order->total_cost);
            $order->total_cost = $order->delivery_cost + $order->products_cost;
            $order->status = $this->statusRepositry->getStatusById($order->status_id, $lang)->name;
            unset($order->status_id);
            unset($order->market_id);
            unset($order->global_order_id);
            unset($order->location);
            return $order;
        });
        return $orders;
    }

    /**
     * get orders by status
     * @param int $status_id
     * @param Market $market
     */
    public function getOrdersByStatusSeller(int $status_id, Market $market)
    {
        $orders = $market->orders()
            ->with(['products' => function ($query) {
                $query->select(
                    'products.id',
                    'products.name_en',
                    'products.name_ar',
                    'products.price',
                    'order_product.quantity',
                );
            }])
            ->where('status_id', $status_id)
            ->select('id', 'date', 'status_id', 'total_cost')->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'products' => $order->products->map(function ($product) {
                        return [
                            'name_en' => $product->name_en,
                            'name_ar' => $product->name_ar,
                            'quantity' => $product->pivot->quantity,
                            'price' => $product->price,
                            'cost' => $product->price * $product->pivot->quantity
                        ];
                    }),
                    'date' => $order->date,
                    'status_id' => $order->status_id,
                    'total_cost' => $order->total_cost
                ];
            });

        return $orders;
    }

    /**
     * get orders for user
     * @param User $user
     * @param string $lang
     */
    public function getOrdersForUser(User $user, string $lang)
    {
        $orders = $user->orders()
            ->with('location')
            ->whereNull('global_order_id')
            ->get();

        $orders->transform(function ($order) use ($lang) {
            $order->delivery_cost = $order->location->cost;
            $order->products_cost = $order->total_cost;
            unset($order->total_cost);
            $order->total_cost = $order->delivery_cost + $order->products_cost;
            $order->status = $this->statusRepositry->getStatusById($order->status_id, $lang)->name;
            unset($order->market_id);
            unset($order->status_id);
            unset($order->global_order_id);
            unset($order->location);
            return $order;
        });

        return $orders;
    }

    /**
     * Get orders for a market
     * @param Market $market
     */
    public function getOrdersForMarket(Market $market)
    {
        $orders = $market->orders()
            ->with(['products' => function ($query) {
                $query->select(
                    'products.id',
                    'products.name_en',
                    'products.name_ar',
                    'products.price',
                    'order_product.quantity',
                );
            }])
            ->select('id', 'date', 'status_id', 'total_cost')->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'products' => $order->products->map(function ($product) {
                        return [
                            'name_en' => $product->name_en,
                            'name_ar' => $product->name_ar,
                            'quantity' => $product->pivot->quantity,
                            'price' => $product->price,
                            'cost' => $product->price * $product->pivot->quantity
                        ];
                    }),
                    'date' => $order->date,
                    'status_id' => $order->status_id,
                    'total_cost' => $order->total_cost
                ];
            });

        return $orders;
    }


    /**
     * complete order
     *
     * @param Order $order
     * @return bool
     */
    public function completeOrder(Order $order): bool
    {
        DB::beginTransaction();
        try {
            $data = [
                'status_id' => 3
            ];
            if (! $order->global_order_id) {
                $marketOrders = Order::where('global_order_id', $order->id)->get();
                foreach ($marketOrders as $marketOrder)
                    if ($marketOrder->status_id != 3)
                        return new \Exception("order not completed");
            }
            $order->update($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }
}