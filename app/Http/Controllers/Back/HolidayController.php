<?php
// app/Http/Controllers/Admin/HolidayController.php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class HolidayController extends Controller
{
    protected $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    /**
     * نمایش لیست تعطیلات
     */
    public function index()
    {
        $years = [];
        $currentYear = Jalalian::now()->getYear();

        for ($i = 0; $i <= 2; $i++) {
            $year = $currentYear + $i;
            $path = storage_path("app/holidays/{$year}.json");

            if (file_exists($path)) {
                $content = file_get_contents($path);
                $data = json_decode($content, true);
                $years[$year] = $data['data'] ?? [];
            } else {
                $years[$year] = [];
            }
        }

        return view('back.holidays.index', compact('years'));
    }

    /**
     * بررسی تعطیل بودن یک روز
     */
    public function check(Request $request)
    {
        $request->validate([
            'date' => 'required|string'
        ]);

        $isHoliday = $this->holidayService->isHoliday($request->date);

        return response()->json([
            'is_holiday' => $isHoliday,
            'description' => $isHoliday ? $this->holidayService->getHolidayDescription($request->date) : null
        ]);
    }

    /**
     * تبدیل تاریخ میلادی به شمسی
     */
    public function convertToJalali(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $jalali = Jalalian::fromDateTime($request->date);

        return response()->json([
            'jalali' => $jalali->format('Y/m/d'),
            'jalali_display' => $jalali->format('j F Y'),
            'day_name' => $jalali->format('l')
        ]);
    }

    /**
     * دریافت تاریخ‌های شروع بر اساس بازه انتخابی
     */
    public function getStartDates(Request $request)
    {
        $request->validate([
            'start_days' => 'required|integer|min:0',
            'range_days' => 'required|integer|min:1'
        ]);

        $startDays = (int)$request->start_days;
        $rangeDays = (int)$request->range_days;

        // تاریخ شروع (امروز + تعداد روزهای شروع + 1 روز)
        $startDate = now()->addDays($startDays + 1);
        $dates = [];

        for ($i = 0; $i < $rangeDays; $i++) {
            $currentDate = clone $startDate;
            $currentDate->addDays($i);

            $jalali = Jalalian::fromDateTime($currentDate);
            $jalaliDate = $jalali->format('Y/m/d');

            // گرفتن روز هفته به انگلیسی با استفاده از Carbon
            $dayNameEn = $currentDate->format('l'); // Friday, Monday, ...
            $isFriday = ($dayNameEn === 'Friday');
            $persianDayName = $this->getPersianDayName($dayNameEn);

            // بررسی تعطیل بودن
            $isHoliday = $this->holidayService->isHoliday($jalaliDate);
            $holidayDescription = $isHoliday ? $this->holidayService->getHolidayDescription($jalaliDate) : null;

            // اگر جمعه است، حتماً تعطیل در نظر گرفته شود
            if ($isFriday) {
                $isHoliday = true;
                $holidayDescription = 'جمعه';
            }

            $dates[] = [
                'gregorian' => $currentDate->format('Y-m-d'),
                'jalali' => $jalaliDate,
                'jalali_display' => $jalali->format('j F Y'),
                'day_name' => $persianDayName,
                'day_name_en' => $dayNameEn,
                'is_holiday' => $isHoliday,
                'is_friday' => $isFriday,
                'holiday_description' => $holidayDescription
            ];
        }

        return response()->json([
            'success' => true,
            'dates' => $dates,
            'start_date' => $startDate->format('Y-m-d'),
            'start_date_jalali' => Jalalian::fromDateTime($startDate)->format('Y/m/d')
        ]);
    }

    /**
     * تبدیل نام روز انگلیسی به فارسی
     */
    private function getPersianDayName($dayNameEn)
    {
        $days = [
            'Saturday' => 'شنبه',
            'Sunday' => 'یکشنبه',
            'Monday' => 'دوشنبه',
            'Tuesday' => 'سه‌شنبه',
            'Wednesday' => 'چهارشنبه',
            'Thursday' => 'پنجشنبه',
            'Friday' => 'جمعه'
        ];

        return $days[$dayNameEn] ?? $dayNameEn;
    }

    /**
     * آپدیت/ایجاد فایل JSON تعطیلات برای یک سال خاص
     */
    public function updateHolidays(Request $request, $year)
    {
        $request->validate([
            'holidays' => 'required|array',
            'holidays.*.date' => 'required|string',
            'holidays.*.isHoliday' => 'required|boolean',
            'holidays.*.holidayDesription' => 'nullable|string'
        ]);

        $data = [
            'year' => $year,
            'data' => $request->holidays
        ];

        $path = storage_path("app/holidays/{$year}.json");
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // ریفرش سرویس تعطیلات
        $this->holidayService->refresh();

        return response()->json([
            'success' => true,
            'message' => "تعطیلات سال {$year} با موفقیت ذخیره شد"
        ]);
    }

    /**
     * دریافت تعطیلات یک سال خاص
     */
    public function getHolidaysByYear($year)
    {
        $path = storage_path("app/holidays/{$year}.json");

        if (file_exists($path)) {
            $content = file_get_contents($path);
            $data = json_decode($content, true);
            return response()->json($data);
        }

        return response()->json([
            'year' => $year,
            'data' => []
        ]);
    }
}
