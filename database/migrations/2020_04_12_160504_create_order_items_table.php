<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->string('title');
            $table->bigInteger('price');
            $table->integer('quantity');
            $table->integer('discount')->nullable();
            $table->integer('commission')->nullable();
            $table->string('delivery_date')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();
            $table->integer('shipping_cost')->nullable();

            $table->string('shipping_status')->default('w-pending');
            $table->text('tracking_code')->nullable();

            $table->boolean('refunded')->default(false);
            $table->timestamp('refunded_at')->nullable();
            $table->bigInteger('refunded_amount')->default(0);

            $table->json('attributes')->nullable();

            $table->text('cancel_reason')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();


            // 1. ایندکس برای جستجوی آیتم‌های یک سفارش (پرکاربردترین)
            $table->index('order_id');

            // 2. ایندکس برای جستجوی آیتم‌های یک فروشنده
            $table->index('seller_id');

            // 3. ایندکس برای جستجوی آیتم‌های یک محصول
            $table->index('product_id');

            // 4. ایندکس ترکیبی برای سفارشات یک فروشنده در یک سفارش خاص
            $table->index(['order_id', 'seller_id']);

            // 5. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('order_items');
    }
}
