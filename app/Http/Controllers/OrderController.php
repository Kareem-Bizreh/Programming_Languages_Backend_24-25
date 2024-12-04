<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
     *         @OA\Property(property="total_cost", type="integer", example=25000),
     *         @OA\Property(property="location_id", type="integer", example=1),
     *         @OA\Property(
     *           property="products",
     *           type="array",
     *           @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="product_id",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="quantity",
     *                  type="integer"
     *              )
     *           ),
     *           example={{
     *             "product_id": 1,
     *             "quantity": 5
     *              }, {
     *             "product_id": 4,
     *             "quantity": 3
     *           }}
     *         )
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
            'total_cost' => 'required|integer|min:0',
            'location_id' => 'required|exists:locations,id',
            'products' => 'required|array'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }

        foreach (request('products') as $product) {
            $product = Validator::make($product, [
                'product_id' => 'required|min:1|exists:products,id',
                'quantity' => 'required|min:1|integer'
            ]);

            if ($product->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $product->errors(),
                ], 400);
            }
        }
        if (! $this->orderService->createOrder($request->all(), auth('user-api')->id())) {
            return response()->json([
                'message' => 'faild to add order , check the quantity of each product.'
            ], 400);
        }

        return response()->json([
            'message' => 'order added successfully'
        ], 200);
    }

    /**
     * @OA\Delete(
     *       path="/orders/deleteOrder/{order}",
     *       summary="delete order",
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
     *          response=201, description="Successful deleted",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="order deleted seccessfully"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function deleteOrder(Order $order)
    {
        if ($this->orderService->deleteOrder($order))
            return response()->json([
                'message' => 'order deleted successfully'
            ], 200);
        return response()->json([
            'message' => 'order deleted failed'
        ], 200);
    }
}