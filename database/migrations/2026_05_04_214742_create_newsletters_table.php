<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();

            // فقط یک فیلد برای ثبت ایمیل یا شماره
            $table->string('contact')->unique(); // میتونه ایمیل باشه یا شماره موبایل

            // وضعیت
            $table->boolean('is_active')->default(true);

            // اطلاعات فنی
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->longText('referrer')->nullable();
            $table->longText('landing_page')->nullable();

            $table->timestamps();

            // 1. ایندکس یکتا برای contact (قبلاً وجود دارد)
            // 2. ایندکس برای فیلتر وضعیت فعال
            $table->index('is_active');

            // 3. ایندکس برای مرتب‌سازی بر اساس تاریخ ثبت‌نام
            $table->index('created_at');

            // 4. ایندکس برای جستجوی contact (برای LIKE)
            $table->index('contact');

            // 5. ایندکس برای فیلتر نوع دستگاه
            $table->index('device_type');

            // 6. ایندکس ترکیبی برای کاربران فعال ثبت شده در بازه زمانی خاص
            $table->index(['is_active', 'created_at']);

            // 7. ایندکس برای آی پی
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletters');
    }
};
