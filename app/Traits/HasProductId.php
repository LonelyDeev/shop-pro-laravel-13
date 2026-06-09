<?php
// app/Traits/HasProductId.php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasProductId
{
    protected static function bootHasProductId()
    {
        // لاگ بنویس تا ببینی چندبار اجرا می‌شود
        \Illuminate\Support\Facades\Log::info('bootHasProductId called for: ' . static::class);

        static::creating(function ($model) {
            \Illuminate\Support\Facades\Log::info('creating event fired for: ' . $model->id ?? 'new');

            if (empty($model->product_id)) {
                $model->product_id = $model->generateProductId();
            }
        });
    }

    /**
     * بررسی اینکه آیا مدل قبلاً بوت شده است
     */
    protected static function hasBeenBooted()
    {
        return isset(static::$booted[static::class]);
    }

    /**
     * تولید product_id خودکار
     */
    public function generateProductId()
    {
        $year = now()->format('y');
        $persianYear = $this->getPersianYear();
        $prefix = 'p-' . $persianYear;

        $lastNumber = $this->getLastSequenceNumber($persianYear);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        $productId = $prefix . $newNumber;

        while (self::where('product_id', $productId)->exists()) {
            $lastNumber++;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $productId = $prefix . $newNumber;
        }

        return $productId;
    }

    /**
     * دریافت سال شمسی جاری
     */
    protected function getPersianYear()
    {
        if (class_exists(\Morilog\Jalali\Jalalian::class)) {
            return \Morilog\Jalali\Jalalian::now()->getYear();
        }

        $year = now()->year;
        $persianYear = $year - 621;

        if (now()->month < 3) {
            $persianYear = $year - 622;
        } elseif (now()->month == 3 && now()->day < 21) {
            $persianYear = $year - 622;
        } else {
            $persianYear = $year - 621;
        }

        return $persianYear;
    }

    /**
     * گرفتن آخرین شماره برای سال جاری
     */
    protected function getLastSequenceNumber($year)
    {
        $prefix = 'p-' . $year;

        $lastProduct = self::where('product_id', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastProduct && $lastProduct->product_id) {
            $lastNumber = (int) substr($lastProduct->product_id, -4);
            return $lastNumber;
        }

        return 0;
    }

    /**
     * فرمت کامل product_id
     */
    public function getFormattedProductIdAttribute()
    {
        return $this->product_id;
    }
}
