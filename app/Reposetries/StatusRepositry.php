<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class StatusRepositry
{
    /**
     * get all status
     */
    public function getAllStatuses()
    {
        return DB::table('statuses')->get();
    }

    /**
     * get status by id
     * @param int $id
     */
    public function getStatusById(int $id)
    {
        return DB::table('statuses')->find($id);
    }
}
