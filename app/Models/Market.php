<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    protected $guarded = [];

    public function products()
    {
        $this->hasMany(Product::class);
    }
}
