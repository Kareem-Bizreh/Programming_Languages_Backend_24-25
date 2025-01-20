<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Models\Location;
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
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
     *        @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(
     *               required={"city" , "address" , "building_number" , "floor_number"},
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
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = $request->validated();
        $data['user_id'] = auth('user-api')->id();
        $location = $this->locationService->add($data);
        if ($location)
            return response()->json([
                'message' => __('messages.location_add_success'),
                'location' => $location
            ], 200);
        return response()->json([
            'message' => __('messages.location_add_failed'),
        ], 400);
    }

    /**
     * @OA\Get(
     *       path="/locations/getLocations",
     *       summary="get all locations for user",
     *       tags={"Locations"},
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
        // $perPage = $request->query('perPage', 10);
        // $page = $request->query('page', 1);
        $locations = $this->locationService->get(auth('user-api')->user());
        return response()->json([
            'message' => 'locations get seccessfully',
            'locations' => $locations
        ]);
    }

    /**
     * @OA\Get(
     *       path="/locations/getLocation/{location}",
     *       summary="get location for user",
     *       tags={"Locations"},
     *       @OA\Parameter(
     *            name="location",
     *            in="path",
     *            required=true,
     *            description="location id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get location",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="location get seccessfully"
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
    public function getLocation(Request $request, Location $location)
    {
        return response()->json([
            'message' => 'location get seccessfully',
            'location' => $location
        ]);
    }

    /**
     * @OA\Get(
     *       path="/locations/cost/{location}",
     *       summary="get cost of location",
     *       tags={"Locations"},
     *       @OA\Parameter(
     *            name="location",
     *            in="path",
     *            required=true,
     *            description="location id",
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *        @OA\Response(
     *          response=201, description="Successful get location",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                   property="message",
     *                   type="string",
     *                   example="location get cost"
     *               ),
     *               @OA\Property(
     *                   property="cost",
     *                   type="integer",
     *                   example=500
     *                ),
     *          )
     *        ),
     *        @OA\Response(response=400, description="Invalid request"),
     *        security={
     *            {"bearer": {}}
     *        }
     * )
     */
    public function getCost(Request $request, Location $location)
    {
        return response()->json([
            'message' => 'location get cost',
            'cost' => $location->cost
        ]);
    }

    /**
     * @OA\Delete(
     *       path="/locations/deleteLocation/{location_id}",
     *       summary="delete location for user",
     *       tags={"Locations"},
     *       @OA\Parameter(
     *           name="Accept-Language",
     *           in="header",
     *           description="Set language parameter",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
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
    public function deleteLocation(Request $request, int $location_id)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        if ($this->locationService->delete($location_id))
            return response()->json([
                'message' => __('messages.location_delete_success')
            ], 200);
        return response()->json([
            'message' => __('messages.location_delete_failed')
        ], 400);
    }
}
