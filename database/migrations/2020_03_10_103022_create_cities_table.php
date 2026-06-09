<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('province_id')->unsigned();
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');

            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();


            // 1. ایندکس برای جستجوی شهرها بر اساس استان (پرکاربردترین)
            $table->index('province_id');

            // 2. ایندکس برای جستجوی نام فارسی شهر
            $table->index('name');

            // 3. ایندکس برای جستجوی نام انگلیسی شهر
            $table->index('name_en');

            // 4. ایندکس ترکیبی برای استان + نام (جستجوی دقیق)
            $table->index(['province_id', 'name']);

            // 5. ایندکس برای موقعیت جغرافیایی (اگر نیاز به جستجوی نزدیک‌ترین شهرها دارید)
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
    }
}
