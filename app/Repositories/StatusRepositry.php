<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class StatusRepositry
{
    /**
     * get all status
     * @param string $lang
     */
    public function getAllStatuses(string $lang)
    {
        return DB::table('statuses')->select(['id', "name_{$lang} as name"])->get();
    }

    /**
     * get status by id
     * @param int $id
     * @param string $lang
     */
    public function getStatusById(int $id, string $lang)
    {
        return DB::table('statuses')->select(['id', "name_{$lang} as name"])->find($id);
    }
}
