<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarrierCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carrier_city', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');

            $table->unsignedBigInteger('carrier_id');
            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('cascade');

            // 1. ایندکس برای جستجوی روش‌های ارسال یک شهر
            $table->index('city_id');

            // 2. ایندکس برای جستجوی شهرهای تحت پوشش یک روش ارسال
            $table->index('carrier_id');

            // 3. ایندکس یکتا برای جلوگیری از تکرار
            $table->unique(['city_id', 'carrier_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carrier_city');
    }
}
