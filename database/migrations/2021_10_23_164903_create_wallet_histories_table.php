<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');

            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');

            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('set null');


            $table->enum('type', ['deposit', 'withdraw'])->index();
            $table->enum('source', ['admin', 'user','seller']);
            $table->enum('status', ['fail', 'success'])->default('fail');
            $table->decimal('amount', 64, 0);
            $table->text('description')->nullable();
            $table->tinyInteger('withdraw')->nullable()->default('0');
            $table->tinyInteger('orderCanceled')->nullable()->default('0');
            $table->string('trackingId')->nullable()->unique();
            $table->enum('status_pay', ['waiting', 'pay','unpay','unpay-refund'])->default('waiting')->nullable();
            $table->timestamps();


            // 1. ایندکس برای جستجوی تاریخچه یک کیف پول (پرکاربردترین)
            $table->index('wallet_id');

            // 2. ایندکس برای جستجوی سفارش مرتبط
            $table->index('order_id');

            // 2. ایندکس برای جستجوی سفارش مرتبط
            $table->index('order_item_id');

            // 3. ایندکس برای فیلتر منبع (ادمین، کاربر، فروشنده)
            $table->index('source');

            // 4. ایندکس برای فیلتر وضعیت تراکنش
            $table->index('status');

            // 5. ایندکس برای فیلتر وضعیت پرداخت
            $table->index('status_pay');

            // 6. ایندکس ترکیبی برای تاریخچه موفق یک کیف پول
            $table->index(['wallet_id', 'status']);

            // 7. ایندکس ترکیبی برای واریزهای موفق
            $table->index(['type', 'status']);

            // 8. ایندکس ترکیبی برای درخواست‌های برداشت
            $table->index(['type', 'withdraw']);

            // 9. ایندکس برای مرتب‌سازی بر اساس مبلغ
            $table->index('amount');

            // 10. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('wallet_histories');
    }
}
