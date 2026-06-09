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
        Schema::create('blocked_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('device_fingerprint')->nullable(); // اثر انگشت دستگاه
            $table->string('browser_fingerprint')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('reason')->nullable();
            $table->enum('block_type', ['session', 'ip', 'device', 'browser', 'all'])->default('device');
            $table->timestamp('blocked_until')->nullable(); // برای بلاک موقت
            $table->boolean('is_permanent')->default(false);
            $table->timestamps();

            // 1. ایندکس برای جستجوی بلاک‌های یک ادمین
            $table->index('admin_id');

            // 2. ایندکس برای جستجوی session_id
            $table->index('session_id');

            // 3. ایندکس برای جستجوی آی پی
            $table->index('ip_address');

            // 4. ایندکس برای جستجوی اثر انگشت دستگاه
            $table->index('device_fingerprint');

            // 5. ایندکس برای جستجوی اثر انگشت مرورگر
            $table->index('browser_fingerprint');

            // 6. ایندکس برای فیلتر نوع بلاک
            $table->index('block_type');

            // 7. ایندکس برای فیلتر بلاک دائمی/موقت
            $table->index('is_permanent');

            // 8. ایندکس برای تاریخ انقضای بلاک
            $table->index('blocked_until');

            // 9. ایندکس ترکیبی برای بلاک‌های فعال (دائمی یا تاریخ انقضا معتبر)
            $table->index(['admin_id', 'is_permanent', 'blocked_until']);

            // 10. ایندکس ترکیبی برای آی پی و نوع بلاک
            $table->index(['ip_address', 'block_type']);

            // 11. ایندکس ترکیبی برای session و نوع بلاک
            $table->index(['session_id', 'block_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_devices');
    }
};
