<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationManagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_manages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins');

            $table->string('title')->nullable();
            $table->text('message');
            $table->tinyInteger('popup')->default('0')->nullable();
            $table->enum('priority',['high','medium','low'])->default('low')->nullable();
            $table->enum('private',['all','user','seller'])->default('all')->nullable();
            $table->tinyInteger('allUsers')->default('1')->nullable();
            $table->tinyInteger('allSellers')->default('1')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی اعلان‌های یک ادمین
            $table->index('admin_id');

            // 2. ایندکس برای فیلتر اولویت
            $table->index('priority');

            // 3. ایندکس برای فیلتر خصوصی (همه/کاربر/فروشنده)
            $table->index('private');

            // 4. ایندکس برای فیلتر پاپ‌آپ
            $table->index('popup');

            // 5. ایندکس ترکیبی برای اولویت و خصوصی
            $table->index(['priority', 'private']);

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
        Schema::dropIfExists('notification_manages');
    }
}
