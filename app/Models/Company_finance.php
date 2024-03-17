<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_finance extends Model
{
    use HasFactory;

    protected $table = 'company_finance';

    protected $fillable = ['userId', 'leftOverCashBack'];
}
