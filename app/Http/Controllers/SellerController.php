<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Services\MarketService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    protected $productService, $marketService, $orderService;

    public function __construct(ProductService $productService, MarketService $marketService, OrderService $orderService)
    {
        $this->productService = $productService;
        $this->marketService = $marketService;
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *       path="/sellers/addProduct",
     *       summary="add new product",
     *       tags={"Sellers"},
     *        @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               required={"name_en" , "name_ar" , "category_id" , "quantity" , "price"},
     *               @OA\Property(
     *                 property="name_en",
     *                 type="string",
     *                 example="Choko Cake"
     *             ),
     *             @OA\Property(
     *                 property="name_ar",
     *                 type="string",
     *                 example="كعكة شوكولا"
     *             ),
     *             @OA\Property(
     *                 property="category_id",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 example=5
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="integer",
     *                 example=5000
     *             ),
     *             @OA\Property(
     *                 property="description_en",
     *                 type="string",
     *                 example="for birthdays"
     *             ),
     *             @OA\Property(
     *                 property="description_ar",
     *                 type="string",
     *                 example="من اجل اعياد الميلاد"
     *             )
     *           )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful added",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully create product"
     *               ),
     *               @OA\Property(
     *                    property="product",
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
    public function addProduct(StoreProductRequest $request)
    {
        $data = $request->validated();

        $product = $this->productService->createProduct($data, auth('manager-api')->user());
        if (! $product) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        return response()->json([
            'message' => 'successfully create product',
            'product' => $product
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/sellers/uploadImage",
     *     summary="Upload market image",
     *     tags={"Sellers"},
     *     @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                  property="image",
     *                  type="string",
     *                  format="binary",
     *               ),
     *           ),
     *       )
     *   ),
     *     @OA\Response(
     *      response=200, description="Successful uploaded an image",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="uploaded market image successfully"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function uploadImageForMarket(Request $request)
    {
        $data = Validator::make($request->all(), [
            'image' => 'required|image|file'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $seller = auth('manager-api')->user();

        if ($this->marketService->uploadImage($seller->market, $request->file('image')))
            return response()->json([
                'message' => 'uploaded market image successfully'
            ], 200);

        return response()->json([
            'error' => 'No image uploaded.',
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/sellers/uploadImage/{product}",
     *     summary="Upload product image",
     *     tags={"Sellers"},
     *       @OA\Parameter(
     *            name="product",
     *            in="path",
     *            required=true,
     *            description="product id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *     @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                  property="image",
     *                  type="string",
     *                  format="binary",
     *               ),
     *           ),
     *       )
     *   ),
     *     @OA\Response(
     *      response=200, description="Successful uploaded an image",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="uploaded product image successfully"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function uploadImageForProduct(Request $request, Product $product)
    {
        $data = Validator::make($request->all(), [
            'image' => 'required|image|file'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $seller = auth('manager-api')->user();

        if ($product->market != $seller->market)
            return response()->json(['message' => 'Forbidden'], 403);

        if ($this->productService->uploadImage($product, $request->file('image')))
            return response()->json([
                'message' => 'uploaded product image successfully'
            ], 200);

        return response()->json([
            'error' => 'No image uploaded.',
        ], 400);
    }

    /**
     * @OA\Put(
     *       path="/sellers/edit/{product}",
     *       summary="edit product",
     *       tags={"Sellers"},
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
     *           required=true,
     *           @OA\JsonContent(
     *               required={"name_en" , "name_ar" , "category_id" , "quantity" , "price"},
     *               @OA\Property(
     *                 property="name_en",
     *                 type="string",
     *                 example="Choko Cake"
     *             ),
     *             @OA\Property(
     *                 property="name_ar",
     *                 type="string",
     *                 example="كعكة شوكولا"
     *             ),
     *             @OA\Property(
     *                 property="category_id",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 example=3
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="integer",
     *                 example=10000
     *             ),
     *             @OA\Property(
     *                 property="description_en",
     *                 type="string",
     *                 example="for birthdays"
     *             ),
     *             @OA\Property(
     *                 property="description_ar",
     *                 type="string",
     *                 example="من اجل اعياد الميلاد"
     *             )
     *           )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful edited",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully edit product"
     *               ),
     *               @OA\Property(
     *                    property="product",
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
    public function editProduct(StoreProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $seller = auth('manager-api')->user();

        if ($product->market != $seller->market)
            return response()->json(['message' => 'Forbidden'], 403);

        $new_product = $this->productService->editProduct($product, $data);
        if (! $new_product) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        unset($new_product->market);
        return response()->json([
            'message' => 'successfully edit product',
            'product' => $new_product
        ], 200);
    }

    /**
     * @OA\Put(
     *       path="/sellers/completeOrder/{order}",
     *       summary="complete order",
     *       tags={"Sellers"},
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
        $seller = auth('manager-api')->user();
        if ($order->status_id >= 3 || $order->market != $seller->market)
            return response()->json(['message' => 'Forbidden'], 403);

        if ($this->orderService->completeOrder($order, 3))
            return response()->json([
                'message' => 'order completed successfully'
            ], 200);
        return response()->json([
            'message' => 'order completed failed'
        ], 400);
    }

    /**
     * @OA\Put(
     *       path="/sellers/rejectOrder/{order}",
     *       summary="reject order",
     *       tags={"Sellers"},
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
        $seller = auth('manager-api')->user();
        if ($order->status_id >= 3 || $order->market != $seller->market)
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
     *       path="/sellers/delete/{product}",
     *       summary="delete product",
     *       tags={"Sellers"},
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
        $seller = auth('manager-api')->user();
        if ($product->market != $seller->market)
            return response()->json(['message' => 'Forbidden'], 403);

        if (! $this->productService->deleteProduct($product)) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }
        return response()->json([
            'message' => 'successfully delete product'
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/sellers/deleteImage",
     *     summary="delete market image",
     *     tags={"Sellers"},
     *     @OA\Response(
     *      response=200, description="delete the market image",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function deleteImageForMarket()
    {
        $seller = auth('manager-api')->user();

        if ($this->marketService->deleteImage($seller->market))
            return response()->json(['message' => 'image deleted successfuly']);
        return response()->json(['message' => 'image deleted failed'], 400);
    }

    /**
     * @OA\Delete(
     *     path="/sellers/deleteImage/{product}",
     *     summary="delete product image",
     *     tags={"Sellers"},
     *       @OA\Parameter(
     *            name="product",
     *            in="path",
     *            required=true,
     *            description="product id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *     @OA\Response(
     *      response=200, description="delete the product image",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function deleteImageForProduct(Product $product)
    {
        $seller = auth('manager-api')->user();

        if ($product->market != $seller->market)
            return response()->json(['message' => 'Forbidden'], 403);

        if ($this->productService->deleteImage($product))
            return response()->json(['message' => 'image deleted successfuly']);
        return response()->json(['message' => 'image deleted failed'], 400);
    }

    /**
     * @OA\Get(
     *       path="/sellers/getProducts",
     *       summary="get all products for market",
     *       tags={"Sellers"},
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
    public function getProductsForSeller(Request $request)
    {
        return response()->json([
            'message' => 'successfully get all products for market',
            'products' => $this->marketService->getProductsForMarketAdmin(auth('manager-api')->user()->market)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/sellers/getTopProducts",
     *       summary="get products order by number of purchases for market",
     *       tags={"Sellers"},
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
    public function getTopProductsForSeller(Request $request)
    {
        return response()->json([
            'message' => 'successfully get products order by number of purchases for market',
            'products' => $this->marketService->getTopProductsAdmin(auth('manager-api')->user()->market, 'en')
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/sellers/getImage",
     *     summary="image for market",
     *     tags={"Sellers"},
     *     @OA\Response(
     *      response=200, description="return the image of market",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function getImageForMarket()
    {
        $seller = auth('manager-api')->user();

        return response()->json(['image_path' => $seller->market->image]);
    }

    /**
     * @OA\Get(
     *     path="/sellers/getImage/{product}",
     *     summary="image for product",
     *     tags={"Sellers"},
     *       @OA\Parameter(
     *            name="product",
     *            in="path",
     *            required=true,
     *            description="product id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *     @OA\Response(
     *      response=200, description="return the image of product",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function getImageForProduct(Product $product)
    {
        $seller = auth('manager-api')->user();

        if ($product->market != $seller->market)
            return response()->json(['message' => 'Forbidden'], 403);

        return response()->json(['image_path' => $product->image]);
    }

    /**
     * @OA\Get(
     *       path="/sellers/getOrders",
     *       summary="get orders for market",
     *       tags={"Sellers"},
     *        @OA\Response(
     *          response=201, description="Successful get orders for market",
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
            'orders' => $this->orderService->getOrdersForMarket(auth('manager-api')->user()->market)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/sellers/getOrdersByStatus/{status}",
     *       summary="get orders by status",
     *       tags={"Sellers"},
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
            'orders' => $this->orderService->getOrdersByStatusSeller($status, auth('manager-api')->user()->market)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/sellers/getOrder/{order}",
     *       summary="get order",
     *       tags={"Sellers"},
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
        $seller = auth('manager-api')->user();
        if ($seller->market != $order->market)
            return response()->json(['message' => 'Forbidden'], 403);

        return response()->json([
            'message' => 'order get successfully',
            'price' => $order->total_cost,
            'products' => $this->orderService->getOrder($order, 'en')
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/sellers/statistics",
     *       summary="get statistics",
     *       tags={"Sellers"},
     *        @OA\Response(
     *          response=201, description="Successful get statistics",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="statistics get seccessfully"
     *               ),
     *               @OA\Property(
     *                   property="number_of_products",
     *                   type="integer",
     *                   example="2"
     *               ),
     *               @OA\Property(
     *                   property="number_of_orders",
     *                   type="integer",
     *                   example="2"
     *               ),
     *               @OA\Property(
     *                   property="salary",
     *                   type="integer",
     *                   example="20000"
     *               ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getStatistics(Request $request)
    {
        $market = auth('manager-api')->user()->market;

        return response()->json([
            'message' => 'statistics get successfully',
            'number_of_products' => count($this->marketService->getProductsForMarketAdmin($market)),
            'number_of_orders' => count($this->orderService->getOrdersForMarket($market)),
            'salary' => $this->orderService->getOrdersForMarket($market)->sum(function ($order) {
                return $order['total_cost'];
            }),
        ], 200);
    }
}