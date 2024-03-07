<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnregisteredDevice extends Model
{
    use HasFactory;
    protected $fillable = ['dev_id','soft_version','hardware_version'];
}
