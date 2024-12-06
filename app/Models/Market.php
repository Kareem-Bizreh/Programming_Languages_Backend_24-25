<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return config('app.url') . '/storage/' . $value;
        }
        return null;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}