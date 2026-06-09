<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('file');
            $table->string('size')->nullable();
            $table->integer('fileable_id');
            $table->string('fileable_type');
            $table->timestamps();


            // 1. ایندکس ترکیبی برای جستجوی فایل‌های یک آیتم (پرکاربردترین)
            $table->index(['fileable_id', 'fileable_type']);

            // 2. ایندکس برای جستجوی نوع fileable
            $table->index('fileable_type');

            // 3. ایندکس برای جستجوی فایل بر اساس اندازه
            $table->index('size');

            // 4. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('files');
    }
}
