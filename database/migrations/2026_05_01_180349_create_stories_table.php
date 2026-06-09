<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();

            // فیلدهای اصلی
            $table->string('title')->nullable();
            $table->enum('type', ['video', 'image'])->default('image');

            // فایل‌ها
            $table->string('cover_image')->nullable();
            $table->string('video')->nullable();
            $table->string('image')->nullable();

            // آمار (فیلدهای ساده)
            $table->bigInteger('real_views_count')->default(0)->nullable();
            $table->bigInteger('views_count')->default(0)->nullable();;
            $table->bigInteger('likes_count')->default(0)->nullable();; // این فیلد به صورت cached نگهداری می‌شود

            // تاریخ انقضا
            $table->timestamp('expiry_date')->nullable();
            $table->string('expiry_date_persian')->nullable();

            // ویجت
            $table->string('widget_title')->nullable();
            $table->string('widget_link')->nullable();

            // محصول
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');


            $table->boolean('active_comments')->default(true);
            $table->boolean('active_likes')->default(true);

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');

            $table->integer('duration')->default(5)->nullable();
            // وضعیت

            $table->integer('product_clicks_count')->default(0)->nullable();
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable();
            $table->enum('status', ['active', 'inactive','expire'])->default('active');
            $table->integer('ordering')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // ========== ایندکس‌های اضافی Stories ==========
            $table->index('type');
            $table->index('expiry_date');
            $table->index('views_count');
            $table->index('likes_count');
            $table->index('status');
            $table->index('ordering');
            $table->index('created_at');
            $table->index(['status', 'expiry_date']);
            $table->index(['type', 'status']);
        });

        // ===== جدول لایک استوری =====
        Schema::create('story_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('stories')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->string('session_id')->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('device_type', ['mobile', 'desktop', 'tablet'])->nullable();
            $table->boolean('is_guest')->default(true);
            $table->timestamps();

            // ========== ایندکس‌های Story Likes ==========
            $table->index(['story_id', 'user_id']);
            $table->index(['story_id', 'ip_address']);
            $table->index(['story_id', 'session_id']);
            $table->index('created_at');
            $table->unique(['story_id', 'user_id'], 'unique_user_story_like');
            $table->index(['story_id', 'ip_address', 'session_id']);
            $table->index('device_type');
        });

        // ===== جدول کامنت استوری =====
        Schema::create('story_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('story_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            // ========== ایندکس‌های Story Comments ==========
            $table->foreign('story_id')->references('id')->on('stories')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['story_id', 'status']);
            $table->index('status');
            $table->index('created_at');
            $table->index('user_id');
        });

        // ===== جدول تعاملات استوری =====
        Schema::create('story_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->onDelete('cascade');

            $table->string('type')->default('like_button')->nullable();

            // اطلاعات تکمیلی
            $table->string('element_id')->nullable();     // آیدی الماسی که کلیک شده
            $table->string('element_text')->nullable();   // متن المان
            $table->text('target_url')->nullable();     // آدرس مقصد
            $table->json('additional_data')->nullable();   // داده‌های اضافی
            $table->integer('count')->default(1);
            $table->timestamp('last_interacted_at')->nullable();

            // اطلاعات کاربر
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('device_type')->nullable();     // mobile, desktop, tablet

            // زمان تعامل
            $table->timestamp('interacted_at');
            $table->timestamps();

            // ========== ایندکس‌های Story Interactions ==========
            $table->index(['story_id', 'type']);
            $table->index(['story_id', 'interacted_at']);
            $table->index('type');
            $table->index('session_id');
            $table->index('user_id');
            $table->index('device_type');
            $table->index(['story_id', 'user_id', 'type']);
            $table->index('interacted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_likes');
        Schema::dropIfExists('story_comments');
        Schema::dropIfExists('story_interactions');
        Schema::dropIfExists('stories');
    }
};
