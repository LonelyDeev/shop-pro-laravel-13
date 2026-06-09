<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('page')->default('home')->nullable();
            $table->string('image')->nullable();
            $table->boolean('published')->default(true);
            $table->string('link')->nullable();
            $table->integer('ordering')->nullable();
            $table->string('group');
            $table->string('place')->nullable();
            $table->timestamps();


            // 1. ایندکس برای فیلتر صفحه
            $table->index('page');

            // 2. ایندکس برای فیلتر وضعیت انتشار
            $table->index('published');

            // 3. ایندکس برای گروه بنر
            $table->index('group');

            // 4. ایندکس برای موقعیت نمایش (مکانی که بنر قرار می‌گیرد)
            $table->index('place');

            // 5. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 6. ایندکس ترکیبی برای بنرهای منتشر شده یک صفحه
            $table->index(['page', 'published']);

            // 7. ایندکس ترکیبی برای بنرهای منتشر شده یک گروه
            $table->index(['group', 'published']);

            // 8. ایندکس ترکیبی برای بنرهای یک صفحه با ترتیب
            $table->index(['page', 'ordering']);

            // 9. ایندکس ترکیبی برای بنرهای یک گروه با ترتیب
            $table->index(['group', 'ordering']);

            // 10. ایندکس ترکیبی برای بنرهای یک مکان در یک صفحه
            $table->index(['page', 'place']);

            // 11. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
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
        Schema::dropIfExists('banners');
    }
}
