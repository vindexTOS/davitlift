<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceError extends Model
{
    use HasFactory;

    protected $table = 'device_errors';

    protected $fillable = [
        'device_id',
        'errorCode',
        'errorText',
    ];

    // Defining the relationship to the Device model
    public function device()
    {
        return $this->belongsTo(Device::class);
    }}
