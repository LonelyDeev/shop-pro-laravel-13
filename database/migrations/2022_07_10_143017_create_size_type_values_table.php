<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSizeTypeValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('size_type_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('size_type_id');
            $table->foreign('size_type_id')->references('id')->on('size_types')->cascadeOnDelete();

            $table->unsignedBigInteger('size_id');
            $table->foreign('size_id')->references('id')->on('sizes')->cascadeOnDelete();

            $table->string('value')->nullable();
            $table->integer('group');
            $table->integer('ordering')->nullable();

            $table->timestamps();

            // 1. ایندکس برای جستجوی مقادیر یک نوع سایز
            $table->index('size_type_id');

            // 2. ایندکس برای جستجوی مقادیر یک سایز خاص
            $table->index('size_id');

            // 3. ایندکس برای فیلتر گروه
            $table->index('group');

            // 4. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 5. ایندکس ترکیبی برای مقادیر یک نوع سایز با ترتیب گروه
            $table->index(['size_type_id', 'group', 'ordering']);

            // 6. ایندکس ترکیبی برای مقادیر یک سایز در یک گروه خاص
            $table->index(['size_id', 'group']);

            // 7. ایندکس ترکیبی برای مقدار خاص در یک سایز و نوع
            $table->index(['size_type_id', 'size_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('size_type_values');
    }
}
