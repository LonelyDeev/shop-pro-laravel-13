<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_deposits', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');

            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            $table->integer('percent')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 64, 0);
            $table->enum('status', ['fail', 'success'])->default('fail');
            $table->timestamps();

            // 1. ایندکس برای جستجوی واریزی‌های یک فروشنده
            $table->index('seller_id');

            // 2. ایندکس برای جستجوی واریزی‌های یک سفارش
            $table->index('order_id');

            // 3. ایندکس برای جستجوی واریزی‌های یک دسته‌بندی
            $table->index('category_id');

            // 4. ایندکس برای فیلتر وضعیت
            $table->index('status');

            // 5. ایندکس ترکیبی برای واریزی‌های موفق یک فروشنده
            $table->index(['seller_id', 'status']);

            // 6. ایندکس ترکیبی برای واریزی‌های موفق یک سفارش
            $table->index(['order_id', 'status']);

            // 7. ایندکس برای مرتب‌سازی بر اساس مبلغ
            $table->index('amount');

            // 8. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('seller_deposits');
    }
}
