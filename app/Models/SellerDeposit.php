<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerDeposit extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
