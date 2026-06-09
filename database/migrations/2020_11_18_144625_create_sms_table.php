<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mobile');
            $table->string('ip');
            $table->string('type')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی پیامک‌های یک شماره موبایل
            $table->index('mobile');

            // 2. ایندکس برای جستجوی پیامک‌های یک آی پی
            $table->index('ip');

            // 3. ایندکس برای فیلتر نوع پیامک (احراز هویت، اطلاع‌رسانی، ...)
            $table->index('type');

            // 4. ایندکس ترکیبی برای یک شماره با نوع خاص
            $table->index(['mobile', 'type']);

            // 5. ایندکس ترکیبی برای جلوگیری از ارسال پیامک تکراری
            $table->index(['mobile', 'ip', 'type']);

            // 6. ایندکس برای مرتب‌سازی بر اساس تاریخ
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
}
