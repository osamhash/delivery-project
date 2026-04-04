<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
     use HasFactory;

    protected $fillable = ['user_id','is_available'];

    // كل Driver ينتمي ل User واحد
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // يمكن أن يكون للـ Driver العديد من Orders
    public function orders() {
        return $this->hasMany(Order::class, 'driver_id', 'id');
    }

    // public function payment(){
    //     return $this->hasMany(Payment::class,'payment_id','id');
    // }

}
