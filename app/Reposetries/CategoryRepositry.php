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
        DB::table('categories')->get();
    }

    /**
     * get category by id
     *
     * @param int $id
     */
    public function getById(int $id)
    {
        DB::table('categories')->where('id', '=', $id)->get()->first();
    }
}
