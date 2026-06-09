<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->string('subject');
            $table->enum('priority', ['low', 'medium', 'hight']);

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');

            $table->enum('status', ['pending', 'close', 'open', 'answered'])->default('pending');

            $table->timestamps();

            // 1. ایندکس برای فیلتر وضعیت تیکت
            $table->index('status');

            // 2. ایندکس برای فیلتر اولویت
            $table->index('priority');

            // 3. ایندکس برای جستجوی تیکت‌های یک کاربر
            $table->index('user_id');

            // 4. ایندکس برای جستجوی تیکت‌های یک فروشنده
            $table->index('seller_id');

            // 5. ایندکس برای جستجوی تیکت‌های یک ادمین
            $table->index('admin_id');

            // 6. ایندکس ترکیبی برای تیکت‌های باز یک کاربر
            $table->index(['user_id', 'status']);

            // 7. ایندکس ترکیبی برای تیکت‌های با اولویت بالا
            $table->index(['priority', 'status']);

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
        Schema::dropIfExists('tickets');
    }
}
