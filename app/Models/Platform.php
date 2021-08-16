<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    public function assets()
    {
        return $this->belongsToMany(Asset::class);
    }
}
