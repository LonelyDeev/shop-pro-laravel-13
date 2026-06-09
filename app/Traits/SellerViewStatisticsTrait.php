<?php

namespace App\Traits;

use App\Models\Viewer;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

trait SellerViewStatisticsTrait
{
    use StatisticsTrait;

    /**
     * ساخت کوئری پایه برای فروشنده
     */
    private function getSellerBaseQuery($sellerSlug)
    {
        return Viewer::where(function($query) use ($sellerSlug) {
            $query->where('path', 'like', '/store/' . $sellerSlug . '%')
                ->orWhere('product_path', 'like', $sellerSlug . '%')
                ->orWhere('page_path', 'like', '%/store/%')
                ->orWhere('page_path', 'like', '%/shop%');
        });
    }

    /**
     * دریافت آمار بازدید فروشگاه فروشنده
     */
    public static function getSellerViewStatisticsData($type, $period, $jalali_date, $start_date, $end_date, $sellerSlug)
    {
        $data = [];

        switch ($period) {
            case "weekly":
            case "daily": {
                $data['chart_category'] = $jalali_date->format('%Y-%m- %d');
                break;
            }
            case "monthly": {
                $data['chart_category'] = $jalali_date->format('%B - %Y');
                break;
            }
            case "yearly": {
                $data['chart_category'] = $jalali_date->format('%Y');
                break;
            }
        }

        // کوئری پایه برای فروشگاه فروشنده
        $baseQuery = Viewer::where(function($query) use ($sellerSlug) {
            $query->where('path', 'like', '/store/' . $sellerSlug . '%')
                ->orWhere('product_path', 'like', $sellerSlug . '%')
                ->orWhere('page_path', 'like', '%/store/%')
                ->orWhere('page_path', 'like', '%/shop%');
        })
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date);

        switch ($type) {
            case "viewCounts": {
                $data['total'] = $baseQuery->count();
                break;
            }
            case "viewerCounts": {
                $data['total'] = $baseQuery->distinct('ip')->count('ip');
                break;
            }
        }

        return $data;
    }

    /**
     * دریافت آمار بازدیدهای فروشنده (AJAX)
     */
    protected function sellerViewCounts(Request $request)
    {
        $sellerSlug = seller()->slug;

        if (!$sellerSlug) {
            return response()->json([
                'status' => 'error',
                'message' => 'فروشنده یافت نشد'
            ], 404);
        }

        $data = $this->getSellerPeriodData('viewCounts', $request, $sellerSlug, [$this, "getSellerViewStatisticsData"]);

        $total_count = 0;
        $total = 0;

        foreach ($data as $item) {
            $total += $item['total'];
            $total_count += 1;
        }

        $avg = $total_count > 0 ? $total / $total_count : 0;

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => formatPriceUnits($total),
                'avg' => formatPriceUnits($avg),
            ],
            'status' => 'success',
        ]);
    }

    /**
     * دریافت آمار بازدیدکنندگان یکتا فروشنده (AJAX)
     */
    protected function sellerViewerCounts(Request $request)
    {
        $sellerSlug = seller()->slug;

        if (!$sellerSlug) {
            return response()->json([
                'status' => 'error',
                'message' => 'فروشنده یافت نشد'
            ], 404);
        }

        $data = $this->getSellerPeriodData('viewerCounts', $request, $sellerSlug, [$this, "getSellerViewStatisticsData"]);

        $total_count = 0;
        $total = 0;

        foreach ($data as $item) {
            $total += $item['total'];
            $total_count += 1;
        }

        $avg = $total_count > 0 ? $total / $total_count : 0;

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => formatPriceUnits($total),
                'avg' => formatPriceUnits($avg),
            ],
            'status' => 'success',
        ]);
    }

    /**
     * دریافت داده‌های دوره‌ای برای فروشنده
     */
    protected function getSellerPeriodData(string $type, Request $request, string $sellerSlug, callable $getStatisticsData)
    {
        $dates = $this->validateDates($request);
        $from_date = $dates['from_date'];
        $to_date = $dates['to_date'];

        $data = [];
        $count = 1;

        switch ($request->period) {
            case "daily": {
                while ($to_date->gte($from_date)) {
                    $start = $from_date;
                    $jalali_date = Jalalian::fromCarbon($from_date);
                    $end = $jalali_date->toCarbon();

                    $data[$count] = $getStatisticsData($type, $request->period, $jalali_date, $start, $end, $sellerSlug);

                    $from_date = $end->addDays(1);
                    $count++;
                }
                break;
            }
            case "weekly": {
                while ($to_date->gte($from_date)) {
                    $start = $from_date;
                    $jalali_date = Jalalian::fromCarbon($from_date);
                    $end = $jalali_date->addDays(6)->toCarbon();
                    $end = $to_date->gt($end) ? $end : $to_date->copy();

                    $data[$count] = $getStatisticsData($type, $request->period, $jalali_date, $start, $end, $sellerSlug);

                    $from_date = $end->addDays(1);
                    $count++;
                }
                break;
            }
            case "yearly": {
                while ($to_date->gte($from_date)) {
                    $start = $from_date;
                    $jalali_date = Jalalian::fromCarbon($from_date);
                    $year = $jalali_date->getYear();
                    $month = $jalali_date->getMonth();
                    $last_day = $jalali_date->isLeapYear() ? 30 : 29;
                    $end = (new Jalalian($year, 12, $last_day))->toCarbon();
                    $end = $to_date->gt($end) ? $end : $to_date->copy();

                    $data[$count] = $getStatisticsData($type, $request->period, $jalali_date, $start, $end, $sellerSlug);

                    $from_date = $end->addDays(1);
                    $count++;
                }
                break;
            }
            default: {
                while ($to_date->gte($from_date)) {
                    $start = $from_date;
                    $jalali_date = Jalalian::fromCarbon($from_date);
                    $year = $jalali_date->getYear();
                    $month = $jalali_date->getMonth();
                    $last_day = $jalali_date->getMonthDays();
                    $end = (new Jalalian($year, $month, $last_day))->toCarbon();
                    $end = $to_date->gt($end) ? $end : $to_date->copy();

                    $data[$count] = $getStatisticsData($type, $request->period, $jalali_date, $start, $end, $sellerSlug);

                    $from_date = $end->addDays(1);
                    $count++;
                }
            }
        }

        return $data;
    }
}
