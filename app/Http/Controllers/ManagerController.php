<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\LoginManagerRequest;
use App\Services\ManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    protected $managerService;

    public function __construct(ManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * @OA\Post(
     *     path="/managers/login",
     *     summary="login manager",
     *     tags={"Managers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name" , "password"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Admin"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="123456789"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successful login",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="manager has been login successfuly"
     *             ),
     *             @OA\Property(
     *                 property="Bearer Token",
     *                 type="string",
     *                 example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function login(LoginManagerRequest $request)
    {
        $data = $request->validated();

        $manager = $this->managerService->findByName($data['name']);

        if (! Hash::check($data['password'], $manager->password)) {
            return response()->json([
                'message' => 'the password is not correct',
            ], 400);
        }

        return $this->managerService->createToken($data);
    }

    /**
     * @OA\Post(
     *     path="/managers/logout",
     *     summary="logout manager",
     *     tags={"Managers"},
     *     @OA\Response(
     *      response=200, description="Successful logout",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="manager has been logout successfuly"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function logout()
    {
        try {
            auth('manager-api')->logout();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'manager logout failed'
            ], 400);
        }
        return response()->json([
            'message' => 'manager has been logout successfuly'
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/managers/refreshToken",
     *     summary="refresh token",
     *     tags={"Managers"},
     *     @OA\Response(
     *      response=200, description="return the new token",
     *      @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="new token set"
     *             ),
     *             @OA\Property(
     *                 property="Bearer Token",
     *                 type="string",
     *                 example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function refreshToken()
    {
        $token = $this->managerService->refreshToken(auth('manager-api'));
        if ($token)
            return response()->json([
                'message' => 'new token set',
                'Bearer Token' => $token
            ], 200);
        else
            return response()->json([
                'message' => 'failed'
            ], 400);
    }

    /**
     * @OA\Put(
     *     path="/managers/resetPassword",
     *     summary="reset password",
     *     tags={"Managers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password", "new_password" , "new_password_confirmation"},
     *             @OA\Property(
     *                 property="old_password",
     *                 type="string",
     *                 example="password"
     *             ),
     *             @OA\Property(
     *                 property="new_password",
     *                 type="string",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="new_password_confirmation",
     *                 type="string",
     *                 example="password123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successfully set new password",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="new password set"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function resetPassword(Request $request)
    {
        $data = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $manager = auth('manager-api')->user();


        if (! Hash::check($data['old_password'], $manager->password)) {
            return response()->json([
                'message' => 'the password is not correct',
            ], 400);
        }

        if ($this->managerService->changePassword($manager->id, $data['new_password']))
            return response()->json([
                'message' => 'new password set'
            ], 200);
        return response()->json([
            'message' => 'failed'
        ], 400);
    }

    /**
     * @OA\Get(
     *     path="/managers/currentManager",
     *     summary="current manager information",
     *     tags={"Managers"},
     *     @OA\Response(
     *      response=200, description="return the manager",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function current()
    {
        $manager = auth('manager-api')->user();
        if ($manager->role == Role::Seller->value) {
            $market = $manager->market;
            $manager->market_name_en = $market->name_en;
            $manager->market_name_ar = $market->name_ar;
            unset($manager->market);
        }
        return response()->json(['manager' => $manager]);
    }

    /**
     * @OA\Get(
     *     path="/managers/checkToken",
     *     summary="current manager authenticated",
     *     tags={"Managers"},
     *     @OA\Response(
     *      response=200, description="check if manager authenticated",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function checkToken()
    {
        return response()->json(['message' => 'authenticated'], 200);
    }
}
