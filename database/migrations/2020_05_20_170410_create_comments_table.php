<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('comment_id')->nullable();
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');

            $table->text('body');

            $table->bigInteger('commentable_id');
            $table->string('commentable_type');

            $table->string('status')->default('pending');

            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0);

            $table->string('ip_address')->nullable();
            $table->string('session_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();


            // 1. ایندکس برای جستجوی کامنت‌های یک آیتم (پرکاربردترین)
            $table->index(['commentable_id', 'commentable_type']);

            // 2. ایندکس برای جستجوی پاسخ‌های یک کامنت
            $table->index('comment_id');

            // 3. ایندکس برای فیلتر وضعیت کامنت (تایید/در انتظار/رد)
            $table->index('status');

            // 4. ایندکس برای جستجوی کامنت‌های یک کاربر
            $table->index('user_id');

            // 5. ایندکس برای جستجوی کامنت‌های یک ادمین
            $table->index('admin_id');

            // 6. ایندکس ترکیبی برای کامنت‌های تایید شده یک آیتم
            $table->index(['commentable_id', 'commentable_type', 'status']);

            // 7. ایندکس برای مرتب‌سازی بر اساس لایک
            $table->index('likes_count');

            // 8. ایندکس برای مرتب‌سازی بر اساس تاریخ
            $table->index('created_at');

            // 9. ایندکس برای جستجوی آی پی
            $table->index('ip_address');

            // 10. ایندکس برای جستجوی نشست
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
