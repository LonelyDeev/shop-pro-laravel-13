<?php

namespace Database\Seeders;

use App\Models\ReturnReason;
use Illuminate\Database\Seeder;

class ReturnReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['title' => 'خرابی محصول', 'description' => 'محصول آسیب دیده یا معیوب', 'is_active' => true, 'ordering' => 1],
            ['title' => 'مغایرت با سفارش', 'description' => 'محصول ارسالی با سفارش متفاوت است', 'is_active' => true, 'ordering' => 2],
            ['title' => 'کیفیت پایین', 'description' => 'کیفیت محصول با انتظار همخوانی ندارد', 'is_active' => true, 'ordering' => 3],
            ['title' => 'عدم نیاز', 'description' => 'کاربر دیگر به محصول نیاز ندارد', 'is_active' => true, 'ordering' => 4],
            ['title' => 'ارسال دیرهنگام', 'description' => 'محصول دیرتر از موعد تحویل شده', 'is_active' => true, 'ordering' => 5],
            ['title' => 'خرابی در ارسال', 'description' => 'محصول در حین ارسال آسیب دیده', 'is_active' => true, 'ordering' => 6],
        ];

        foreach ($reasons as $reason) {
            ReturnReason::firstOrCreate(['title' => $reason['title']], $reason);
        }

        option_update('return_days_limit', '7');
        option_update('return_enabled', '1');

    }
}
