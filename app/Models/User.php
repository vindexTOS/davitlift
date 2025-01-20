<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'id_number',
        'password',
        'phone',
        'balance',
        'cashback',
        'freezed_balance',
        'saved_card_status',
        'saved_order_id',
        'hide_statistic',
        'role',
        'fixed_card_amount',
        "fixed_phone_amount",
        "fixed_individual_amount"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function Devices()
    {
        return $this->belongsToMany(Device::class, 'device_user')->withPivot(
            'subscription'
        );
    }
    public function companies()
    {
        return $this->belongsToMany('App\Models\Company');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
