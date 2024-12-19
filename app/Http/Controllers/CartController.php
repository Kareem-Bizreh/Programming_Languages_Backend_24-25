<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * @OA\Post(
     *       path="/carts/addProduct/{product}",
     *       summary="add product to cart",
     *       tags={"Carts"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
     *       @OA\Parameter(
     *            name="product",
     *            in="path",
     *            required=true,
     *            description="product id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"count"},
     *                  @OA\Property(
     *                    property="count",
     *                    type="integer",
     *                    example=5
     *                  ),
     *          )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful add product to cart",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully add product to cart"
     *               )
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function addProduct(Request $request, Product $product)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = Validator::make($request->all(), [
            'count' => 'required|integer'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();
        if ($this->cartService->changeProduct($product, auth('user-api')->user()->cart, $data['count']))
            return response()->json([
                'message' => __('messages.product_add_success')
            ], 200);
        else
            return response()->json([
                'message' => __('messages.product_add_failed')
            ], 400);
    }

    /**
     * @OA\Put(
     *       path="/carts/plusProductOne/{product}",
     *       summary="plus product count by one",
     *       tags={"Carts"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *          response=201, description="Successful plus product count",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully plus product count"
     *               )
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function plusProductOne(Request $request, Product $product)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        if ($this->cartService->changeProduct($product, auth('user-api')->user()->cart, 1))
            return response()->json([
                'message' => __('messages.product_add_success')
            ], 200);
        else
            return response()->json([
                'message' => __('messages.product_add_failed')
            ], 400);
    }

    /**
     * @OA\Put(
     *       path="/carts/minusProductOne/{product}",
     *       summary="minus product count by one",
     *       tags={"Carts"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *          response=201, description="Successful minus product count",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully minus product count"
     *               )
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function minusProductOne(Request $request, Product $product)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        if ($this->cartService->changeProduct($product, auth('user-api')->user()->cart, -1))
            return response()->json([
                'message' => __('messages.product_delete_success')
            ], 200);
        else
            return response()->json([
                'message' => __('messages.product_delete_failed')
            ], 400);
    }

    /**
     * @OA\Delete(
     *       path="/carts/deleteProduct/{product}",
     *       summary="delete product from cart",
     *       tags={"Carts"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *          response=201, description="Successful delete product",
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
    public function deleteProduct(Request $request, Product $product)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        if ($this->cartService->deleteProduct($product, auth('user-api')->user()->cart))
            return response()->json([
                'message' => __('messages.product_delete_success')
            ], 200);
        else
            return response()->json([
                'message' => __('messages.product_delete_failed')
            ], 400);
    }

    /**
     * @OA\Delete(
     *       path="/carts/clearCart",
     *       summary="clear cart",
     *       tags={"Carts"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
     *        @OA\Response(
     *          response=201, description="Successful clear cart",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully clear cart"
     *               )
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function clearCart(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        if ($this->cartService->clearCart(auth('user-api')->user()->cart))
            return response()->json([
                'message' => __('messages.cart_clear_success')
            ], 200);
        else
            return response()->json([
                'message' => __('messages.cart_clear_failed')
            ], 400);
    }

    /**
     * @OA\Get(
     *       path="/carts/getCart",
     *       summary="get cart",
     *       tags={"Carts"},
     *       @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get cart",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get cart"
     *               ),
     *               @OA\Property(
     *                   property="cart",
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
    public function getCart(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        $cart = auth('user-api')->user()->cart;
        return response()->json([
            'message' => 'successfully get cart',
            'cart' => [
                'count' => $cart->count,
                'total_cost' => $cart->total_cost,
                'products' => $this->cartService->getCart($cart, $lang)
            ]
        ], 200);
    }
}