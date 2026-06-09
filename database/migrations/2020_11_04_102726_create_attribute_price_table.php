<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_price', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');

            $table->unsignedBigInteger('attribute_id');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');

            $table->unsignedBigInteger('price_id');
            $table->foreign('price_id')->references('id')->on('prices')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');

            $table->timestamps();

            // 1. ایندکس برای جستجوی ویژگی‌های یک قیمت
            $table->index('price_id');

            // 2. ایندکس برای جستجوی قیمت‌های یک ویژگی
            $table->index('attribute_id');

            // 3. ایندکس برای جستجوی فروشنده
            $table->index('seller_id');

            // 4. ایندکس برای جستجوی محصول
            $table->index('product_id');

            // 5. ایندکس ترکیبی برای یک قیمت خاص و ویژگی
            $table->index(['price_id', 'attribute_id']);

            // 6. ایندکس ترکیبی برای یک محصول و قیمت
            $table->index(['product_id', 'price_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_price');
    }
}
