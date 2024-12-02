<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Market;
use App\Models\Product;
use App\Services\MarketService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    protected $productService, $marketService;

    public function __construct(ProductService $productService, MarketService $marketService)
    {
        $this->productService = $productService;
        $this->marketService = $marketService;
    }

    /**
     * @OA\Post(
     *       path="/sellers/addProduct",
     *       summary="add new product",
     *       tags={"Sellers"},
     *        @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               required={"name", "category_id" , "quantity" , "price"},
     *               @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Choko Cake"
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
     *                 property="description",
     *                 type="string",
     *                 example="for birthdays"
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
    public function uploadImage(Request $request, Product $product)
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
     *               required={"name", "category_id" , "quantity" , "price"},
     *               @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Choko Cake"
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
     *                 property="description",
     *                 type="string",
     *                 example="for birthdays"
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
        return response()->json([
            'message' => 'successfully edit product',
            'product' => $new_product
        ], 200);
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
    public function deleteImage(Product $product)
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
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get all products for market',
            'products' => $this->marketService->getProductsForMarket($perPage, $page, auth('manager-api')->user()->market)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/sellers/getTopProducts",
     *       summary="get products order by number of purchases for market",
     *       tags={"Sellers"},
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
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products order by number of purchases for market',
            'products' => $this->marketService->getTopProducts($perPage, $page, auth('manager-api')->user()->market)
        ], 200);
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
    public function getImage(Product $product)
    {
        $seller = auth('manager-api')->user();

        if ($product->market != $seller->market)
            return response()->json(['message' => 'Forbidden'], 403);

        return response()->json(['image_path' => $product->image]);
    }
}