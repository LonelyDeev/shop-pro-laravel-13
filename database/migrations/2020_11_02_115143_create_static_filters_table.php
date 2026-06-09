<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaticFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('static_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('type');
            $table->integer('ordering')->nullable();
            $table->timestamps();

            // 1. ایندکس برای فیلتر نوع (برند، رنگ، قیمت، ...)
            $table->index('type');

            // 2. ایندکس برای جستجوی عنوان
            $table->index('title');

            // 3. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 4. ایندکس ترکیبی برای نوع و ترتیب
            $table->index(['type', 'ordering']);

            // 5. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('static_filters');
    }
}
