<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @OA\Post(
     *       path="/locations/addLocation",
     *       summary="add new location",
     *       tags={"Locations"},
     *        @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               required={"city" , "address" , "building_number" , "floor_number" , "notes"},
     *               @OA\Property(
     *                   property="name",
     *                   type="string",
     *                   example=" potter"
     *               ),
     *               @OA\Property(
     *                    property="location",
     *                     type="string",
     *                     example="damascus Al-Midan"
     *                ),
     *               @OA\Property(
     *                    property="street",
     *                     type="string",
     *                     example="long one"
     *                ),
     *               @OA\Property(
     *                    property="notes",
     *                     type="string",
     *                     example="in front of the red building."
     *                ),
     *           )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful added",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="location added seccessfully"
     *               ),
     *               @OA\Property(
     *                    property="location",
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
    public function addLocation(StoreLocationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        return response()->json([
            'message' => 'location added seccessfully',
            'location' => $this->locationService->add($data)
        ]);
    }

    /**
     * @OA\Get(
     *       path="/locations/getLocations",
     *       summary="get all locations for user",
     *       tags={"Locations"},
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
     *          response=201, description="Successful get locations",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="locations get seccessfully"
     *               ),
     *               @OA\Property(
     *                    property="locations",
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
    public function getLocations(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $locations = $this->locationService->get(Auth::user(), $perPage, $page);
        return response()->json([
            'message' => 'locations get seccessfully',
            'locations' => $locations
        ]);
    }

    /**
     * @OA\Delete(
     *       path="/locations/deleteLocation/{location_id}",
     *       summary="delete location for user",
     *       tags={"Locations"},
     *       @OA\Parameter(
     *            name="location_id",
     *            in="path",
     *            required=true,
     *            description="id of location you want to delete",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful delete location",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="locations delete seccessfully"
     *               )
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function deleteLocation(int $location_id)
    {
        if ($this->locationService->delete($location_id))
            return response()->json([
                'message' => 'locations delete seccessfully'
            ]);
        return response()->json([
            'message' => 'locations delete failed'
        ], 400);
    }
}
