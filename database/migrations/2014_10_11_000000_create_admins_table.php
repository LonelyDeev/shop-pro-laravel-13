<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('image')->nullable();
            $table->string('level')->default('admin');
            $table->text('bio')->nullable();
            $table->enum('status',['ACTIVE','INACTIVE'])->default('ACTIVE')->nullable();

            $table->string('password');
            $table->rememberToken();
            $table->timestamps();


            // 1. ایندکس برای فیلتر وضعیت (فعال/غیرفعال)
            $table->index('status');

            // 2. ایندکس برای جستجوی نام (اگر نیاز به جستجو دارید)
            $table->index('first_name');
            $table->index('last_name');

            // 3. ایندکس ترکیبی برای نام و نام خانوادگی (جستجوی همزمان)
            $table->index(['first_name', 'last_name']);

            // 4. ایندکس برای سطح دسترسی (ادمین عادی/سوپرادمین/...)
            $table->index('level');

            // 5. ایندکس ترکیبی برای وضعیت + سطح (جستجوی ادمین‌های فعال با سطح خاص)
            $table->index(['status', 'level']);

            // 6. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');

            // 7. ایندکس برای مرتب‌سازی بر اساس زمان بروزرسانی
            $table->index('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
