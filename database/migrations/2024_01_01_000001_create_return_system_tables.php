<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ۱. جدول دلایل مرجوعی (قابل مدیریت از پنل ادمین)
        Schema::create('return_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('ordering')->default(0);
            $table->timestamps();
        });

        // ۲. جدول درخواست‌های مرجوعی
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('return_reason_id')->nullable()->constrained('return_reasons')->nullOnDelete();
            $table->foreignId('seller_id')->nullable();
            $table->foreignId('admin_id')->nullable(); // ادمینی که بررسی کرده

            // وضعیت: pending, approved, received, completed, rejected, cancelled
            $table->enum('status', ['pending', 'approved', 'received', 'completed', 'rejected', 'cancelled'])->default('pending');

            // اطلاعات درخواست
            $table->text('description')->nullable(); // توضیحات کاربر
            $table->decimal('refund_amount', 20, 0)->default(0); // مبلغ برگشتی
            $table->boolean('refund_to_wallet')->default(false); // آیا به کیف پول برگشت داده شد

            // یادداشت‌های ادمین
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // زمان‌بندی
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        // ۳. جدول تصاویر مرجوعی
        Schema::create('return_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        // ۴. افزودن وضعیت مرجوعی به order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('return_status', ['none', 'pending', 'approved', 'received', 'completed', 'rejected', 'cancelled'])
                  ->default('none')
                  ->after('refunded');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('return_status');
        });
        Schema::dropIfExists('return_images');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('return_reasons');
    }
};
