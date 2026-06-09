<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug')->unique();

            $table->boolean('published')->default(false);
            $table->text('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();


            // 1. ایندکس برای فیلتر وضعیت انتشار (صفحات منتشر شده)
            $table->index('published');

            // 2. ایندکس برای جستجوی عنوان صفحه
            $table->index('title');

            // 3. ایندکس ترکیبی برای صفحات منتشر شده با عنوان
            $table->index(['published', 'title']);

            // 4. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
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
        Schema::dropIfExists('pages');
    }
}
