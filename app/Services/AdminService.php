<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Manager;
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
                    'name' => $data['market_name'],
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
     * @param string $name
     * @param Manager $manager
     */
    public function editMarket(string $name, Manager $manager)
    {
        DB::beginTransaction();
        try {
            $market = $manager->market;
            $market->update(['name' => $name]);
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
}