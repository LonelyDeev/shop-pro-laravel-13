<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_discount', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            $table->unsignedBigInteger('discount_id');
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('cascade');

            $table->enum('type', ['include', 'exclude']);

            $table->unique(['category_id', 'discount_id', 'type']);

            // 1. ایندکس برای جستجوی دسته‌بندی‌های یک تخفیف
            $table->index('discount_id');

            // 2. ایندکس برای جستجوی تخفیف‌های یک دسته‌بندی
            $table->index('category_id');

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
        Schema::dropIfExists('category_discount');
    }
}
