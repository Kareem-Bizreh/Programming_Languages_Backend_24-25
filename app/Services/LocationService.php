<?php

namespace App\Services;

use App\Models\Location;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class LocationService
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * get all locations for user
     *
     * @param User $user
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function get(User $user)
    {
        return $user->locations()->get();
    }

    /**
     * add location to some user
     *
     * @param array $data contains all data needed to create location like user_id
     * @return Location|null
     */
    public function add(array $data)
    {
        $location = null;
        DB::beginTransaction();
        try {
            $location = Location::create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
        return $location;
    }

    /**
     * delete location
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $location = $this->findById($id);
            if (! $location) {
                throw new \Exception('Location not found');
            }
            $location->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /**
     * find locations by id
     *
     * @param int $id
     */
    public function findById(int $id)
    {
        return Location::find($id);
    }
}
