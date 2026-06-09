<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('price');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->integer('discount')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('cart_max')->nullable();
            $table->boolean('published')->default(false)->nullable();
            $table->string('attribute_hash', 32)->nullable();
            $table->timestamps();


            // 1. ایندکس برای جستجوی قیمت‌های یک محصول (پرکاربردترین)
            $table->index('product_id');

            // 2. ایندکس برای جستجوی قیمت‌های یک فروشنده
            $table->index('seller_id');

            // 3. ایندکس برای فیلتر وضعیت انتشار
            $table->index('published');

            // 4. ایندکس برای مرتب‌سازی بر اساس قیمت
            $table->index('price');

            // 5. ایندکس برای مرتب‌سازی بر اساس تخفیف
            $table->index('discount');

            // 6. ایندکس برای فیلتر موجودی
            $table->index('stock');

            // 7. ایندکس ترکیبی برای قیمت منتشر شده یک محصول
            $table->index(['product_id', 'published']);

            // 8. ایندکس ترکیبی برای قیمت منتشر شده یک فروشنده
            $table->index(['seller_id', 'published']);

            // 9. ایندکس ترکیبی برای قیمت با تخفیف
            $table->index(['published', 'discount']);

            // 10. ایندکس ترکیبی برای مرتب‌سازی بر اساس قیمت در یک محصول
            $table->index(['product_id', 'price']);

            // 11. ایندکس برای مرتب‌سازی بر اساس زمان
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
        Schema::dropIfExists('prices');
    }
}
