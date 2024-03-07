<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sim_card_number',
        'relay_pulse_time',
        'lcd_brightness',
        'led_brightness',
        'msg_appear_time',
        'card_read_delay',
        'tariff_amount',
        'limit',
        'fixed_amount',
        'timezone',
        'storage_disable',
        'relay1_node',
        'dev_name',
        'op_mode',
        'dev_id',
        'lastBeat',
        'users_id',
        'guest_msg_L1',
        'guest_msg_L2',
        'guest_msg_L3',
        'validity_msg_L1',
        'validity_msg_L2',
        'name',
        'comment',
        'company_id',
        'soft_version',
        'hardware_version',
        'url',
        'limit',
        'crc32',
        'network',
        'signal',
        'pay_day',
        'isBlocked'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the user that owns the device.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'device_user')->withPivot('subscription')->withCount('cards');
    }
    public function earnings()
    {
        return $this->hasMany(DeviceEarn::class,'device_id','id');
    }
    public function withdrawals()
    {
        return $this->hasMany(DeviceWithdrawn::class);
    }
    public function errors() {
        return $this->hasMany(DeviceError::class,'device_id','id');

    }

    /**
     * Get the company that owns the device.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public static function search($term)
    {
        return self::query()
            ->where('can_search', true)
            ->where(function($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('comment', 'LIKE', "%{$term}%");
            });
    }
    public function lastUpdate() {
        return $this->hasOne(UpdatingDevice::class,'device_id','id')->where('status', '=',1)->latest();
    }
}
