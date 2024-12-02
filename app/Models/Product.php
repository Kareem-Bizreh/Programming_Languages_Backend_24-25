<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $guarded = [];

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return config('app.url') . '/storage/' . $value;
        }
        return null;
    }
}
