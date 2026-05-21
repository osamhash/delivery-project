<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use Notifiable;
    use HasFactory,SoftDeletes;
    use HasApiTokens;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'first_name','second_name','last_name','email','password',
        'phone','role_id','gender','address','image_path','date_of_birth'
    ];
    //osama
    protected $hidden = ['password', 'remember_token'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function role(){
        return $this->belongsTo(Role::class,'role_id','id');
    }

     // علاقة اختيارية إذا كان User Driver
    public function driver() {
        return $this->hasOne(Driver::class, 'user_id', 'id');
    }

     // علاقة اختيارية إذا كان User Provider
    public function provider() {
        return $this->hasOne(Provider::class, 'user_id', 'id');
    }

    public function favoriteProviders()
    {
        return $this->belongsToMany(Provider::class, 'favorite_providers', 'user_id', 'provider_id')
                    ->withTimestamps();
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
