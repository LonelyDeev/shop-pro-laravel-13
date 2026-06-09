<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMobileVerifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_mobile_verify', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mobile');
            $table->string('code');
            $table->timestamps();

            // 1. ایندکس برای جستجوی کد تأیید یک شماره موبایل (پرکاربردترین)
            $table->index(['mobile', 'code']);

            // 2. ایندکس برای جستجوی کدهای یک شماره
            $table->index('mobile');

            // 3. ایندکس برای پاکسازی کدهای منقضی شده
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
        Schema::dropIfExists('user_mobile_verify');
    }
}
