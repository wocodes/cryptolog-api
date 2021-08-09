<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalApi extends Model
{
    protected $fillable = ["organization", "host", "api_key", "meta", "active"];

    protected $casts = ["meta" => "array"];
}
