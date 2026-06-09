<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSizeTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('size_types', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('lang', 30)->default('fa');
            $table->softDeletes();
            $table->timestamps();

            // 1. ایندکس برای جستجوی عنوان
            $table->index('title');

            // 2. ایندکس برای فیلتر زبان
            $table->index('lang');

            // 3. ایندکس ترکیبی برای عنوان و زبان
            $table->index(['title', 'lang']);

            // 4. ایندکس برای حذف نرم
            $table->index('deleted_at');

            // 5. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('size_types');
    }
}
