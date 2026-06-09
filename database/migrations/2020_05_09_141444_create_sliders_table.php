<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('page')->default('home')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image');
            $table->boolean('published')->default(true);
            $table->string('link')->nullable();
            $table->integer('ordering')->nullable();
            $table->string('group');
            $table->timestamps();


            // 1. ایندکس برای فیلتر صفحه (اسلایدر صفحه اصلی، محصولات، ...)
            $table->index('page');

            // 2. ایندکس برای فیلتر وضعیت انتشار
            $table->index('published');

            // 3. ایندکس برای گروه اسلایدر (اسلایدر اصلی، اسلایدر محصولات، ...)
            $table->index('group');

            // 4. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 5. ایندکس ترکیبی برای اسلایدرهای منتشر شده یک صفحه
            $table->index(['page', 'published']);

            // 6. ایندکس ترکیبی برای اسلایدرهای منتشر شده یک گروه
            $table->index(['group', 'published']);

            // 7. ایندکس ترکیبی برای اسلایدرهای یک صفحه با ترتیب
            $table->index(['page', 'ordering']);

            // 8. ایندکس ترکیبی برای اسلایدرهای یک گروه با ترتیب
            $table->index(['group', 'ordering']);

            // 9. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
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
        Schema::dropIfExists('sliders');
    }
}
