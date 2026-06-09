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
        Schema::create('admin_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('session_id');
            $table->string('device_fingerprint');
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // ایندکس ترکیبی برای جستجوی سریع (قبلاً وجود دارد)
            $table->unique(['admin_id', 'device_fingerprint'], 'admin_device_unique');

            // 1. ایندکس برای جستجوی session_id
            $table->index('session_id');

            // 2. ایندکس برای مرتب‌سازی بر اساس آخرین فعالیت
            $table->index('last_activity');

            // 3. ایندکس برای فیلتر نشست‌های فعال
            $table->index('is_active');

            // 4. ایندکس برای جستجوی آی پی
            $table->index('ip_address');

            // 5. ایندکس برای فیلتر نوع دستگاه
            $table->index('device_type');

            // 6. ایندکس ترکیبی برای نشست‌های فعال یک ادمین
            $table->index(['admin_id', 'is_active']);

            // 7. ایندکس ترکیبی برای نشست‌های فعال و آخرین فعالیت
            $table->index(['is_active', 'last_activity']);

            // 8. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_sessions');
    }
};
