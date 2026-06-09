<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('attribute_group_id');
            $table->foreign('attribute_group_id')->references('id')->on('attribute_groups')->onDelete('cascade');
            $table->string('name');
            $table->string('value')->nullable();
            $table->softDeletes('deleted_at');

            $table->timestamps();

            // 1. ایندکس برای جستجوی ویژگی‌های یک گروه
            $table->index('attribute_group_id');

            // 2. ایندکس برای جستجوی نام ویژگی
            $table->index('name');

            // 3. ایندکس برای جستجوی مقدار (مثلاً کد رنگ)
            $table->index('value');

            // 4. ایندکس ترکیبی برای گروه و نام
            $table->index(['attribute_group_id', 'name']);

            // 5. ایندکس برای مرتب‌سازی بر اساس تاریخ
            $table->index('created_at');

            // 6. ایندکس برای حذف نرم
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attributes');
    }
}
