<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    public function activeApi()
    {
        return $this->belongsTo(ExternalApi::class, "external_api_id", "id");
    }
}
