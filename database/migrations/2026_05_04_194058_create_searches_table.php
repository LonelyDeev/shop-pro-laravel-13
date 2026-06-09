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
        Schema::create('searches', function (Blueprint $table) {
            $table->id();
            // اطلاعات کاربر
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // اطلاعات جستجو
            $table->string('keyword'); // کلمه جستجو شده
            $table->enum('search_type', ['products', 'posts']); // نوع جستجو: products یا posts
            $table->json('filters')->nullable(); // فیلترهای اعمال شده

            // نتایج جستجو
            $table->integer('products_count')->default(0); // تعداد محصولات پیدا شده
            $table->integer('categories_count')->default(0); // تعداد دسته‌بندی‌ها
            $table->integer('brands_count')->default(0); // تعداد برندها
            $table->integer('posts_count')->default(0); // تعداد پست‌ها
            $table->json('result_ids')->nullable(); // آیدی نتایج

            // اطلاعات بیشتر
            $table->boolean('has_brand')->default(false); // آیا برندی پیدا شد
            $table->boolean('is_ajax')->default(false); // آیا جستجو از طریق ajax بوده

            // زمان
            $table->timestamp('searched_at')->useCurrent();
            $table->timestamps();


            // 1. ایندکس برای جستجوی کلمه کلیدی بر اساس نوع (پرکاربردترین)
            $table->index(['keyword', 'search_type']);

            // 2. ایندکس برای جستجوی کاربر
            $table->index('user_id');

            // 3. ایندکس برای جستجوی آی پی
            $table->index('ip_address');

            // 4. ایندکس برای مرتب‌سازی بر اساس زمان جستجو
            $table->index('searched_at');

            // 5. ایندکس برای فیلتر نوع جستجو (محصولات یا پست‌ها)
            $table->index('search_type');

            // 6. ایندکس ترکیبی برای کاربر و نوع جستجو
            $table->index(['user_id', 'search_type']);

            // 7. ایندکس ترکیبی برای آی پی و نوع جستجو
            $table->index(['ip_address', 'search_type']);

            // 8. ایندکس برای کلمه کلیدی (برای جستجوی سریع)
            $table->index('keyword');

            // 9. ایندکس برای تعداد محصولات یافت شده
            $table->index('products_count');

            // 10. ایندکس برای جستجوهای ajax
            $table->index('is_ajax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('searches');
    }
};
