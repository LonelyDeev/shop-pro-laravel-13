<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributeGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->enum('type', ['color', 'checkbox']);
            $table->integer('ordering')->nullable();
            $table->softDeletes('deleted_at');

            $table->timestamps();


            // 1. ایندکس برای فیلتر نوع گروه ویژگی
            $table->index('type');

            // 2. ایندکس برای جستجوی نام
            $table->index('name');

            // 3. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 4. ایندکس ترکیبی برای نوع و ترتیب
            $table->index(['type', 'ordering']);

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
        Schema::dropIfExists('attribute_groups');
    }
}
