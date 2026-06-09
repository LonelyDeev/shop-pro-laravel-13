<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOneTimeCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('one_time_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('code');
            $table->timestamps();

            // 1. ایندکس برای جستجوی کدهای یک کاربر
            $table->index('user_id');

            // 2. ایندکس برای جستجوی خود کد (برای احراز هویت)
            $table->index('code');

            // 3. ایندکس ترکیبی برای کاربر و کد (سریع‌ترین جستجو)
            $table->index(['user_id', 'code']);

            // 4. ایندکس برای پاکسازی کدهای قدیمی
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
        Schema::dropIfExists('one_time_codes');
    }
}
