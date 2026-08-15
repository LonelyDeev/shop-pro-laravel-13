<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }

    public function products()
    {
        $seller_id = $this->seller_id;
        $order = $this->order;

        if (!$order) {
            return collect([]);
        }

        return $order->items()->where('seller_id', $seller_id)->get();
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function get_price()
    {
        return $this->belongsTo(Price::class, 'price_id', 'id');
    }

    public function user()
    {
        return $this->order()->first()->user;
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function realPrice()
    {
        return $this->real_price;
    }

    public function discountAmount()
    {
        return $this->quantity * ($this->real_price - $this->price);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function isRefunded(): bool
    {
        return $this->refunded === true;
    }

    public function returnRequest()
    {
        return $this->hasOne(ReturnRequest::class)->latest();
    }
    public function canBeCanceled(): bool
    {
        // اگر قبلاً لغو شده باشد
        if ($this->isRefunded()) {
            return false;
        }

        // اگر وضعیت پرداخت نشده باشد
        if (!$this->order->isPaid()) {
            return false;
        }

        // اگر قبلاً برای این سفارش refund ثبت شده باشد
        $existingRefund = WalletHistory::where([
            'order_id' => $this->order_id,
            'orderCanceled' => 1
        ])->exists();

        if ($existingRefund) {
            return false;
        }

        return true;
    }

}
