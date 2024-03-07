<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = ['company_name', 'admin_id', 'comment', 'sk_code','cashback'];
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
    public function admin() {
        return $this->belongsTo(User::class, 'admin_id');
    }

}
