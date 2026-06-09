<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'product_id',
        'price_id',
        'warehouse_id',
        'order_id',
        'order_item_id',
        'type',
        'quantity',
        'before_stock',
        'after_stock',
        'reference',
        'description',
        'attributes',
        'operator_type',
        'operator_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'before_stock' => 'integer',
        'after_stock' => 'integer'
    ];

    // ========== روابط ==========
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    // ========== اسکوپ‌ها ==========
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->whereHas('price', function($q) use ($productId) {
            $q->where('product_id', $productId);
        });
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDateBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // ========== متدهای کمکی ==========
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in' => 'ورود به انبار',
            'out' => 'خروج از انبار',
            'reserve' => 'رزرو موقت',
            'unreserve' => 'لغو رزرو',
            'adjustment' => 'تعدیل دستی',
            default => 'نامشخص'
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            'in' => 'bg-success',
            'out' => 'bg-danger',
            'reserve' => 'bg-warning',
            'unreserve' => 'bg-info',
            'adjustment' => 'bg-secondary',
            default => 'bg-dark'
        };
    }

    public function getOperatorNameAttribute(): string
    {
        if ($this->operator_type == 'admin' && $this->operator_id) {
            $admin = Admin::find($this->operator_id);
            return $admin ? $admin->full_name : 'ادمین';
        }

        if ($this->operator_type == 'seller' && $this->operator_id) {
            $seller = Seller::find($this->operator_id);
            return $seller ? $seller->business_name : 'فروشنده';
        }

        return 'سیستم';
    }
}
