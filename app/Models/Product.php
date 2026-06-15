<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];


    public function provider(){
        return $this->belongsTo(Provider::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'orders_products')
                    ->withPivot('price', 'quantity')
                    ->withTimestamps();
    }


}
