<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Services\MarketService;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    protected $marketService;

    public function __construct(MarketService $marketService)
    {
        $this->marketService = $marketService;
    }

    /**
     * @OA\Get(
     *       path="/markets/getMarkets",
     *       summary="get all markets",
     *       tags={"Markets"},
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
     *          response=201, description="Successful get markets",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get markets"
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
            'message' => 'successfully get markets',
            'markets' => $this->marketService->getMarkets($perPage, $page)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/markets/getMarketsByName/{market_name}",
     *       summary="get markets by name",
     *       tags={"Markets"},
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
     *            name="market_name",
     *            in="path",
     *            required=true,
     *            description="market name",
     *            @OA\Schema(
     *                type="string"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get markets by name",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get markets by name"
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
    public function getMarketsByName(Request $request, string $market_name)
    {
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        return response()->json([
            'message' => 'successfully get markets by name',
            'markets' => $this->marketService->getMarketsByName($perPage, $page, $market_name)
        ], 200);
    }

    /**
     * @OA\Get(
     *       path="/markets/getProductsForMarket/{market}",
     *       summary="get all products for market",
     *       tags={"Markets"},
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
     *          response=201, description="Successful get products for market",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="successfully get products for market"
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
            'message' => 'successfully get products for market',
            'products' => $this->marketService->getProductsForMarket($perPage, $page, $market)
        ], 200);
    }
}
