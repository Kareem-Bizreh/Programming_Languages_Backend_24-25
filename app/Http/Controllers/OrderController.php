<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *     path="/orders/createOrder",
     *     summary="create order",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="date", type="string", example="2024/12/20"),
     *         @OA\Property(property="location_id", type="integer", example=1),
     *      )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successfully order created",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="order added successfully"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function createOrder(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|string'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => $data->errors()->first(),
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();
        if (! $this->orderService->createOrder($data, auth('user-api')->user()->cart, auth('user-api')->id())) {
            return response()->json([
                'message' => __('messages.order_failed')
            ], 400);
        }

        return response()->json([
            'message' => __('messages.order_success')
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/orders/editOrder/{order}",
     *     summary="edit order",
     *     tags={"Orders"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
     *       @OA\Parameter(
     *            name="order",
     *            in="path",
     *            required=true,
     *            description="order id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="location_id", type="integer", example=1),
     *      )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successfully order edited",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="order edited successfully"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function editOrder(Request $request, Order $order)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => $data->errors()->first(),
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        if (auth('user-api')->id() != $order->user_id || $order->status_id != 1)
            return response()->json(['message' => __('messages.forbidden')], 403);

        if (! $this->orderService->editOrder($request->all(), auth('user-api')->id(), $order)) {
            return response()->json([
                'message' => __('messages.failed')
            ], 400);
        }

        return response()->json([
            'message' => __('messages.success')
        ], 200);
    }

    /**
     * @OA\Put(
     *       path="/orders/cancelOrder/{order}",
     *       summary="cancel order",
     *       tags={"Orders"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *          response=201, description="Successful canceled",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="order canceled seccessfully"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function cancelOrder(Request $request, Order $order)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        if (auth('user-api')->id() != $order->user_id || $order->status_id > 2)
            return response()->json(['message' => __('messages.forbidden')], 403);

        if ($this->orderService->cancelOrder($order, 5))
            return response()->json([
                'message' => __('messages.order_cancel_success')
            ], 200);
        return response()->json([
            'message' => __('messages.order_cancel_failed')
        ], 400);
    }

    /**
     * @OA\Delete(
     *       path="/orders/deleteProduct/{order}/{product}",
     *       summary="delete product",
     *       tags={"Orders"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *                   example="product deleted seccessfully"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function deleteProduct(Request $request, Order $order, Product $product)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        if (auth('user-api')->id() != $order->user_id || $order->status_id != 1)
            return response()->json(['message' => __('messages.forbidden')], 403);

        if ($this->orderService->deleteProduct($order, $product))
            return response()->json([
                'message' => __('messages.product_delete_success')
            ], 200);
        return response()->json([
            'message' => __('messages.product_delete_failed')
        ], 400);
    }

    /**
     * @OA\Get(
     *       path="/orders/getOrders",
     *       summary="get orders for user",
     *       tags={"Orders"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
    public function getOrders(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        return response()->json([
            'message' => 'orders get successfully',
            'orders' => $this->orderService->getOrdersForUser(auth('user-api')->user(), $lang)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/orders/getOrdersByStatus/{status}",
     *       summary="get orders by status",
     *       tags={"Orders"},
     *      @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
        $lang = $request->header('Accept-Language', 'en');
        return response()->json([
            'message' => 'orders get successfully',
            'orders' => $this->orderService->getOrdersByStatus($status, auth('user-api')->id(), $lang)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/orders/getOrder/{order}",
     *       summary="get order",
     *       tags={"Orders"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *                   property="delivery_cost",
     *                   type="integer",
     *                   example=500
     *               ),
     *               @OA\Property(
     *                   property="products_cost",
     *                   type="integer",
     *                   example=20000
     *               ),
     *               @OA\Property(
     *                   property="total_cost",
     *                   type="integer",
     *                   example=20500
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
        $lang = $request->header('Accept-Language', 'en');
        if (auth('user-api')->id() != $order->user_id)
            return response()->json(['message' => 'Forbidden'], 403);

        return response()->json([
            'message' => 'order get successfully',
            'delivery_cost' => $order->location->cost,
            'products_cost' => $order->total_cost,
            'total_cost' => $order->location->cost + $order->total_cost,
            'date' => $order->date,
            'location_name' => $order->location->name,
            'markets' => $this->orderService->getOrder($order, $lang)
        ], 200);
    }
}
