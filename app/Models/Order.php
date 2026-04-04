<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_id',
        'driver_id',
        'status_id',
        'total_price',
        'payment_status',
        'order_address',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    // ---- Relations ----
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'orders_products', 'order_id', 'product_id')
                    ->withPivot('price', 'quantity')
                    ->withTimestamps();
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'order_id', 'id');
    }
}
