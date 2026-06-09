<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserved_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->unsignedBigInteger('reserved_order_id');
            $table->foreign('reserved_order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->timestamps();

            // 1. ایندکس برای جستجوی سفارش‌های اصلی
            $table->index('order_id');

            // 2. ایندکس برای جستجوی سفارش‌های رزرو شده
            $table->index('reserved_order_id');

            // 3. ایندکس یکتا برای جلوگیری از تکرار
            $table->unique(['order_id', 'reserved_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserved_orders');
    }
}
