<?php

namespace App\Services;

use App\Models\Market;
use Illuminate\Support\Facades\DB;

class MarketService
{
    protected $categoryRepositry;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * create market
     *
     * @param array $data
     */
    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $market = Market::create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
        return $market;
    }

    /**
     * edit market
     *
     * @param array $data
     * @param Market $market
     */
    public function edit(array $data, Market $market)
    {
        DB::beginTransaction();
        try {
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
     * find market by id
     *
     * @param int $id
     * @return Market
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Market
    {
        return Market::findOrFail($id);
    }

    /**
     * get all markets
     *
     * @param int $perPage
     * @param int $page
     */
    public function getAll(int $perPage, int $page)
    {
        $market = Market::paginate($perPage, ['*'], 'page', $page);

        return [
            'currentPageItems' => $market->items(),
            'total' => $market->total(),
            'perPage' => $market->perPage(),
            'currentPage' => $market->currentPage(),
            'lastPage' => $market->lastPage(),
        ];
    }

    /**
     * get All products for market
     *
     * @param int $perPage
     * @param int $page
     * @param Market $market
     */
    public function getProductsForMarket(int $perPage, int $page, Market $market)
    {
        $products = $market->products()->paginate($perPage, ['*'], 'page', $page);

        return [
            'currentPageItems' => $products->items(),
            'total' => $products->total(),
            'perPage' => $products->perPage(),
            'currentPage' => $products->currentPage(),
            'lastPage' => $products->lastPage(),
        ];
    }

    /**
     * get all products order by number of purchases
     *
     * @param int $perPage
     * @param int $page
     * @param Market $market
     */
    public function getTopProducts(int $perPage, int $page, Market $market)
    {
        $products = $market->products()->orderBy('number_of_purchases', 'desc')
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
