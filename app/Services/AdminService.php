<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Manager;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AdminService
{
    public $marketService, $productService;

    /**
     * Create a new class instance.
     */
    public function __construct(MarketService $marketService, ProductService $productService)
    {
        $this->marketService = $marketService;
        $this->productService = $productService;
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
     * @param int $perPage
     * @param int $page
     */
    public function getTopProducts(int $perPage, int $page)
    {
        $products = Product::orderBy('number_of_purchases', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'currentPageItems' => $products->items(),
            'total' => $products->total(),
            'perPage' => $products->perPage(),
            'currentPage' => $products->currentPage(),
            'lastPage' => $products->lastPage(),
        ];
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
    public function getAllOrders()
    {
        return Order::whereNull('global_order_id')->get();
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
}
