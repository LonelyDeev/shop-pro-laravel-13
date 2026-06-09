<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('page')->default('home')->nullable();
            $table->string('title');
            $table->string('key');
            $table->boolean('is_active')->default(true);
            $table->string('theme');
            $table->integer('ordering')->nullable();
            $table->timestamps();


            // 1. ایندکس برای فیلتر صفحه (ویجت‌های صفحه اصلی، محصولات، ...)
            $table->index('page');

            // 2. ایندکس برای فیلتر وضعیت فعال
            $table->index('is_active');

            // 3. ایندکس برای جستجوی کلید ویجت
            $table->index('key');

            // 4. ایندکس برای فیلتر قالب (theme)
            $table->index('theme');

            // 5. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 6. ایندکس ترکیبی برای ویجت‌های فعال یک صفحه
            $table->index(['page', 'is_active']);

            // 7. ایندکس ترکیبی برای ویجت‌های فعال یک قالب
            $table->index(['theme', 'is_active']);

            // 8. ایندکس ترکیبی برای ویجت‌های یک صفحه با ترتیب
            $table->index(['page', 'ordering']);

            // 9. ایندکس ترکیبی برای ویجت‌های یک قالب با ترتیب
            $table->index(['theme', 'ordering']);

            // 10. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('widgets');
    }
}
