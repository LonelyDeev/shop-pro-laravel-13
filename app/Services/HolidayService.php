<?php
// app/Services/HolidayService.php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

class HolidayService
{
    protected $holidays = [];

    public function __construct()
    {
        $this->loadHolidays();
    }

    /**
     * بارگذاری فایل‌های JSON تعطیلات
     */
    protected function loadHolidays()
    {
        $currentYear = Jalalian::now()->getYear();

        for ($i = 0; $i <= 2; $i++) {
            $year = $currentYear + $i;
            $this->loadYearHolidays($year);
        }
    }

    /**
     * بارگذاری تعطیلات یک سال خاص
     */
    protected function loadYearHolidays($year)
    {
        $path = public_path("back/app-assets/holidays/{$year}.json");

        if (file_exists($path)) {
            $content = file_get_contents($path);
            $data = json_decode($content, true);

            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $holiday) {
                    if (isset($holiday['isHoliday']) && $holiday['isHoliday'] === true) {
                        $this->holidays[$holiday['date']] = $holiday;
                    }
                }
            }
        }
    }

    /**
     * بررسی تعطیل بودن یک تاریخ شمسی (جمعه‌ها همیشه تعطیل)
     */
    public function isHoliday($jalaliDate)
    {
        // جمعه‌ها همیشه تعطیل هستند
        if ($this->isFriday($jalaliDate)) {
            return true;
        }

        // بررسی تعطیلات رسمی از JSON
        return isset($this->holidays[$jalaliDate]);
    }

    /**
     * بررسی جمعه بودن تاریخ
     */
    protected function isFriday($jalaliDate)
    {
        try {
            $jalali = Jalalian::fromFormat('Y/m/d', $jalaliDate);
            $dayOfWeek = $jalali->format('l');
            return $dayOfWeek === 'Friday';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * دریافت توضیح تعطیل
     */
    public function getHolidayDescription($jalaliDate)
    {
        if ($this->isFriday($jalaliDate)) {
            return 'جمعه';
        }

        return $this->holidays[$jalaliDate]['holidayDesription'] ?? 'تعطیل رسمی';
    }

    /**
     * بررسی اینکه آیا جمعه است
     */
    public function isFridayDate($jalaliDate)
    {
        return $this->isFriday($jalaliDate);
    }

    public function refresh()
    {
        $this->holidays = [];
        $this->loadHolidays();
    }
}
