<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\StatusRepositry;

class StatusController extends Controller
{
    protected $statusRepositry;

    public function __construct(StatusRepositry $statusRepositry)
    {
        $this->statusRepositry = $statusRepositry;
    }

    /**
     * @OA\Get(
     *     path="/statuses/getAll",
     *     summary="get all statuses",
     *     tags={"Statuses"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(response=200, description="succesful get all statuses",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     * )
     */
    public function getAll(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        return response()->json($this->statusRepositry->getAllStatuses($lang), 200);
    }

    /**
     * @OA\Get(
     *     path="/statuses/get/{status}",
     *     summary="get status by id",
     *     tags={"Statuses"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *       @OA\Parameter(
     *            name="status",
     *            in="path",
     *            required=true,
     *            description="status id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *     @OA\Response(response=200, description="succesful get statuss",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     * )
     */
    public function getStatusById(int $status, Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        return response()->json(['status' => $this->statusRepositry->getStatusById($status, $lang)], 200);
    }
}
