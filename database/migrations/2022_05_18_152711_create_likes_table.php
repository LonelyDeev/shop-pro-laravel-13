<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            // Polymorphic fields
            $table->morphs('likeable'); // likeable_id + likeable_type

            // کاربر (ثبت شده یا مهمان)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // نوع لایک (لایک یا دیسلایک)
            $table->enum('type', ['like', 'dislike'])->default('like');

            // اطلاعات مهمان
            $table->string('ip_address')->nullable();
            $table->string('session_id')->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('device_type', ['mobile', 'desktop', 'tablet'])->nullable();
            $table->boolean('is_guest')->default(true);

            $table->timestamps();


            // 2. ایندکس برای جستجوی لایک‌های یک کاربر
            $table->index('user_id');

            // 3. ایندکس برای فیلتر نوع لایک
            $table->index('type');

            // 4. ایندکس ترکیبی برای یک کاربر و یک آیتم (جلوگیری از لایک تکراری)
            $table->unique(['likeable_id', 'likeable_type', 'user_id'], 'likes_unique_user');

            // 5. ایندکس ترکیبی برای آیتم و آی پی (جلوگیری از لایک تکراری مهمان)
            $table->index(['likeable_id', 'likeable_type', 'ip_address'], 'likes_ip_index');

            // 6. ایندکس ترکیبی برای آیتم و نشست
            $table->index(['likeable_id', 'likeable_type', 'session_id'], 'likes_session_index');

            // 7. ایندکس برای فیلتر نوع دستگاه
            $table->index('device_type');

            // 8. ایندکس برای فیلتر کاربر مهمان/عضو
            $table->index('is_guest');

            // 9. ایندکس برای آی پی
            $table->index('ip_address');

            // 10. ایندکس برای نشست
            $table->index('session_id');

            // 11. ایندکس برای مرتب‌سازی بر اساس تاریخ
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
