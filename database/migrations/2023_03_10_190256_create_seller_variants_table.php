<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_variants', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');

            $table->text('prices_id')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی واریانت‌های یک فروشنده
            $table->index('seller_id');

            // 2. ایندکس برای جستجوی واریانت‌های یک محصول
            $table->index('product_id');

            // 3. ایندکس ترکیبی برای واریانت یک فروشنده و محصول
            $table->index(['seller_id', 'product_id']);

            // 4. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('seller_variants');
    }
}
