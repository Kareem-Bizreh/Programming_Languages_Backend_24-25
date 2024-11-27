<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Services\MessageService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Info(title="My API Docs", version="0.1")
 */

class UserController extends Controller
{
    protected $messageService, $userService;

    public function __construct(MessageService $messageService, UserService $userService)
    {
        $this->messageService = $messageService;
        $this->userService = $userService;
    }

    /**
     * @OA\Post(
     *     path="/users/register",
     *     summary="register users",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name" , "number"},
     *             @OA\Property(
     *                 property="first_name",
     *                 type="string",
     *                 example="Harry"
     *             ),
     *             @OA\Property(
     *                 property="last_name",
     *                 type="string",
     *                 example="potter"
     *             ),
     *             @OA\Property(
     *                 property="number",
     *                 type="string",
     *                 example="09xxxxxxxx"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successful register and verification code send to user with id",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Verification code sent"
     *             ),
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example=1
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function register(StoreUserRequest $request)
    {
        $validatedData = $request->validated();
        $user = $this->userService->findByNumber($validatedData['number']);
        if ($user) {
            if ($user->number_verified_at) {
                return response([
                    'message' => 'this number already exist'
                ], 400);
            } elseif (Cache::get('user_id_' . $user->id)) {
                return response([
                    'message' => 'verify code already sent to this number'
                ], 400);
            }
        }
        $verificationCode = Str::random(6);
        DB::beginTransaction();
        try {
            if (! $user)
                $user = $this->userService->createUser($validatedData);
            Cache::put('user_id_' . $user->id, $verificationCode, now()->addMinutes(5));
            $recipient = $validatedData['number'];
            $body = "
Hi {$validatedData['first_name']},

We received a request to verify your account. Your verification code is: {$verificationCode}

Please enter this code in the app.

If you did not request this change, then feel free to ignore this message.
";
            $this->messageService->sendMessage($body, $recipient);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response()->json([
                'message' => 'register failed, check if the phone number is correct and try again'
            ], 400);
        }

        return response()->json([
            'message' => 'Verification code sent',
            'id' => $user->id
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/users/verifyNumber",
     *     summary="verify users",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"verify_code", "id"},
     *             @OA\Property(
     *                 property="verify_code",
     *                 type="string",
     *                 example="123456"
     *             ),
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example=1
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successful verify",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="user has been verified"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function verifyNumber(Request $request)
    {
        $data = Validator::make($request->all(), [
            'verify_code' => 'required|string',
            'id' => 'required'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        return $this->userService->verifyNumber($data['verify_code'], $data['id']);
    }

    /**
     * @OA\Post(
     *     path="/users/login",
     *     summary="login user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"number"},
     *             @OA\Property(
     *                 property="number",
     *                 type="string",
     *                 example="09xxxxxxxx"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successful login",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="user has been login successfuly"
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
    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'number' => 'required|exists:users,number|numeric|digits:10',
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $user = $this->userService->findByNumber($data['number']);

        if (! $user->number_verified_at) {
            return response()->json([
                'message' => 'user has not been verified',
            ], 400);
        }

        return $this->userService->createToken($user);
    }

    /**
     * @OA\Post(
     *     path="/users/logout",
     *     summary="logout user",
     *     tags={"Users"},
     *     @OA\Response(
     *      response=200, description="Successful logout",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="user has been logout successfuly"
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
            Auth::logout();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'user logout failed'
            ], 400);
        }
        return response()->json([
            'message' => 'user has been logout successfuly'
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/users/editUser",
     *     summary="edit users",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name" , "last_name"},
     *             @OA\Property(
     *                 property="first_name",
     *                 type="string",
     *                 example="Ron"
     *             ),
     *             @OA\Property(
     *                 property="last_name",
     *                 type="string",
     *                 example="Weasly"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successfully edit the user",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="updated done"
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="string",
     *                 example="[]"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function edit(Request $request)
    {
        $data = Validator::make($request->all(), [
            'first_name' => 'required|string|max:20',
            'last_name' => 'required|string|max:20'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $user = $this->userService->updateUser(Auth::id(), $data);

        if ($user)
            return response()->json([
                'message' => 'updated done',
                'user' => $user
            ]);
        return response()->json(['message' => 'updated failed'], 400);
    }

    /**
     * @OA\Put(
     *     path="/users/refreshToken",
     *     summary="refresh token",
     *     tags={"Users"},
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
        $token = Auth::refresh();
        return response()->json([
            'message' => 'new token set',
            'Bearer Token' => $token
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/users/currentUser",
     *     summary="current user information",
     *     tags={"Users"},
     *     @OA\Response(
     *      response=200, description="return the user",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function current()
    {
        return response()->json(['user' => Auth::user()]);
    }
}
