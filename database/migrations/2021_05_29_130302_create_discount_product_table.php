<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unsignedBigInteger('discount_id');
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('cascade');

            $table->enum('type', ['include', 'exclude']);

            $table->unique(['product_id', 'discount_id', 'type']);

            // 1. ایندکس برای جستجوی محصولات یک تخفیف
            $table->index('discount_id');

            // 2. ایندکس برای جستجوی تخفیف‌های یک محصول
            $table->index('product_id');

            // 3. ایندکس ترکیبی برای نوع شامل/عدم شامل
            $table->index(['discount_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discount_product');
    }
}
