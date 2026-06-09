<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('size_type_id')->constrained('size_types')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('ordering')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی سایزهای یک نوع خاص
            $table->index('size_type_id');

            // 2. ایندکس برای جستجوی عنوان سایز
            $table->index('title');

            // 3. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 4. ایندکس ترکیبی برای سایزهای یک نوع با ترتیب
            $table->index(['size_type_id', 'ordering']);

            // 5. ایندکس ترکیبی برای سایزهای یک نوع با عنوان
            $table->index(['size_type_id', 'title']);

            // 6. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('sizes');
    }
}
