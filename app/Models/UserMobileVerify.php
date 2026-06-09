<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMobileVerify extends Model
{
    use HasFactory;
    protected $table="user_mobile_verify";
    protected $guarded = ['id'];
}
