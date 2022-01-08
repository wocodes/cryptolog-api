<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    public const ASSET_NAMES = [
        'Digital Currency' => "Cryptocurrency",
        'Real Estate' => "Real Estate",
        'Stock' => "Stock"
    ];

    public function activeApi()
    {
        return $this->belongsTo(ExternalApi::class, "external_api_id", "id");
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
