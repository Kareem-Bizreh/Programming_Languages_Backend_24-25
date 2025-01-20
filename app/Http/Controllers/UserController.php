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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\Mailer\Exception\TransportException;

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
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $validatedData = $request->validated();
        $user = $this->userService->findByNumber($validatedData['number']);
        if ($user && $user->number_verified_at) {
            return response([
                'message' => __('validation.unique', ['attribute' => __('messages.number')])
            ], 400);
        }
        $verificationCode = rand(100000, 999999);
        DB::beginTransaction();
        try {
            if (! $user)
                $user = $this->userService->createUser($validatedData);
            else
                $user = $this->userService->updateUser($user, $validatedData);
            Cache::put('user_id_' . $user->id, $verificationCode, now()->addMinutes(5));
            Mail::to($validatedData['email'])->send(new EmailVerify($user->first_name, $verificationCode));
            DB::commit();
        } catch (TransportException $e) {
            return response()->json(['message' => __('messages.email_send_failed')], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response()->json([
                'message' => __('messages.register_failed')
            ], 400);
        }

        return response()->json([
            'message' => __('messages.code_sent'),
            'id' => $user->id
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/users/verifyNumber",
     *     summary="verify users",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = Validator::make($request->all(), [
            'verify_code' => 'required|string',
            'id' => 'required'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => $data->errors()->first(),
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
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
     *             ),
     *             @OA\Property(
     *                 property="fcm_token",
     *                 type="string",
     *                 example="anything"
     *             ),
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
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = $request->validated();

        $user = $this->userService->findByNumber($data['number']);

        if (! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => __('messages.login_password'),
            ], 400);
        }

        if (! $user->number_verified_at) {
            $verificationCode = rand(100000, 999999);
            Cache::put('user_id_' . $user->id, $verificationCode, now()->addMinutes(5));
            Mail::to($user->email)->send(new EmailVerify($user->first_name, $verificationCode));
            return response()->json([
                'message' => __('messages.user_not_verified'),
                'id' => $user->id
            ], 200);
        }

        if ($request->has('fcm_token')) {
            $this->userService->updateUser($user, ['fcm_token' => $request->input('fcm_token')]);
            unset($data['fcm_token']);
        }

        return $this->userService->createToken($data);
    }

    /**
     * @OA\Post(
     *     path="/users/logout",
     *     summary="logout user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
    public function logout(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        try {
            auth('user-api')->logout();
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('messages.user_logout_failed')
            ], 400);
        }
        return response()->json([
            'message' => __('messages.user_logout_success')
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/users/forgetPassword",
     *     summary="send check code to user for forget password",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"number"},
     *             @OA\Property(
     *                 property="number",
     *                 type="string",
     *                 example="0912345678"
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
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = Validator::make($request->all(), [
            'number' => 'required|exists:users,number'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $user = $this->userService->findByNumber($data['number']);
        $verificationCode = rand(100000, 999999);
        Cache::put('user_id_' . $user->id, $verificationCode, now()->addMinutes(5));
        Mail::to($user->email)->send(new ResetPassword($user->first_name, $verificationCode));
        return response()->json([
            'message' => __('messages.code_sent'),
            'id' => $user->id
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/users/verifyNewPassword",
     *     summary="verify user to set new password",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
     *             )
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
    public function verifyNewPassword(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = Validator::make($request->all(), [
            'verify_code' => 'required|string|min:6|max:6',
            'id' => 'required',
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        return $this->userService->verifyNewPassword($data['verify_code'], $data['id']);
    }

    /**
     * @OA\Post(
     *     path="/users/uploadImage",
     *     summary="Upload user image",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
     *                 example="user has uploaded his image successfully"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function uploadImage(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

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

        if ($this->userService->uploadImage(auth('user-api')->user(), $request->file('image')))
            return response()->json([
                'message' => __('messages.success')
            ], 200);

        return response()->json([
            'message' => __('messages.failed'),
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/users/generateVerificationCode",
     *     summary="send check code to user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"number"},
     *             @OA\Property(
     *                 property="number",
     *                 type="string",
     *                 example="0912345678"
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
    public function generateVerificationCode(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = Validator::make($request->all(), [
            'number' => 'required|exists:users,number'
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $user = $this->userService->findByNumber($data['number']);
        $verificationCode = rand(100000, 999999);
        Cache::put('user_id_' . $user->id, $verificationCode, now()->addMinutes(5));
        Mail::to($user->email)->send(new EmailVerify($user->first_name, $verificationCode));
        return response()->json([
            'message' => __('messages.code_sent'),
            'id' => $user->id
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/users/setPassword",
     *     summary="set new password",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

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
                'message' => __('messages.new_password_set')
            ], 200);
        return response()->json([
            'message' => __('messages.failed')
        ], 400);
    }

    /**
     * @OA\Put(
     *     path="/users/resetPassword",
     *     summary="reset password",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

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

        $user = auth('user-api')->user();


        if (! Hash::check($data['old_password'], $user->password)) {
            return response()->json([
                'message' => 'the password is not correct',
            ], 400);
        }

        if ($this->userService->changeUserPassword($user->id, $data['new_password']))
            return response()->json([
                'message' => __('messages.new_password_set')
            ], 200);
        return response()->json([
            'message' => __('messages.failed')
        ], 400);
    }

    /**
     * @OA\Put(
     *     path="/users/editUser",
     *     summary="edit users",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name" , "last_name" , "email"},
     *             @OA\Property(
     *                 property="first_name",
     *                 type="string",
     *                 example="Ron"
     *             ),
     *             @OA\Property(
     *                 property="last_name",
     *                 type="string",
     *                 example="Weasly"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 example="test@example.com"
     *             ),
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
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        $data = Validator::make($request->all(), [
            'first_name' => 'required|string|max:20',
            'last_name' => 'required|string|max:20',
            'email' => 'required|email',
        ]);

        if ($data->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $data->errors(),
            ], 400);
        }
        $data = $data->validated();

        $user = $this->userService->updateUser(auth('user-api')->user(), $data);

        if ($user) {
            return response()->json([
                'message' => __('messages.success'),
                'user' => $user
            ]);
        }
        return response()->json(['message' => __('messages.failed')], 400);
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
        $token = $this->userService->refreshToken(auth('user-api'));
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
        $user = auth('user-api')->user();
        return response()->json(['user' => $user]);
    }

    /**
     * @OA\Get(
     *     path="/users/checkToken",
     *     summary="current user authenticated",
     *     tags={"Users"},
     *     @OA\Response(
     *      response=200, description="check if user authenticated",@OA\JsonContent()),
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

    /**
     * @OA\Get(
     *     path="/users/getImage",
     *     summary="image of current user",
     *     tags={"Users"},
     *     @OA\Response(
     *      response=200, description="return the image of user",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function getImage()
    {
        return response()->json(['image_path' => auth('user-api')->user()->image]);
    }

    /**
     * @OA\Delete(
     *     path="/users/deleteImage",
     *     summary="delete user image",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Set language parameter",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *      response=200, description="delete the user image",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function deleteImage(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        app()->setlocale($lang);

        if ($this->userService->deleteImage(auth('user-api')->user()))
            return response()->json(['message' => __('messages.image_delete_success')], 200);
        return response()->json(['message' => __('messages.image_delete_failed')], 400);
    }
}