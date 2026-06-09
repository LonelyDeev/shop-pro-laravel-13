<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('published')->default(true);
            $table->string('success_message')->default('فرم با موفقیت ارسال شد');
            $table->string('button_text')->default('ارسال');
            $table->string('email_notify')->nullable(); // ایمیل برای دریافت اعلان
            $table->json('settings')->nullable(); // تنظیمات اضافی
            $table->timestamps();

            // 1. ایندکس برای فیلتر وضعیت انتشار
            $table->index('published');

            // 2. ایندکس برای جستجوی عنوان
            $table->index('title');

            // 3. ایندکس ترکیبی برای فرم‌های منتشر شده با عنوان
            $table->index(['published', 'title']);

            // 4. ایندکس برای مرتب‌سازی بر اساس تاریخ
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
