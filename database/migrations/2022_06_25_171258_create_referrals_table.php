<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_discount_id')->nullable()->constrained('discounts')->onDelete('set null');
            $table->foreignId('user_discount_id')->nullable()->constrained('discounts')->onDelete('set null');
            $table->integer('owner_wallet_history_id')->nullable()->constrained('wallet_histories')->onDelete('set null');
            $table->integer('user_wallet_history_id')->nullable()->constrained('wallet_histories')->onDelete('set null');
            $table->timestamps();

            // 1. ایندکس برای جستجوی معرفی‌های یک کاربر (کسی که معرفی کرده)
            $table->index('owner_id');

            // 2. ایندکس برای جستجوی کاربرانی که توسط یک فرد معرفی شده‌اند
            $table->index('user_id');

            // 3. ایندکس ترکیبی برای روابط یکتا
            $table->unique(['owner_id', 'user_id']);

            // 4. ایندکس برای تخفیف معرف
            $table->index('owner_discount_id');

            // 5. ایندکس برای تخفیف کاربر معرفی شده
            $table->index('user_discount_id');

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
        Schema::dropIfExists('referrals');
    }
}
