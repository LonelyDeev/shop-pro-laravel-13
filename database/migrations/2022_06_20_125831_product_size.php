<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_size', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_id')->constrained('sizes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('group');
            $table->string('value')->nullable();
            $table->integer('ordering')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی سایزهای یک محصول (پرکاربردترین)
            $table->index('product_id');

            // 2. ایندکس برای جستجوی محصولات دارای یک سایز خاص
            $table->index('size_id');

            // 3. ایندکس برای فیلتر گروه سایز
            $table->index('group');

            // 4. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 5. ایندکس ترکیبی برای سایزهای یک محصول با ترتیب گروه
            $table->index(['product_id', 'group', 'ordering']);

            // 6. ایندکس ترکیبی برای سایز خاص در یک محصول
            $table->index(['product_id', 'size_id']);

            // 7. ایندکس یکتا برای جلوگیری از تکرار
            $table->unique(['product_id', 'size_id', 'group']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_size');
    }
}
