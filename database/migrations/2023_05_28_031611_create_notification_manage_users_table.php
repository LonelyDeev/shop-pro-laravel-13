<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationManageUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_manage_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_manage_id');
            $table->foreign('notification_manage_id')->references('id')->on('notification_manages')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');

            $table->tinyInteger('read')->default('0')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی اعلان‌های یک کاربر
            $table->index('user_id');

            // 2. ایندکس برای جستجوی اعلان‌های یک فروشنده
            $table->index('seller_id');

            // 3. ایندکس برای فیلتر وضعیت خوانده شده
            $table->index('read');

            // 4. ایندکس برای جستجوی کاربران یک اعلان
            $table->index('notification_manage_id');

            // 5. ایندکس ترکیبی برای اعلان‌های خوانده نشده یک کاربر
            $table->index(['user_id', 'read']);

            // 6. ایندکس ترکیبی برای اعلان‌های خوانده نشده یک فروشنده
            $table->index(['seller_id', 'read']);

            // 7. ایندکس یکتا برای جلوگیری از تکرار
            $table->unique(['notification_manage_id', 'user_id', 'seller_id'], 'notif_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_manage_users');
    }
}
