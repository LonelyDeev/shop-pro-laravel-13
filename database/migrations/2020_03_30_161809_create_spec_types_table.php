<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spec_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();


            // 1. ایندکس برای جستجوی نام نوع مشخصات (پرکاربرد)
            $table->index('name');

            // 2. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');

            // 3. اگر نام باید یکتا باشد (پیشنهادی)
            // $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spec_types');
    }
}
