<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('name_en')->unique()->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();


            // 1. ایندکس برای جستجوی نام فارسی برند
            $table->index('name');

            // 2. ایندکس برای جستجوی نام انگلیسی برند
            $table->index('name_en');

            // 3. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('brands');
    }
}
