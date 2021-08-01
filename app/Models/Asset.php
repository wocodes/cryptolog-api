<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
}
