<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockNotifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_notifies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->boolean('seen')->default(false);

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->timestamps();


            // 1. ایندکس برای جستجوی درخواست‌های یک محصول
            $table->index('product_id');

            // 2. ایندکس برای فیلتر وضعیت دیده شده/نشده
            $table->index('seen');

            // 3. ایندکس ترکیبی برای درخواست‌های دیده نشده یک محصول
            $table->index(['product_id', 'seen']);

            // 4. ایندکس برای جستجوی موبایل
            $table->index('mobile');

            // 5. ایندکس برای جستجوی ایمیل
            $table->index('email');

            // 6. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('stock_notifies');
    }
}
