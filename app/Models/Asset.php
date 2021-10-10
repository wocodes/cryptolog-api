<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['name', 'symbol', 'asset_type_id'];

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class);
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class);
    }
}
