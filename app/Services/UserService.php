<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    /**
     * find user by id
     *
     * @param int $id
     * @return User
     * @throws ModelNotFoundException
     */
    public function findById(int $id): User
    {
        return User::find($id);
    }

    /**
     * find user by number
     *
     * @param string $number
     * @throws ModelNotFoundException
     */
    public function findByNumber(string $number)
    {
        return User::where('number', $number)->first();
    }

    /**
     * Create a new user.
     *
     * @param $data.
     * @return User
     * @throws ValidationException
     */
    public function createUser($data): User
    {
        $user = User::create($data);
        return $user;
    }

    /**
     * Update user information.
     *
     * @param int $id
     * @param $data
     * @return User
     * @throws ModelNotFoundException
     */
    public function updateUser(int $id, $data): User
    {
        $user = User::find($id);
        $user->update($data);
        $user->save();
        return $user;
    }

    /**
     * Verify the user's email address.
     *
     * @param string $verificationCode
     * @param int $id
     */
    public function verifyNumber(string $verificationCode, int $id)
    {
        $code = Cache::get('user_id_' . $id);
        $user = $this->findById($id);
        if (! $code) {
            return response()->json(['message' => 'Verification code dont exist'], 400);
        }
        if ($code != $verificationCode) {
            return response()->json(['message' => 'Verification code is not correct'], 400);
        }
        $user->number_verified_at = now();
        $user->save();
        return response()->json(['message' => 'user has been verified']);
    }

    /**
     * Change the user's password.
     *
     * @param int $id
     * @param string $newPassword
     * @throws ModelNotFoundException
     */
    public function changeUserPassword(int $id, string $newPassword)
    {
        $user = $this->findById($id);
        $user->password = bcrypt($newPassword);
        $user->save();
    }

    /**
     * Create token.
     *
     * @param User $data
     */
    public function createToken(User $data)
    {
        $token = JWTAuth::fromUser($data);
        if (! $token) {
            return response()->json([
                'message' => 'user login failed'
            ], 400);
        }
        return response()->json([
            'message' => 'user has been login successfuly',
            'Bearer Token' => $token
        ], 200);
    }

    /**
     * Verify the user's to create new password .
     *
     * @param string $verificationCode
     * @param int $id
     */
    public function verifyPassword(string $verificationCode, int $id)
    {
        $code = Cache::get('user_id_' . $id);
        if (! $code) {
            return response()->json(['message' => 'Verification code dont exist'], 400);
        }
        if ($code != $verificationCode) {
            return response()->json(['message' => 'Verification code is not correct'], 400);
        }
        return response()->json(['message' => 'user has been verified']);
    }
}
