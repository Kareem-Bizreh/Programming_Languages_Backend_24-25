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
                'message' => 'faild to add order , check from each product.'
            ], 400);
        }

        return response()->json([
            'message' => 'order added successfully'
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/orders/editOrder/{order}",
     *     summary="edit order",
     *     tags={"Orders"},
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
            return response()->json(['message' => 'Forbidden'], 403);

        if (! $this->orderService->editOrder($request->all(), auth('user-api')->id(), $order)) {
            return response()->json([
                'message' => 'faild to edit order'
            ], 400);
        }

        return response()->json([
            'message' => 'order edited successfully'
        ], 200);
    }

    /**
     * @OA\Put(
     *       path="/orders/cancelOrder/{order}",
     *       summary="cancel order",
     *       tags={"Orders"},
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
    public function cancelOrder(Order $order)
    {
        if (auth('user-api')->id() != $order->user_id || $order->status_id > 2)
            return response()->json(['message' => 'Forbidden'], 403);

        if ($this->orderService->cancelOrder($order, 5))
            return response()->json([
                'message' => 'order canceled successfully'
            ], 200);
        return response()->json([
            'message' => 'order canceled failed'
        ], 400);
    }

    /**
     * @OA\Delete(
     *       path="/orders/deleteProduct/{order}/{product}",
     *       summary="delete product",
     *       tags={"Orders"},
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
    public function deleteProduct(Order $order, Product $product)
    {
        if (auth('user-api')->id() != $order->user_id || $order->status_id != 1)
            return response()->json(['message' => 'Forbidden'], 403);

        if ($this->orderService->deleteProduct($order, $product))
            return response()->json([
                'message' => 'product deleted successfully'
            ], 200);
        return response()->json([
            'message' => 'product deleted failed'
        ], 400);
    }

    /**
     * @OA\Get(
     *       path="/orders/getOrders",
     *       summary="get orders for user",
     *       tags={"Orders"},
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
        return response()->json([
            'message' => 'orders get successfully',
            'orders' => $this->orderService->getOrdersForUser(auth('user-api')->user())
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/orders/getOrdersByStatus/{status}",
     *       summary="get orders by status",
     *       tags={"Orders"},
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
            'orders' => $this->orderService->getOrdersByStatus($status, auth('manager-api')->id())
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/orders/getOrder/{order}",
     *       summary="get order",
     *       tags={"Orders"},
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
     *                   property="order",
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
            'products' => $this->orderService->getOrder($order)
        ], 200);
    }
}