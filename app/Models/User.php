<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name','second_name','last_name','email','password',
        'phone','role_id','gender','address','image_path','date_of_birth'
    ];

    public function role(){
        return $this->belongsTo(Role::class,'role_id','id');
    }


}
