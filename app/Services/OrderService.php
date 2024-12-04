<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * create order
     *
     * @param $data
     * @param int $user_id
     * @return bool
     */
    public function createOrder($data, int $user_id): bool
    {
        DB::beginTransaction();
        try {
            $products = $data['products'];
            $globalOrder = Order::create([
                'user_id' => $user_id,
                'status_id' => 1,
                'location_id' => $data['location_id'],
                'total_cost' => $data['total_cost'],
                'count' => count($products)
            ]);
            $marketsProduct = [];
            foreach ($products as $value) {
                $product = $this->productService->findById($value['product_id']);
                if (! $product) {
                    throw new \Exception("product not found");
                }
                if ($product->quantity < $value['quantity']) {
                    throw new \Exception("quantity not available");
                }
                $market_id = $product->market_id;
                $product->quantity -= $value['quantity'];
                $product->number_of_purchases++;
                $product->save();

                if (!isset($marketsProduct[$market_id])) {
                    $marketsProduct[$market_id] = [];
                }

                $marketsProduct[$market_id][] = [
                    'id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $value['quantity']
                ];
                $globalOrder->products()->attach($product->id, ['quantity' => $value['quantity']]);
            }

            foreach ($marketsProduct as $market_id => $products) {
                $price = 0;
                $marketOrder = Order::create([
                    'user_id' => $user_id,
                    'market_id' => $market_id,
                    'status_id' => 1,
                    'location_id' => $data['location_id'],
                    'count' => count($products),
                    'global_order_id' => $globalOrder->id
                ]);
                foreach ($products as $product) {
                    $price += $product['price'] * $product['quantity'];
                    $marketOrder->products()->attach($product['id'], ['quantity' => $product['quantity']]);
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
     * delete order
     *
     * @param Order $order
     * @return bool
     */
    public function deleteOrder(Order $order): bool
    {
        DB::beginTransaction();
        try {
            $order->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }
}