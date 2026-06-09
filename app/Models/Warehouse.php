<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'address', 'phone', 'manager_name',
        'seller_id', 'is_active', 'type', 'province_id', 'city_id', 'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($warehouse) {
            if (empty($warehouse->code)) {
                $lastId = self::withTrashed()->max('id') ?? 0;
                $warehouse->code = 'WH-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // روابط
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }
    public function products()
    {
        return $this->hasManyThrough(Product::class, Price::class, 'warehouse_id', 'id', 'id', 'product_id')
            ->distinct();
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // اسکوپ‌ها
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSeller($query, $sellerId = null)
    {
        if ($sellerId) {
            return $query->where('seller_id', $sellerId);
        }
        return $query->whereNull('seller_id');
    }

    // متدهای کمکی
    public function getFullAddressAttribute()
    {
        $parts = [];
        if ($this->province_id) $parts[] = $this->province_id;
        if ($this->city_id) $parts[] = $this->city_id;
        if ($this->address) $parts[] = $this->address;
        return implode(' - ', $parts);
    }
}
