<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->integer('national_code')->unique()->nullable();
            $table->string('birth_date')->nullable();
            $table->string('card_number')->nullable();
            $table->string('image')->nullable();
            $table->string('level')->default('user');

            $table->string('password')->nullable();
            $table->tinyInteger('status')->nullable()->default('1');
            $table->tinyInteger('newsletter')->nullable()->default('0');
            $table->tinyInteger('notification')->nullable()->default('1');
            $table->rememberToken();
            $table->timestamps();


            // 1. ایندکس برای فیلتر وضعیت کاربر (فعال/غیرفعال)
            $table->index('status');

            // 2. ایندکس برای فیلتر سطح دسترسی
            $table->index('level');

            // 3. ایندکس برای جستجوی نام
            $table->index('first_name');
            $table->index('last_name');

            // 4. ایندکس ترکیبی برای نام و نام خانوادگی
            $table->index(['first_name', 'last_name']);

            // 5. ایندکس برای خبرنامه
            $table->index('newsletter');

            // 6. ایندکس ترکیبی برای وضعیت + سطح (کاربران فعال با سطح خاص)
            $table->index(['status', 'level']);

            // 7. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');

            // 8. ایندکس برای تاریخ تولد (اگر در فیلترها استفاده می‌شود)
            $table->index('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
