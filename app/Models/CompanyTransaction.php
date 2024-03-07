<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_transactions';

    protected $fillable = [
        'company_id',
        'amount',
        'manager_id',
        'type',
        'transaction_date',
    ];

    protected $dates = ['deleted_at'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }}
