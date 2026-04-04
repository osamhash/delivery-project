<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements JWTSubject
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'first_name', 'second_name', 'last_name', 'email', 'password',
        'phone', 'role_id', 'gender', 'address', 'image_path', 'date_of_birth',
    ];

    protected $hidden = ['password'];

    // ---- JWT ----
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    // ---- Relations ----
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function driver()
    {
        return $this->hasOne(Driver::class, 'user_id', 'id');
    }

    public function provider()
    {
        return $this->hasOne(Provider::class, 'user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'id');
    }
}
