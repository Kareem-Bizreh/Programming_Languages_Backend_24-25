<?php

namespace App\Services;

use App\Models\Manager;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ManagerService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * find manager by id
     *
     * @param int $id
     * @return Manager
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Manager
    {
        return Manager::findOrFail($id);
    }

    /**
     * find manager by name
     *
     * @param string $name
     * @throws ModelNotFoundException
     */
    public function findByName(string $name)
    {
        return Manager::where('name', $name)->first();
    }

    /**
     * Create a new manager.
     *
     * @param $data.
     * @return User|null
     */
    public function createManager($data)
    {
        $manager = Manager::create($data);
        return $manager;
    }

    /**
     * Create token.
     *
     * @param array $data
     */
    public function createToken(array $data)
    {
        $token = auth('manager-api')->attempt($data);
        if (! $token) {
            return response()->json([
                'message' => 'manager login failed'
            ], 400);
        }
        return response()->json([
            'message' => 'manager has been login successfuly',
            'Bearer Token' => $token
        ], 200);
    }

    /**
     * Change the manager's password.
     *
     * @param int $id
     * @param string $newPassword
     * @throws ModelNotFoundException
     * @return bool
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        DB::beginTransaction();
        try {
            $manager = $this->findById($id);
            $manager->password = bcrypt($newPassword);
            $manager->save();
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