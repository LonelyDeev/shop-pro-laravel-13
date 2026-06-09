<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');

            // تنظیمات نمایش فرم
            $table->enum('form_position', ['top', 'bottom'])->default('top'); // موقعیت فرم نسبت به توضیحات
            $table->enum('form_width', ['full', 'half', 'third'])->default('full'); // عرض فرم
            $table->enum('form_alignment', ['center', 'right', 'left'])->default('center'); // تراز فرم

            // استایل فرم
            $table->string('form_class')->nullable(); // کلاس اضافی فرم
            $table->text('custom_css')->nullable(); // CSS سفارشی

            // تنظیمات پیش‌فرض فیلدها
            $table->string('default_column_class')->default('col-md-6'); // کلاس پیش‌فرض ستون
            $table->json('field_settings')->nullable(); // تنظیمات خاص هر فیلد

            $table->timestamps();

            // 1. ایندکس یکتا برای هر فرم (هر فرم فقط یک تنظیمات دارد)
            $table->unique('form_id');

            // 2. ایندکس برای فیلتر موقعیت فرم
            $table->index('form_position');

            // 3. ایندکس برای فیلتر عرض فرم
            $table->index('form_width');

            // 4. ایندکس برای فیلتر تراز فرم
            $table->index('form_alignment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_settings');
    }
};
