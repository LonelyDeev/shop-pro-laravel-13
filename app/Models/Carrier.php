<?php

namespace App\Models;

use App\Traits\Languageable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrier extends Model
{
    use SoftDeletes, Languageable;

    protected $guarded = ['id'];

    public function cities()
    {
        return $this->belongsToMany(City::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function tariffs()
    {
        return $this->hasMany(Tariff::class);
    }

    // رابطه با فروشنده
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    // اسکوپ برای فیلتر بر اساس فروشنده
    public function scopeForCurrentSeller($query, $sellerId = null)
    {
        return $query->where('seller_id', seller()->id);
    }
    /**
     * فقط روش‌های ارسال عمومی (بدون فروشنده)
     */
    public function scopeCurrentGeneral($query)
    {
        return $query->whereNull('seller_id');
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where(function ($q) {
            $q->where('carrige_forward', true)->orWhereHas('tariffs');
        });
    }

    public function getCityTarif($city_id, $weight)
    {
        $is_within_province = $this->province->cities()->find($city_id);

        if ($is_within_province) {
            $tariff = $this->tariffs()
                ->where('type', 'within_province')
                ->where('max_weight', '>=', $weight)
                ->orderBy('max_weight')
                ->first();

            if (!$tariff && $this->extra_cost) {
                $tariff = $this->tariffs()
                    ->where('type', 'within_province')
                    ->orderBy('max_weight')
                    ->first();
            }
        } else {
            $tariff = $this->tariffs()
                ->where('type', 'extra_province')
                ->where('max_weight', '>=', $weight)
                ->orderBy('max_weight')
                ->first();

            if (!$tariff && $this->extra_cost) {
                $tariff = $this->tariffs()
                    ->where('type', 'extra_province')
                    ->orderBy('max_weight')
                    ->first();
            }
        }

        return $tariff;
    }

    // دریافت بازه‌های قابل انتخاب
    public function getAvailableRangesAttribute()
    {
        if ($this->delivery_time_type === 'user_select' && $this->user_select_ranges) {
            return $this->user_select_ranges;
        }
        return [];
    }

    // دریافت متن بازه زمانی برای نمایش در سایت
    public function getDeliveryTimeTextAttribute()
    {
        if ($this->delivery_time_type === 'default') {
            return $this->default_delivery_range ?? 'ارسال در 3 الی 6 روز کاری';
        }

        $ranges = $this->available_ranges;
        if (count($ranges) === 1) {
            return "ارسال در {$ranges[0]} روز کاری";
        }

        $min = min($ranges);
        $max = max($ranges);
        return "ارسال در {$min} الی {$max} روز کاری (انتخاب توسط کاربر)";
    }

    // محاسبه تاریخ‌های قابل انتخاب برای کاربر
    public function getAvailableDeliveryDates($startDate = null)
    {
        $startDate = $startDate ?? now();

        // افزودن روزهای شروع
        $startDate = $startDate->addDays($this->start_days_after_order);

        $dates = [];

        if ($this->delivery_time_type === 'user_select' && $this->user_select_ranges) {
            foreach ($this->user_select_ranges as $days) {
                $date = clone $startDate;
                $date->addDays($days);
                $dates[$days] = $date;
            }
        } else {
            // استخراج اعداد از متن پیشفرض (مثلا "3 الی 6")
            preg_match('/(\d+)\s*الی\s*(\d+)/', $this->default_delivery_range ?? '', $matches);
            if (count($matches) >= 3) {
                $min = (int)$matches[1];
                $max = (int)$matches[2];
                for ($i = $min; $i <= $max; $i++) {
                    $date = clone $startDate;
                    $date->addDays($i);
                    $dates[$i] = $date;
                }
            }
        }

        return $dates;
    }


}
