<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAbilityScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_ability_scores', function (Blueprint $table) {
            $table->bigInteger('product_id');
            $table->string('name');
            $table->string('value')->default(0)->nullable();
            $table->integer('ordering')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی امتیازات یک محصول
            $table->index('product_id');

            // 2. ایندکس برای جستجوی نام امتیاز
            $table->index('name');

            // 3. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 4. ایندکس ترکیبی برای امتیازات یک محصول با ترتیب
            $table->index(['product_id', 'ordering']);

            // 5. ایندکس برای مقادیر بالاتر از صفر
            $table->index('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_ability_scores');
    }
}
