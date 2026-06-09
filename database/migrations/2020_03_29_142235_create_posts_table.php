<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('post_type', ['text','video','podcast'])->default('text')->nullable();
            $table->string('title');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->string('slug')->unique();
            $table->text('image')->nullable();
            $table->longText('images')->nullable();

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');

            $table->boolean('published')->default(false);
            $table->unsignedBigInteger('view')->default(0);
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->text('video_url')->nullable()->nullable();
            $table->text('podcast_url')->nullable()->nullable();
            $table->enum('created_by',['admin','ai','ai-pro'])->default('admin')->nullable();
            $table->enum('status',['waiting','end','error'])->default('waiting')->nullable();
            $table->text('more')->nullable();
            $table->text('source')->nullable();

            $table->boolean('is_editor_pick')->default(false)->nullable();
            $table->boolean('allow_comments')->default(true)->nullable();

            $table->timestamps();


            // 1. ایندکس برای فیلتر وضعیت انتشار (منتشر شده/پیش‌نویس)
            $table->index('published');

            // 2. ایندکس برای فیلتر نوع پست (متن، ویدیو، پادکست)
            $table->index('post_type');

            // 3. ایندکس برای دسته‌بندی
            $table->index('category_id');

            // 4. ایندکس برای نویسنده (ادمین)
            $table->index('admin_id');

            // 5. ایندکس برای انتخاب سردبیر
            $table->index('is_editor_pick');

            // 6. ایندکس برای وضعیت تولید محتوا (در انتظار، پایان، خطا)
            $table->index('status');

            // 7. ایندکس برای اجازه کامنت
            $table->index('allow_comments');

            // 8. ایندکس برای مرتب‌سازی بر اساس بازدید
            $table->index('view');

            // 9. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');

            // 10. ایندکس ترکیبی برای نمایش مقالات منتشر شده (پرکاربردترین)
            $table->index(['published', 'created_at']);

            // 11. ایندکس ترکیبی برای مقالات منتشر شده + انتخاب سردبیر
            $table->index(['published', 'is_editor_pick']);

            // 12. ایندکس ترکیبی برای مقالات منتشر شده + نوع پست
            $table->index(['published', 'post_type']);

            // 13. ایندکس ترکیبی برای مقالات یک دسته‌بندی + منتشر شده
            $table->index(['category_id', 'published', 'created_at']);

            // 14. ایندکس ترکیبی برای مقالات یک نویسنده + منتشر شده
            $table->index(['admin_id', 'published', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
