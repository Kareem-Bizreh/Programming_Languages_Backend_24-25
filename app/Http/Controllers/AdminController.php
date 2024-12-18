<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreManagerRequest;
use App\Http\Requests\StoreMarketRequest;
use App\Models\Manager;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Services\AdminService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected $adminService, $orderService;

    public function __construct(AdminService $adminService, OrderService $orderService)
    {
        $this->adminService = $adminService;
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *       path="/admins/addMarket",
     *       summary="add new market",
     *       tags={"Admins"},
     *        @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               required={"name", "market_name_en" , "market_name_ar" , "password" , "password_confirmation"},
     *               @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Harry Potter"
     *             ),
     *             @OA\Property(
     *                 property="market_name_en",
     *                 type="string",
     *                 example="be order"
     *             ),
     *             @OA\Property(
     *                 property="market_name_ar",
     *                 type="string",
     *                 example="بي اوردر"
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
     *                    property="manager",
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

        $manager = $this->adminService->createManager($data, Role::Seller->value);
        if (! $manager) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        return response()->json([
            'message' => 'successfully create market',
            'manager' => $manager
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
     *               required={"name_en" , "name_ar"},
     *               @OA\Property(
     *                   property="name_en",
     *                   type="string",
     *                   example="bee order"
     *               ),
     *               @OA\Property(
     *                   property="name_ar",
     *                   type="string",
     *                   example="بي أوردر"
     *                ),
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
            'name_en' => 'required|string|max:20',
            'name_ar' => 'required|string|max:20'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $market = $this->adminService->editMarket($data, $manager);
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
     * @OA\Put(
     *       path="/admins/completeOrder/{order}",
     *       summary="complete order",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="order",
     *            in="path",
     *            required=true,
     *            description="order id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful completed",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="order completed seccessfully"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function completeOrder(Order $order)
    {
        if ($order->status_id >= 3)
            return response()->json(['message' => 'Forbidden'], 403);

        if ($this->orderService->completeOrder($order))
            return response()->json([
                'message' => 'order completed successfully'
            ], 200);
        return response()->json([
            'message' => 'order completed failed'
        ], 400);
    }

    /**
     * @OA\Put(
     *       path="/admins/deliverOrder/{order}",
     *       summary="deliver order",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="order",
     *            in="path",
     *            required=true,
     *            description="order id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful delivering",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="order delivering"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function deliverOrder(Order $order)
    {
        if ($order->status_id != 1)
            return response()->json(['message' => 'Forbidden'], 403);

        if ($this->adminService->deliverOrder($order))
            return response()->json([
                'message' => 'order delivering'
            ], 200);
        return response()->json([
            'message' => 'failed'
        ], 400);
    }

    /**
     * @OA\Put(
     *       path="/admins/rejectOrder/{order}",
     *       summary="reject order",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="order",
     *            in="path",
     *            required=true,
     *            description="order id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful rejected",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="order rejected seccessfully"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function rejectOrder(Order $order)
    {
        if ($order->status_id >= 3)
            return response()->json(['message' => 'Forbidden'], 403);

        if ($this->orderService->cancelOrder($order, 4))
            return response()->json([
                'message' => 'order rejected successfully'
            ], 200);
        return response()->json([
            'message' => 'order rejected failed'
        ], 400);
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
            'products' => $this->adminService->marketService->getTopProducts($perPage, $page, $market, 'en')
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getAllOrders",
     *       summary="get all orders",
     *       tags={"Admins"},
     *        @OA\Response(
     *          response=201, description="Successful get all orders",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="orders get seccessfully"
     *               ),
     *               @OA\Property(
     *                   property="orders",
     *                   type="string",
     *                   example="[]"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getAllOrders(Request $request)
    {
        return response()->json([
            'message' => 'orders get successfully',
            'orders' => $this->adminService->getAllOrders()
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getMarketOrders/{order}",
     *       summary="get all market orders of global order",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="order",
     *            in="path",
     *            required=true,
     *            description="order id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get all orders",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="orders get seccessfully"
     *               ),
     *               @OA\Property(
     *                   property="orders",
     *                   type="string",
     *                   example="[]"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getMarketOrders(Request $request, Order $order)
    {
        if ($order->global_order_id)
            return response()->json(['message' => 'Not Found'], 404);
        return response()->json([
            'message' => 'orders get successfully',
            'orders' => $this->adminService->getMarketOrders($order)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getMarketOrdersByStatus/{order}/{status}",
     *       summary="get all market orders of global order by status",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="order",
     *            in="path",
     *            required=true,
     *            description="order id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *       @OA\Parameter(
     *            name="status",
     *            in="path",
     *            required=true,
     *            description="status id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get all orders",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="orders get seccessfully"
     *               ),
     *               @OA\Property(
     *                   property="orders",
     *                   type="string",
     *                   example="[]"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getMarketOrdersByStatus(Request $request, Order $order, int $status)
    {
        if ($order->global_order_id)
            return response()->json(['message' => 'Not Found'], 404);
        return response()->json([
            'message' => 'orders get successfully',
            'orders' => $this->adminService->getMarketOrdersByStatus($order, $status)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getOrders/{market}",
     *       summary="get all orders of market",
     *       tags={"Admins"},
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
     *          response=201, description="Successful get all orders of market",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="orders get seccessfully"
     *               ),
     *               @OA\Property(
     *                   property="orders",
     *                   type="string",
     *                   example="[]"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getOrdersOfMarket(Request $request, Market $market)
    {
        return response()->json([
            'message' => 'orders get successfully',
            'orders' => $this->adminService->getOrdersOfMarket($market)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getOrdersByStatus/{status}",
     *       summary="get orders by status",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="status",
     *            in="path",
     *            required=true,
     *            description="status id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get orders",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="orders get seccessfully"
     *               ),
     *               @OA\Property(
     *                   property="orders",
     *                   type="string",
     *                   example="[]"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getOrdersByStatus(Request $request, int $status)
    {
        return response()->json([
            'message' => 'orders get successfully',
            'orders' => $this->adminService->getOrdersByStatus($status)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/admins/getOrder/{order}",
     *       summary="get order",
     *       tags={"Admins"},
     *       @OA\Parameter(
     *            name="order",
     *            in="path",
     *            required=true,
     *            description="order id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get order",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="order get seccessfully"
     *               ),
     *               @OA\Property(
     *                   property="price",
     *                   type="integer",
     *                   example=50000
     *               ),
     *               @OA\Property(
     *                   property="date",
     *                   type="string",
     *                   example="20/12/2024"
     *               ),
     *               @OA\Property(
     *                   property="location_name",
     *                   type="string",
     *                   example="potter"
     *               ),
     *               @OA\Property(
     *                   property="markets",
     *                   type="string",
     *                   example="[]"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getOrder(Request $request, Order $order)
    {
        if (auth('user-api')->id() != $order->user_id)
            return response()->json(['message' => 'Forbidden'], 403);

        return response()->json([
            'message' => 'order get successfully',
            'price' => $order->total_cost,
            'date' => $order->date,
            'location_name' => $order->location->name,
            'markets' => $this->orderService->getOrder($order, 'en')
        ], 200);
    }
}
