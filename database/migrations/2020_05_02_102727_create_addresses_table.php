<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fullname');
            $table->string('mobile',11)->nullable();

            $table->unsignedBigInteger('province_id');
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');

            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');

            $table->string('province_name');
            $table->string('city_name');

            $table->string('postal_code');
            $table->string('address', 300);

            $table->integer('buildingNumber');
            $table->string('unit')->nullable();

            $table->string('lat')->nullable();
            $table->string('lng')->nullable();

            $table->tinyInteger('active')->nullable()->default('0');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();


            // 1. ایندکس برای جستجوی آدرس‌های یک کاربر (پرکاربردترین)
            $table->index('user_id');

            // 2. ایندکس برای فیلتر آدرس فعال
            $table->index('active');

            // 3. ایندکس برای جستجوی آدرس‌های یک استان
            $table->index('province_id');

            // 4. ایندکس برای جستجوی آدرس‌های یک شهر
            $table->index('city_id');

            // 5. ایندکس ترکیبی برای آدرس فعال یک کاربر
            $table->index(['user_id', 'active']);

            // 6. ایندکس برای جستجوی کد پستی
            $table->index('postal_code');

            // 7. ایندکس برای جستجوی موبایل
            $table->index('mobile');

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
        Schema::dropIfExists('addresses');
    }
}
