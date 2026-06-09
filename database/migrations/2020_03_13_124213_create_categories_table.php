<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->bigInteger('ordering')->nullable();
            $table->integer('commission')->nullable();
            $table->timestamps();


            // 1. ایندکس برای جستجوی دسته‌بندی والد (زیردسته‌ها)
            $table->index('category_id');

            // 2. ایندکس برای فیلتر نوع دسته‌بندی (محصولات، مقالات، و ...)
            $table->index('type');

            // 3. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 4. ایندکس برای جستجوی عنوان
            $table->index('title');

            // 5. ایندکس ترکیبی برای نوع + والد (دسته‌بندی‌های محصولات که زیردسته ندارند)
            $table->index(['type', 'category_id']);

            // 6. ایندکس ترکیبی برای نوع + ترتیب (مرتب‌سازی دسته‌بندی‌ها بر اساس ترتیب)
            $table->index(['type', 'ordering']);

            // 7. ایندکس ترکیبی برای والد + ترتیب (زیردسته‌های مرتب شده)
            $table->index(['category_id', 'ordering']);

            // 8. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
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
        Schema::dropIfExists('categories');
    }
}
