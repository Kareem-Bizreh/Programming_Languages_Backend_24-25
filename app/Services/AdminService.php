<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Manager;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\CategoryRepositry;
use Illuminate\Support\Facades\DB;

class AdminService
{
    public $marketService, $productService, $categoryRepositry;

    /**
     * Create a new class instance.
     */
    public function __construct(
        MarketService $marketService,
        ProductService $productService,
        CategoryRepositry $categoryRepositry
    ) {
        $this->marketService = $marketService;
        $this->productService = $productService;
        $this->categoryRepositry = $categoryRepositry;
    }

    /**
     * create seller account with his market
     *
     * @param array $data
     * @param string $role
     */
    public function createManager(array $data, string $role)
    {
        DB::beginTransaction();
        try {
            $data['role'] = $role;
            $manager = Manager::create($data);
            if ($role == Role::Seller->value) {
                $market =  $this->marketService->create([
                    'name_en' => $data['market_name_en'],
                    'name_ar' => $data['market_name_ar'],
                    'manager_id' => $manager->id
                ]);
                if (! $market) {
                    throw new \Exception("market create failed");
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
        return $manager;
    }

    /**
     * edit market
     *
     * @param array $data
     * @param Manager $manager
     */
    public function editMarket(array $data, Manager $manager)
    {
        DB::beginTransaction();
        try {
            $market = $manager->market;
            $market->update($data);
            $market->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
        return $market;
    }

    /**
     * delete market and his owner
     *
     * @param Manager $manager
     * @return bool
     */
    public function deleteManager(Manager $manager): bool
    {
        DB::beginTransaction();
        try {
            $this->marketService->deleteImage($manager->market);
            $manager->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * get all products order by number of purchases
     *
     */
    public function getTopProducts()
    {
        $products = Product::with('market')->orderBy('number_of_purchases', 'desc')->get();

        $products->transform(function ($product) {
            $product->market_name_en = $product->market['name_en'];
            $product->market_name_ar = $product->market['name_ar'];
            $product->category_en = $this->categoryRepositry->getById($product->category_id, 'en')->name;
            $product->category_ar = $this->categoryRepositry->getById($product->category_id, 'ar')->name;
            unset($product->market);
            unset($product->market_id);
            unset($product->category_id);
            return $product;
        });

        return $products;
    }

    /**
     * deliver order
     *
     * @param Order $order
     * @return bool
     */
    public function deliverOrder(Order $order): bool
    {
        DB::beginTransaction();
        try {
            $data = [
                'status_id' => 2
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
     * get all global orders
     */
    public function getOrders()
    {
        $orders = Order::whereNull('global_order_id')->select('id', 'date', 'status_id', 'total_cost')->get();

        foreach ($orders as $order) {
            $marketOrders = Order::with('market')->where('global_order_id', $order->id)->get();
            $order['products'] = [];
            foreach ($marketOrders as $marketOrder) {
                $products = $marketOrder->products->map(function ($product) use ($marketOrder) {
                    return [
                        'name_en' => $product->name_en,
                        'name_ar' => $product->name_ar,
                        'market_name_en' => $marketOrder->market->name_en,
                        'market_name_ar' => $marketOrder->market->name_ar,
                        'quantity' => $product->pivot->quantity,
                        'price' => $product->price,
                        'cost' => $product->price * $product->pivot->quantity
                    ];
                });

                $order['products'] = array_merge($order['products'], $products->toArray());
            }
        }

        return $orders;
    }

    /**
     * get market orders
     *
     * @param Order $order
     */
    public function getMarketOrders(Order $order)
    {
        return Order::where('global_order_id', $order->id)->get();
    }

    /**
     * get market orders by status
     *
     * @param Order $order
     * @param int $status_id
     */
    public function getMarketOrdersByStatus(Order $order, int $status_id)
    {
        return Order::where('global_order_id', $order->id)->where('status_id', $status_id)->get();
    }

    /**
     * get orders of market
     *
     * @param Market $market
     */
    public function getOrdersOfMarket(Market $market)
    {
        return $market->orders()->get();
    }

    /**
     * get orders by status
     * @param int $status_id
     */
    public function getOrdersByStatus(int $status_id)
    {
        $orders = Order::where('status_id', $status_id)
            ->whereNull('global_order_id')
            ->get();
        return $orders;
    }

    /**
     * get products by name
     *
     * @param string $name
     */
    public function getProductsByName(string $name)
    {
        $products = Product::with('market')
            ->where('name_en', 'LIKE', "%{$name}%")
            ->get();

        $products->transform(function ($product) {
            $product->market_name_en = $product->market['name_en'];
            $product->market_name_ar = $product->market['name_ar'];
            $product->category_en = $this->categoryRepositry->getById($product->category_id, 'en')->name;
            $product->category_ar = $this->categoryRepositry->getById($product->category_id, 'ar')->name;
            unset($product->market);
            unset($product->market_id);
            unset($product->category_id);
            return $product;
        });

        return $products;
    }

    /**
     * get all products with their id, name, market name, image, category and price
     */
    public function getProducts()
    {
        $products = Product::with('market')->get();

        $products->transform(function ($product) {
            $product->market_name_en = $product->market['name_en'];
            $product->market_name_ar = $product->market['name_ar'];
            $product->category_en = $this->categoryRepositry->getById($product->category_id, 'en')->name;
            $product->category_ar = $this->categoryRepositry->getById($product->category_id, 'ar')->name;
            unset($product->market);
            unset($product->market_id);
            unset($product->category_id);
            return $product;
        });

        return $products;
    }

    public function getAdmins()
    {
        return Manager::where('role', Role::Admin->value)->get();
    }
}