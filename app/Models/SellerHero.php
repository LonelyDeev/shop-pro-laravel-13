<?php

namespace App\Models;

use App\Traits\Languageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerHero extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
}
