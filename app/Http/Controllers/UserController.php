<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Mail\EmailVerify;
use App\Mail\ResetPassword;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(title="My API Docs", version="0.1")
 */

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
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
     *             required={"first_name", "last_name" , "number" , "email" , "password" , "password_confirmation"},
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
     *                 example="0912345678"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 example="test@example.com"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 example="password123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successful register and verification code send to email to verify user with id",
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
        if ($user && $user->number_verified_at) {
            return response([
                'message' => 'this number already exist'
            ], 400);
        }
        $verificationCode = Str::random(6);
        DB::beginTransaction();
        try {
            if (! $user)
                $user = $this->userService->createUser($validatedData);
            Cache::put('user_id_' . $user->id, $verificationCode, now()->addMinutes(5));
            Mail::to($validatedData['email'])->send(new EmailVerify($user->first_name, $verificationCode));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response()->json([
                'message' => 'register failed, check if the email address is correct and try again'
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
     *             required={"number" , "password"},
     *             @OA\Property(
     *                 property="number",
     *                 type="string",
     *                 example="0912345678"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password123"
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
    public function login(LoginUserRequest $request)
    {
        $data = $request->validated();

        $user = $this->userService->findByNumber($data['number']);

        if (! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'the password is not correct',
            ], 400);
        }

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
     * @OA\Post(
     *     path="/users/forgetPassword",
     *     summary="send check code to user for forget password",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"number" , "email"},
     *             @OA\Property(
     *                 property="number",
     *                 type="string",
     *                 example="0912345678"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 example="test@example.com"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="verification code send to user with id",
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
    public function forgetPassword(Request $request)
    {
        $data = Validator::make($request->all(), [
            'number' => 'required|exists:users,number',
            'email' => 'required|email',
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $user = $this->userService->findByNumber($data['number']);
        $verificationCode = Str::random(6);
        Cache::put('user_id_' . $user->id, $verificationCode, now()->addMinutes(5));
        Mail::to($data['email'])->send(new ResetPassword($user->first_name, $verificationCode));
        return response()->json([
            'message' => 'Verification code sent',
            'id' => $user->id
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/users/verifyNewPassword",
     *     summary="verify user to set new password",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"verify_code", "id" , "password" , "password_confirmation"},
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
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 example="password123"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successful verify and set new password",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="user has been verified and new password set"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function verifyNewPassword(Request $request)
    {
        $data = Validator::make($request->all(), [
            'verify_code' => 'required|string|min:6|max:6',
            'id' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        return $this->userService->verifyNewPassword($data['verify_code'], $data['id'], $data['password']);
    }

    /**
     * @OA\Put(
     *     path="/users/setPassword",
     *     summary="set new password",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "password" , "password_confirmation"},
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 example="password123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="Successfully set of password",
     *       @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="new password set"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function setPassword(Request $request)
    {
        $data = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
            'id' => 'required'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        if ($this->userService->changeUserPassword($data['id'], $data['password']))
            return response()->json([
                'message' => 'new password set'
            ], 200);
        return response()->json([
            'message' => 'failed'
        ], 400);
    }

    /**
     * @OA\Put(
     *     path="/users/resetPassword",
     *     summary="reset password",
     *     tags={"Users"},
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
     *      response=200, description="Successfully set of password",
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

        $user = null;
        try {
            $user = $this->userService->findById(Auth::id());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'failed'
            ], 400);
        }


        if (! Hash::check($data['old_password'], $user->password)) {
            return response()->json([
                'message' => 'the password is not correct',
            ], 400);
        }

        if ($this->userService->changeUserPassword($user->id, $data['new_password']))
            return response()->json([
                'message' => 'new password set'
            ], 200);
        return response()->json([
            'message' => 'failed'
        ], 400);
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
        $token = JWTAuth::parseToken()->refresh();
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
