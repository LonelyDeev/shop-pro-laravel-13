<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');

            $table->decimal('balance', 64, 0)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();


            // 1. ایندکس برای جستجوی کیف پول یک کاربر
            $table->index('user_id');

            // 2. ایندکس برای جستجوی کیف پول یک فروشنده
            $table->index('seller_id');

            // 3. ایندکس برای فیلتر کیف پول‌های فعال
            $table->index('is_active');

            // 4. ایندکس ترکیبی برای کیف پول فعال یک کاربر
            $table->index(['user_id', 'is_active']);

            // 5. ایندکس ترکیبی برای کیف پول فعال یک فروشنده
            $table->index(['seller_id', 'is_active']);

            // 6. ایندکس برای مرتب‌سازی بر اساس موجودی
            $table->index('balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallets');
    }
}
