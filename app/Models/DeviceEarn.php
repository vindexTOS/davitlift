<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceEarn extends Model
{
    use HasFactory;

    // Define the table name if it's not the pluralized version of the model name
    protected $table = 'device_earn';

    // Define the fillable attributes
    protected $fillable = [
        'device_id',
        'earnings',
        'month',
        'year',
        'cashback',
        'deviceTariff',
    ];
}
