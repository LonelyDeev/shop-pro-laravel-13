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
        Schema::create('filds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('value')->nullable();
            $table->enum('belongs_to', ['users', 'products', 'blogs'])->comment('فیلد متعلق است به')->default('users');
            $table->enum('type', ['input', 'textarea', 'number', 'email', 'colorPicker', 'checkbox', 'select'])->default('input')->nullable();
            $table->longText('select_options')->nullable()->comment('تنها در صورتی مقدار می‌گیرد که type برابر Select باشد');
            $table->boolean('user_show')->default(false);
            $table->boolean('required')->default(false);
            $table->boolean('published')->default(true);
            $table->timestamps();

            // 1. ایندکس برای فیلتر نوع متعلقات (کاربران/محصولات/بلاگ‌ها)
            $table->index('belongs_to');

            // 2. ایندکس برای فیلتر نوع فیلد
            $table->index('type');

            // 3. ایندکس برای فیلتر وضعیت انتشار
            $table->index('published');

            // 4. ایندکس برای فیلتر الزامی بودن
            $table->index('required');

            // 5. ایندکس ترکیبی برای فیلدهای فعال یک نوع خاص
            $table->index(['belongs_to', 'published']);

            // 6. ایندکس ترکیبی برای فیلدهای الزامی یک نوع خاص
            $table->index(['belongs_to', 'required']);

            // 7. ایندکس ترکیبی برای فیلدهای قابل نمایش در فرانت
            $table->index(['belongs_to', 'user_show']);

            // 8. ایندکس برای جستجوی عنوان
            $table->index('title');

            // 9. ایندکس برای مرتب‌سازی بر اساس تاریخ
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filds');
    }
};
