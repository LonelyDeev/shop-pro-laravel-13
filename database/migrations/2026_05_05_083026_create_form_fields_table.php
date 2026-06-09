<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('label'); // عنوان فیلد
            $table->string('name'); // نام فیلد (برای ارسال)
            $table->enum('type', [
                'text', 'email', 'tel', 'number', 'textarea',
                'select', 'checkbox', 'radio', 'date', 'file',
                'password', 'url', 'hidden'
            ])->default('text');
            $table->boolean('required')->default(false);
            $table->string('placeholder')->nullable();
            $table->text('options')->nullable(); // برای select, radio, checkbox (JSON)
            $table->string('default_value')->nullable();
            $table->string('rules_validation')->nullable(); // قوانین اعتبارسنجی
            $table->integer('order')->default(0); // ترتیب نمایش
            $table->string('class')->nullable(); // کلاس CSS
            $table->string('help_text')->nullable(); // متن راهنما
            $table->json('settings')->nullable(); // تنظیمات اضافی
            $table->timestamps();

            // 1. ایندکس برای جستجوی فیلدهای یک فرم (پرکاربردترین)
            $table->index('form_id');

            // 2. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('order');

            // 3. ایندکس ترکیبی برای فیلدهای یک فرم با ترتیب
            $table->index(['form_id', 'order']);

            // 4. ایندکس برای فیلتر نوع فیلد
            $table->index('type');

            // 5. ایندکس برای فیلتر فیلدهای اجباری
            $table->index('required');

            // 6. ایندکس ترکیبی برای فیلدهای اجباری یک فرم
            $table->index(['form_id', 'required']);

            // 7. ایندکس برای جستجوی نام فیلد
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
