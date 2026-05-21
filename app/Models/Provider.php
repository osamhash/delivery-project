<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','type'];

    // كل Provider ينتمي ل User واحد
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Provider لديه العديد من Products
    public function products() {
        return $this->hasMany(Product::class, 'provider_id', 'id');
    }

    // Provider لديه العديد من Orders
    public function orders() {
        return $this->hasMany(Order::class, 'provider_id', 'id');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_providers');
    }


}
