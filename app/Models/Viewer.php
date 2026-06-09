<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viewer extends Model
{
    protected $guarded = ['id'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function product()
    {
        return $this->hasOne(Product::class,'slug','product_path');
    }


    /**
     * ساخت کوئری پایه برای فروشگاه فروشنده (بر اساس path)
     */
    private static function baseSellerStoreQuery($sellerSlug = null)
    {
        $query = self::query();

        if ($sellerSlug) {
            // بررسی می‌کنیم که path با /store/ اسلاگ فروشنده شروع شود
            $query->where('path', 'like', '/store/' . $sellerSlug . '%');
        }

        return $query;
    }

    /**
     * تعداد بازدید فروشگاه فروشنده
     */
    public static function getSellerStoreVisitsCount($sellerSlug = null)
    {
        return self::baseSellerStoreQuery($sellerSlug)->count();
    }

    /**
     * تعداد بازدید امروز فروشگاه فروشنده
     */
    public static function getTodaySellerStoreVisits($sellerSlug = null)
    {
        return self::baseSellerStoreQuery($sellerSlug)
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * تعداد بازدید دیروز فروشگاه فروشنده
     */
    public static function getYesterdaySellerStoreVisits($sellerSlug = null)
    {
        return self::baseSellerStoreQuery($sellerSlug)
            ->whereDate('created_at', today()->subDay())
            ->count();
    }

    /**
     * آمار بازدیدهای روزانه فروشگاه
     */
    public static function getDailySellerStoreVisits($sellerSlug = null, $days = 7)
    {
        $query = self::baseSellerStoreQuery($sellerSlug)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'asc');

        return $query->get();
    }

    /**
     * تعداد بازدیدکنندگان یکتا فروشگاه
     */
    public static function getUniqueSellerStoreVisitors($sellerSlug = null)
    {
        return self::baseSellerStoreQuery($sellerSlug)
            ->distinct('ip')
            ->count('ip');
    }

    /**
     * میانگین بازدید روزانه (30 روز اخیر)
     */
    public static function getAverageDailyVisits($sellerSlug = null)
    {
        $total = self::baseSellerStoreQuery($sellerSlug)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return $total > 0 ? round($total / 30) : 0;
    }

    /**
     * درصد تغییرات نسبت به دیروز
     */
    public static function getChangePercent($sellerSlug = null)
    {
        $today = self::getTodaySellerStoreVisits($sellerSlug);
        $yesterday = self::getYesterdaySellerStoreVisits($sellerSlug);

        if ($yesterday > 0) {
            return round(($today - $yesterday) / $yesterday * 100, 1);
        }

        return $today > 0 ? 100 : 0;
    }

    /**
     * آخرین بازدیدکنندگان فروشگاه
     */
    public static function getLatestStoreVisitors($sellerSlug = null, $limit = 10)
    {
        return self::baseSellerStoreQuery($sellerSlug)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['ip', 'path', 'created_at']);
    }
}
