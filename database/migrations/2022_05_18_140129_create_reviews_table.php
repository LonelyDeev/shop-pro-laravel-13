<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->integer('rating');
            $table->enum('suggest', ['yes', 'no', 'not_sure'])->nullable();
            $table->enum('status', ['accepted', 'pending', 'rejected'])->default('pending');
            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0);
            $table->timestamps();


            // 1. ایندکس برای جستجوی نظرات یک محصول (پرکاربردترین)
            $table->index('product_id');

            // 2. ایندکس برای جستجوی نظرات یک کاربر
            $table->index('user_id');

            // 3. ایندکس برای فیلتر وضعیت (تایید شده/در انتظار/رد شده)
            $table->index('status');

            // 4. ایندکس برای فیلتر امتیاز
            $table->index('rating');

            // 5. ایندکس برای فیلتر پیشنهاد خرید
            $table->index('suggest');

            // 6. ایندکس ترکیبی برای نظرات تایید شده یک محصول
            $table->index(['product_id', 'status']);

            // 7. ایندکس ترکیبی برای نظرات پرامتیاز یک محصول
            $table->index(['product_id', 'rating']);

            // 8. ایندکس برای مرتب‌سازی بر اساس مفیدترین نظرات
            $table->index('likes_count');

            // 9. ایندکس برای مرتب‌سازی بر اساس تاریخ
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
        Schema::dropIfExists('reviews');
    }
}
