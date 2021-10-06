<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserApiKey extends Model
{
    protected $fillable = [
        'platform_id', 'key', 'secret'
    ];
}
