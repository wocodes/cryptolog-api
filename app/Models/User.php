<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    const METAS = [
        'settings.hide_balance' => 0,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'referred_by', 'referral_code', 'password_reset_token', 'verification_code'
    ];

    protected $appends = [
      'has_api_keys'
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

    public function apiKeys()
    {
        return $this->hasMany(UserApiKey::class);
    }

    public function getHasApiKeysAttribute()
    {
        return $this->apiKeys()->exists();
    }

    public function botTradeAssets()
    {
        return $this->hasMany(BotTrade::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
}
