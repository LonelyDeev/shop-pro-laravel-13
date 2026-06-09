<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('discount_id');
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('cascade');

            // 1. ایندکس برای جستجوی تخفیف‌های یک کاربر
            $table->index('user_id');

            // 2. ایندکس برای جستجوی کاربران استفاده کننده از یک تخفیف
            $table->index('discount_id');

            // 3. ایندکس یکتا برای جلوگیری از استفاده تکراری یک کاربر از یک تخفیف
            $table->unique(['user_id', 'discount_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discount_user');
    }
}
