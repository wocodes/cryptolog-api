<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLocation extends Model
{
    protected $fillable = ['name', 'state', 'country', 'interest_rate'];

    public function assets()
    {
        $this->hasMany(Asset::class);
    }
}
