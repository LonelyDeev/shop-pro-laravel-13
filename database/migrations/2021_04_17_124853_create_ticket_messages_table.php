<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');

            $table->unsignedBigInteger('ticket_id');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');

            $table->text('message');

            $table->timestamps();


            // 1. ایندکس برای جستجوی پیام‌های یک تیکت (پرکاربردترین)
            $table->index('ticket_id');

            // 2. ایندکس برای جستجوی پیام‌های یک کاربر
            $table->index('user_id');

            // 3. ایندکس برای جستجوی پیام‌های یک فروشنده
            $table->index('seller_id');

            // 4. ایندکس برای جستجوی پیام‌های یک ادمین
            $table->index('admin_id');

            // 5. ایندکس ترکیبی برای پیام‌های یک تیکت با ترتیب زمانی
            $table->index(['ticket_id', 'created_at']);

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
        Schema::dropIfExists('ticket_messages');
    }
}
