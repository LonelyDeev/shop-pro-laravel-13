<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->timestamps();


            // 1. ایندکس برای جستجوی ایمیل
            $table->index('email');

            // 2. ایندکس برای جستجوی عنوان پیام
            $table->index('subject');

            // 3. ایندکس برای جستجوی نام
            $table->index('name');

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
        Schema::dropIfExists('contacts');
    }
}
