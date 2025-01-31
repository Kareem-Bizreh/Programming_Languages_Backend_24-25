<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        return User::findOrFail($id);
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
     * @return User|null
     */
    public function createUser($data)
    {
        $user = User::create($data);
        DB::table('carts')->insert(['user_id' => $user->id]);
        return $user;
    }

    /**
     * Update user information.
     *
     * @param User $user
     * @param $data
     * @return User|null
     * @throws ModelNotFoundException
     */
    public function updateUser(User $user, $data)
    {
        DB::beginTransaction();
        try {
            $user->update($data);
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
        return $user;
    }

    /**
     * Verify the user's number.
     *
     * @param string $verificationCode
     * @param int $id
     */
    public function verifyNumber(string $verificationCode, int $id)
    {
        $code = Cache::get('user_id_' . $id);
        if (! $code) {
            return response()->json(['message' => __('messages.code_not_exist')], 400);
        }
        if ($code != $verificationCode) {
            return response()->json(['message' => __('messages.code_not_correct')], 400);
        }
        DB::beginTransaction();
        try {
            $user = $this->findById($id);
            $user->number_verified_at = now();
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => __('messages.failed')], 400);
        }
        return response()->json(['message' => __('messages.user_verified')]);
    }

    /**
     * Change the user's password.
     *
     * @param int $id
     * @param string $newPassword
     * @throws ModelNotFoundException
     * @return bool
     */
    public function changeUserPassword(int $id, string $newPassword): bool
    {
        DB::beginTransaction();
        try {
            $user = $this->findById($id);
            $user->password = bcrypt($newPassword);
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * Create token.
     *
     * @param array $data
     */
    public function createToken(array $data)
    {
        $token = auth('user-api')->attempt($data);
        if (! $token) {
            return response()->json([
                'message' => __('messages.user_login_failed')
            ], 400);
        }
        return response()->json([
            'message' => __('messages.user_login_success'),
            'Bearer Token' => $token
        ], 200);
    }

    /**
     * Verify the user's to create new password .
     *
     * @param string $verificationCode
     * @param int $id
     */
    public function verifyNewPassword(string $verificationCode, int $id)
    {
        $code = Cache::get('user_id_' . $id);
        if (! $code) {
            return response()->json(['message' => __('messages.code_not_exist')], 400);
        }
        if ($code != $verificationCode) {
            return response()->json(['message' => __('messages.code_not_correct')], 400);
        }
        return response()->json(['message' => __('messages.new_password_set')]);
    }

    /**
     * upload Image for user
     *
     * @param User $user
     * @param $image
     * @return bool
     */
    public function uploadImage(User $user, $image): bool
    {
        DB::beginTransaction();
        try {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $user->image = $image->store('images/users', 'public');
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * delete image for user
     *
     * @param User $user
     * @return bool
     */
    public function deleteImage(User $user): bool
    {
        DB::beginTransaction();
        try {
            $imagePath = $user->getAttributes()['image'];
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                $user->image = null;
                $user->save();
            } else
                throw new \Exception("no image to delete");
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * refresh token
     *
     * @param $old_token
     */
    public function refreshToken($old_token)
    {
        DB::beginTransaction();
        try {
            $token = $old_token->parseToken()->refresh();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
        return $token;
    }
}