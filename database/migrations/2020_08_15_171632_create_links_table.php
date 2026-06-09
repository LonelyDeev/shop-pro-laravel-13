<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('link');
            $table->integer('ordering')->nullable();
            $table->string('link_group_id');
            $table->timestamps();


            // 1. ایندکس برای جستجوی لینک‌های یک گروه (پرکاربردترین)
            $table->index('link_group_id');

            // 2. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 3. ایندکس ترکیبی برای لینک‌های یک گروه با ترتیب
            $table->index(['link_group_id', 'ordering']);

            // 4. ایندکس برای جستجوی عنوان
            $table->index('title');

            // 5. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
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
        Schema::dropIfExists('links');
    }
}
