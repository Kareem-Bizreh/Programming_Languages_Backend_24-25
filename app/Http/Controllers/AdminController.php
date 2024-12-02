<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\StoreMarketRequest;
use App\Models\Manager;
use App\Models\Market;
use App\Models\Product;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * @OA\Post(
     *       path="/admins/addMarket",
     *       summary="add new market",
     *       tags={"Admins"},
     *        @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               required={"name", "market_name" , "password" , "password_confirmation"},
     *               @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Harry Potter"
     *             ),
     *             @OA\Property(
     *                 property="market_name",
     *                 type="string",
     *                 example="be order"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 example="password123"
     *             )
     *           )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful added",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully create market"
     *               ),
     *               @OA\Property(
     *                    property="market",
     *                    type="string",
     *                     example="[]"
     *                ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function addMarket(StoreMarketRequest $request)
    {
        $data = $request->validated();

        $market = $this->adminService->createManager($data, Role::Seller->value);
        if (! $market) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        return response()->json([
            'message' => 'successfully create market',
            'market' => $market
        ], 200);
    }

    /**
     * @OA\Post(
     *       path="/admins/addAdmin",
     *       summary="add new admin",
     *       tags={"Admins"},
     *        @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               required={"name", "password" , "password_confirmation"},
     *               @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Admin2"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 example="password"
     *             )
     *           )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful added",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully create admin"
     *               ),
     *               @OA\Property(
     *                    property="market",
     *                    type="string",
     *                     example="[]"
     *                ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function addAdmin(StoreManagerRequest $request)
    {
        $data = $request->validated();

        $admin = $this->adminService->createManager($data, Role::Admin->value);
        if (! $admin) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        return response()->json([
            'message' => 'successfully create admin',
            'admin' => $admin
        ], 200);
    }

    /**
     * @OA\Put(
     *       path="/admins/editMarket/{manager}",
     *       summary="edit on market",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="manager",
     *            in="path",
     *            required=true,
     *            description="manager id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               required={"new_market_name"},
     *               @OA\Property(
     *                   property="new_market_name",
     *                   type="string",
     *                   example="bee order"
     *               )
     *           )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful edited",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully edit market"
     *               ),
     *               @OA\Property(
     *                    property="market",
     *                    type="string",
     *                     example="[]"
     *                ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function editMarket(Request $request, Manager $manager)
    {
        $data = Validator::make($request->all(), [
            'new_market_name' => 'required|string|max:20'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $market = $this->adminService->editMarket($data['new_market_name'], $manager);
        if (! $market) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        return response()->json([
            'message' => 'successfully edit market',
            'market' => $market
        ], 200);
    }

    /**
     * @OA\Delete(
     *       path="/admins/deleteMarket/{manager}",
     *       summary="delete market and his owner",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="manager",
     *            in="path",
     *            required=true,
     *            description="manager id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful deleted",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully delete market"
     *               )
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function deleteMarket(Manager $manager)
    {
        if (! $this->adminService->deleteManager($manager)) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        return response()->json([
            'message' => 'successfully delete market'
        ], 200);
    }

    /**
     * @OA\Delete(
     *       path="/admins/delete/{product}",
     *       summary="delete product",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="product",
     *            in="path",
     *            required=true,
     *            description="product id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful deleted",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully delete product"
     *               )
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function deleteProduct(Product $product)
    {
        if (! $this->adminService->productService->deleteProduct($product)) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        return response()->json([
            'message' => 'successfully delete product'
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getMarkets",
     *       summary="get all markets",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="perPage",
     *            in="query",
     *            required=true,
     *            description="number of records per page",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *       @OA\Parameter(
     *            name="page",
     *            in="query",
     *            required=true,
     *            description="number of page",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get all markets",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get all markets"
     *               ),
     *               @OA\Property(
     *                    property="markets",
     *                    type="string",
     *                     example="[]"
     *                ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getMarkets(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get all markets',
            'markets' => $this->adminService->marketService->getAll($perPage, $page)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getProducts/{market}",
     *       summary="get all products for market",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="perPage",
     *            in="query",
     *            required=true,
     *            description="number of records per page",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *       @OA\Parameter(
     *            name="page",
     *            in="query",
     *            required=true,
     *            description="number of page",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *       @OA\Parameter(
     *            name="market",
     *            in="path",
     *            required=true,
     *            description="market id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get all products for market",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get all products for market"
     *               ),
     *               @OA\Property(
     *                    property="products",
     *                    type="string",
     *                     example="[]"
     *                ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getProductsForMarket(Request $request, Market $market)
    {
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get all products for market',
            'products' => $this->adminService->marketService->getProductsForMarket($perPage, $page, $market)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getTopProducts",
     *       summary="get products order by number of purchases",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="perPage",
     *            in="query",
     *            required=true,
     *            description="number of records per page",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *       @OA\Parameter(
     *            name="page",
     *            in="query",
     *            required=true,
     *            description="number of page",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get products order by number of purchases",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get products order by number of purchases"
     *               ),
     *               @OA\Property(
     *                    property="products",
     *                    type="string",
     *                     example="[]"
     *                ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getTopProducts(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products order by number of purchases',
            'products' => $this->adminService->getTopProducts($perPage, $page)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getTopProducts/{market}",
     *       summary="get products order by number of purchases for market",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="perPage",
     *            in="query",
     *            required=true,
     *            description="number of records per page",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *       @OA\Parameter(
     *            name="page",
     *            in="query",
     *            required=true,
     *            description="number of page",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *       @OA\Parameter(
     *            name="market",
     *            in="path",
     *            required=true,
     *            description="market id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get products order by number of purchases for market",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get products order by number of purchases for market"
     *               ),
     *               @OA\Property(
     *                    property="products",
     *                    type="string",
     *                     example="[]"
     *                ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getTopProductsForMarket(Request $request, Market $market)
    {
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products order by number of purchases for market',
            'products' => $this->adminService->marketService->getTopProducts($perPage, $page, $market)
        ], 200);
    }
}