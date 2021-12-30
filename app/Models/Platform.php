<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = ['name'];

    public function assets()
    {
        return $this->belongsToMany(Asset::class);
    }

    public function assetTypes()
    {
        return $this->belongsToMany(AssetType::class);
    }
}
