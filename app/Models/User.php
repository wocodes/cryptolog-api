<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    const METAS = [
        'settings.hide_balance' => 0,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function assetLogs()
    {
        return $this->hasMany(AssetLog::class);
    }

    public function metas() {
        return $this->morphMany(Meta::class, 'model');
    }

    public function getIsAdminAttribute($value)
    {
        return (bool) $value;
    }

    public function fiat()
    {
        return $this->belongsTo(Fiat::class);
    }
}
