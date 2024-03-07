<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdatingDevice extends Model
{
    protected $table = 'updating_device';
    use HasFactory;
    protected $fillable = [
        'dev_id',
        'device_id',
        'previous_version',
        'new_version',
        'status',
        'is_checked',
    ];
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
