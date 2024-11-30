<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class CategoryRepositry
{
    /**
     * get all categories
     */
    public function getAll()
    {
        return DB::table('categories')->get();
    }

    /**
     * get category by id
     *
     * @param int $id
     */
    public function getById(int $id)
    {
        return DB::table('categories')->where('id', '=', $id)->get()->first();
    }
}