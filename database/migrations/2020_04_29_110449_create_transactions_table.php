<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('status');
            $table->bigInteger('amount')->nullable();
            $table->string('transId')->nullable()->unique();
            $table->string('factorNumber')->nullable();
            $table->string('mobile')->nullable();
            $table->text('description')->nullable();
            $table->string('cardNumber')->nullable();
            $table->string('traceNumber')->nullable();
            $table->string('message')->nullable();
            $table->string('token');

            $table->unsignedBigInteger('transactionable_id');
            $table->string('transactionable_type');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();


            // 1. ایندکس برای فیلتر وضعیت تراکنش (موفق/ناموفق)
            $table->index('status');

            // 2. ایندکس برای جستجوی تراکنش‌های یک کاربر
            $table->index('user_id');

            // 3. ایندکس برای جستجوی تراکنش با توکن
            $table->index('token');

            // 4. ایندکس ترکیبی برای تراکنش‌های یک آیتم (سفارش، فاکتور و ...)
            $table->index(['transactionable_id', 'transactionable_type']);

            // 5. ایندکس برای جستجوی تراکنش با شماره کارت
            $table->index('cardNumber');

            // 6. ایندکس برای جستجوی تراکنش با شماره پیگیری
            $table->index('traceNumber');

            // 7. ایندکس برای جستجوی تراکنش با شماره فاکتور
            $table->index('factorNumber');

            // 8. ایندکس برای جستجوی موبایل
            $table->index('mobile');

            // 9. ایندکس ترکیبی برای تراکنش‌های موفق یک کاربر
            $table->index(['user_id', 'status']);

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
        Schema::dropIfExists('transactions');
    }
}
