<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class CategoryRepositry
{
    /**
     * get all categories
     * @param string $lang
     */
    public function getAll(string $lang)
    {
        return DB::table('categories')->select(['id',  "name_{$lang} as name"])->get();
    }

    /**
     * get category by id
     *
     * @param int $id
     * @param string $lang
     */
    public function getById(int $id, string $lang)
    {
        return DB::table('categories')->where('id', '=', $id)->select(['id',  "name_{$lang} as name"])->get()->first();
    }
}
