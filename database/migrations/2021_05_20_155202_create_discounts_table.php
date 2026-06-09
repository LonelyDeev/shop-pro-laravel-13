<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('type', ['amount', 'percent']);
            $table->decimal('amount', 15);
            $table->integer('discount_ceiling')->nullable();
            $table->bigInteger('least_price')->nullable();
            $table->integer('least_products_count')->nullable();
            $table->text('description')->nullable();
            $table->boolean('only_first_purchase')->default(false);
            $table->boolean('not_discount_products')->default(false);
            $table->boolean('published')->default(true);
            $table->integer('quantity')->nullable();
            $table->integer('quantity_per_user')->nullable();

            $table->enum('include_type', ['all', 'category', 'product'])->default('all');
            $table->enum('exclude_type', ['none', 'category', 'product'])->default('none');
            $table->timestamps();

            // 1. ایندکس برای فیلتر وضعیت انتشار
            $table->index('published');

            // 2. ایندکس برای فیلتر نوع تخفیف (مبلغی/درصدی)
            $table->index('type');

            // 3. ایندکس برای تاریخ شروع
            $table->index('start_date');

            // 4. ایندکس برای تاریخ انقضا
            $table->index('end_date');

            // 5. ایندکس ترکیبی برای تخفیف‌های فعال (منتشر شده و تاریخ معتبر)
            $table->index(['published', 'start_date', 'end_date']);

            // 6. ایندکس ترکیبی برای تخفیف‌های ویژه اولین خرید
            $table->index(['only_first_purchase', 'published']);

            // 7. ایندکس برای کد تخفیف (برای اعتبارسنجی سریع)
            $table->index('code');

            // 8. ایندکس برای موجودی (تعداد)
            $table->index('quantity');

            // 9. ایندکس برای مرتب‌سازی بر اساس تاریخ ایجاد
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
        Schema::dropIfExists('discounts');
    }
}
