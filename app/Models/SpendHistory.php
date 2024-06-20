<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpendHistory extends Model



{   
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'device_id',
        'amount',
        "created_at"
    ];
    
    public function users(){
        return $this->belongsTo(Users::class);
    }
    
}
