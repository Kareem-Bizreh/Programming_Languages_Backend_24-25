<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @OA\Post(
     *       path="/products/toggleFavorite/{product}",
     *       summary="change status of favorite for user of some product",
     *       tags={"Products"},
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
     *          response=201, description="Successful toggle",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully toggle"
     *               )
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function toggleFavorite(Product $product)
    {
        $user = auth('user-api')->user();

        $exists = $this->productService->isFavorite($product->id, $user->id);

        if ($this->productService->toggleFavorite($user, $product->id, $exists)) {
            if ($exists) {
                return response()->json(['message' => 'product removed from favorites.']);
            } else {
                return response()->json(['message' => 'product added to favorites.']);
            }
        }
        return response()->json(['message' => 'failed'], 400);
    }

    /**
     * @OA\Get(
     *       path="/products/getFavoriteProducts",
     *       summary="get favorite products with their id, name, market name, image, category and price for user",
     *       tags={"Products"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *          response=201, description="Successful get products",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get products"
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
    public function getFavoriteProducts(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products',
            'products' => $this->productService->getFavoriteProducts(auth('user-api')->user(), $perPage, $page, $lang)
        ], 200);
    }


    /**
     * @OA\Get(
     *       path="/products/getProducts",
     *       summary="get all products with their id, name, market name, image, category and price",
     *       tags={"Products"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *          response=201, description="Successful get products",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get products"
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
    public function getProducts(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products',
            'products' => $this->productService->getProducts($perPage, $page, $lang)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/products/getProductsByCategory/{category}",
     *       summary="get all products by category with their id, name, market name, image and price",
     *       tags={"Products"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *            name="category",
     *            in="path",
     *            required=true,
     *            description="category id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get products by category",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get products by category"
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
    public function getProductsByCategory(Request $request, int $category)
    {
        $lang = $request->header('Accept-Language', 'en');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products by category',
            'products' => $this->productService->getProductsByCategory($perPage, $page, $category, $lang)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/products/getProduct/{product}",
     *       summary="get all information for product and if this product is favorite for user",
     *       tags={"Products"},
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
     *          response=201, description="Successful get product",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get product"
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
    public function getProduct(Request $request, Product $product)
    {
        $lang = $request->header('Accept-Language', 'en');
        return response()->json([
            'message' => 'successfully get product',
            'product' => $this->productService->getProduct($product, $lang),
            'isFavorite' => $this->productService->isFavorite($product->id, auth('user-api')->id()),
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/products/getProductsByName/{product_name}",
     *       summary="get products by name",
     *       tags={"Products"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
     *            name="product_name",
     *            in="path",
     *            required=true,
     *            description="product name",
     *            @OA\Schema(
     *                type="string"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get products by name",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get products by name"
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
    public function getProductsByName(Request $request, string $product_name)
    {
        $lang = $request->header('Accept-Language', 'en');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products by name',
            'products' => $this->productService->getProductsByName($perPage, $page, $product_name, $lang)
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/products/getImage/{product}",
     *     summary="image for product",
     *     tags={"Products"},
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
        return response()->json(['image_path' => $product->image]);
    }

    /**
     * @OA\Get(
     *       path="/products/getTopProducts",
     *       summary="get products order by number of purchases",
     *       tags={"Products"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
        $lang = $request->header('Accept-Language', 'en');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products order by number of purchases',
            'products' => $this->productService->getTopProducts($perPage, $page, $lang)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/products/getTopProducts/{market}",
     *       summary="get products order by number of purchases for market",
     *       tags={"Products"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
        $lang = $request->header('Accept-Language', 'en');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get products order by number of purchases for market',
            'market_name' => $market['name_' . $lang],
            'products' => $this->productService->marketService->getTopProducts($perPage, $page, $market, $lang)
        ], 200);
    }
}
