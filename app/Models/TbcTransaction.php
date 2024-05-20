<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tbctransaction extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'amount', 'order_id', 'FileId', 'type'];
}
