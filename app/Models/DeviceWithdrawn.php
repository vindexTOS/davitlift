<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceWithdrawn extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id', 'withdrawn_amount', 'comment', 'withdrawal_date'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'device_withdrawn';

    /**
     * Get the device that owns the withdrawal.
     */
    public function device()
    {
        return $this->belongsTo('App\Device', 'device_id', 'id');
    }
}
