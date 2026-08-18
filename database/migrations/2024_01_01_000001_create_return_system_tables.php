<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ۱. دلایل مرجوعی
        Schema::create('return_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('ordering')->default(0);
            $table->timestamps();
        });

        // ۲. درخواست‌های مرجوعی
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('return_reason_id')->nullable()->constrained('return_reasons')->nullOnDelete();
            $table->foreignId('seller_id')->nullable();
            $table->foreignId('admin_id')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'shipped_by_customer',
                'received',
                'reshipped',
                'completed',
                'rejected',
                'cancelled',
                'failed',
            ])->default('pending');

            $table->text('description')->nullable();

            $table->decimal('item_price', 20, 0)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('total_item_amount', 20, 0)->default(0);
            $table->decimal('discount_amount', 20, 0)->default(0);
            $table->decimal('refund_amount', 20, 0)->default(0);

            $table->string('payment_type', 32)->default('cash')->index();
            $table->decimal('wallet_refund_amount', 20, 0)->default(0);
            $table->decimal('credit_restore_amount', 20, 0)->default(0);
            $table->boolean('paid_to_wallet')->default(false);
            $table->boolean('credit_restored')->default(false);

            $table->boolean('refund_to_wallet')->default(false);
            $table->boolean('reship_product')->default(false);

            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('inspection_result')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('customer_shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('reshipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        // ۳. تصاویر مرجوعی
        Schema::create('return_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        // ۴. افزودن return_status به order_items (با بررسی وجود ستون)
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'return_status')) {
                $table->enum('return_status', [
                    'none',
                    'pending',
                    'approved',
                    'shipped_by_customer',
                    'received',
                    'reshipped',
                    'completed',
                    'rejected',
                    'cancelled',
                    'failed',
                ])->default('none')->after('refunded');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'return_status')) {
                $table->dropColumn('return_status');
            }
        });
        Schema::dropIfExists('return_images');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('return_reasons');
    }
};
