<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory,SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'first_name','second_name','last_name','email','password',
        'phone','role_id','gender','address','image_path','date_of_birth'
    ];

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

}
