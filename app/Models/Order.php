<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
    'user_id',
    'provider_id',
    'driver_id',
    'status_id',
    'total_price',
    'payment_status',
    'order_address','rejection_reason'
];
    protected $casts = [
        'total_price' => 'decimal:2'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id', 'id');
    }
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'orders_products')
                    ->withPivot('price', 'quantity')
                    ->withTimestamps();
    }
    // public function products()
    // {
    //     return $this->belongsToMany(Product::class, 'orders_products')
    //         ->withPivot('price', 'quantity');
    // }
    public function review()
    {
        return $this->hasOne(Review::class);
    }

}
