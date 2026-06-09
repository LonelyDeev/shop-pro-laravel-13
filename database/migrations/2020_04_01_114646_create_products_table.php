<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('product_id')->unique();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('type')->nullable()->default('physical');

            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            $table->unsignedBigInteger('spec_type_id')->nullable();
            $table->foreign('spec_type_id')->references('id')->on('spec_types')->onDelete('set null');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');


            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('file')->nullable();
            $table->bigInteger('price')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('stock')->nullable();
            $table->boolean('special')->default(false);
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->unsignedBigInteger('sell')->default(0);
            $table->unsignedBigInteger('view')->default(0);
            $table->enum('shipping_nature',['small','medium','large'])->default('small')->nullable();
            $table->enum('status',['Accept','Reject','Waiting'])->default('Accept')->nullable();
            $table->text('more')->nullable();
            $table->timestamps();


            // 1. ایندکس برای فیلتر وضعیت محصول (تایید/رد/در انتظار)
            $table->index('status');

            // 2. ایندکس برای دسته‌بندی محصول
            $table->index('category_id');

            // 3. ایندکس برای فروشنده
            $table->index('seller_id');

            // 4. ایندکس برای نوع محصول (فیزیکی/مجازی)
            $table->index('type');

            // 5. ایندکس برای محصولات ویژه
            $table->index('special');

            // 6. ایندکس برای مرتب‌سازی بر اساس قیمت
            $table->index('price');

            // 7. ایندکس برای مرتب‌سازی بر اساس تخفیف
            $table->index('discount');

            // 8. ایندکس برای مرتب‌سازی بر اساس تعداد فروش
            $table->index('sell');

            // 9. ایندکس برای مرتب‌سازی بر اساس بازدید
            $table->index('view');

            // 10. ایندکس برای موجودی انبار
            $table->index('stock');

            // 11. ایندکس برای وزن محصول
            $table->index('weight');

            // 12. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');

            // 13. ایندکس ترکیبی برای محصولات یک دسته‌بندی + فعال
            $table->index(['category_id', 'status']);

            // 14. ایندکس ترکیبی برای محصولات یک فروشنده + فعال
            $table->index(['seller_id', 'status']);

            // 15. ایندکس ترکیبی برای محصولات ویژه + فعال
            $table->index(['special', 'status']);

            // 16. ایندکس ترکیبی برای محصولات یک دسته‌بندی + ویژه
            $table->index(['category_id', 'special']);

            // 17. ایندکس ترکیبی برای مرتب‌سازی بر اساس قیمت در یک دسته‌بندی
            $table->index(['category_id', 'price']);

            // 18. ایندکس ترکیبی برای مرتب‌سازی بر اساس فروش در یک دسته‌بندی
            $table->index(['category_id', 'sell']);

            // 19. ایندکس ترکیبی برای جستجوی عنوان (برای سرعت بخشی به LIKE)
            $table->index('title');

            // 20. ایندکس برای نوع حمل و نقل
            $table->index('shipping_nature');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
