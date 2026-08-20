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

            // به جای یک صفحه/گروه واحد، آرایه‌ای از صفحات و گروه‌ها ذخیره می‌شود
            $table->json('pages')->nullable();   // مثال: ["home","products"]
            $table->json('groups')->nullable();  // مثال: ["main","banner"]

            $table->string('title')->nullable();
            $table->string('motionTitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('link')->nullable();
            $table->string('lang')->nullable();

            $table->boolean('published')->default(true);
            $table->integer('ordering')->nullable();
            $table->timestamps();

            // ایندکس‌های اصلی
            $table->index('published');
            $table->index('ordering');
            $table->index('created_at');

            // ایندکس ترکیبی برای مرتب‌سازی اسلایدرهای منتشر شده
            $table->index(['published', 'ordering']);
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
