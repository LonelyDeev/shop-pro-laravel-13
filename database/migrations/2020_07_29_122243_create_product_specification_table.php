<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSpecificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_specification', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('specification_id');
            $table->foreign('specification_id')->references('id')->on('specifications')->onDelete('cascade');

            $table->unsignedBigInteger('specification_group_id');
            $table->foreign('specification_group_id')->references('id')->on('specification_groups')->onDelete('cascade');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->integer('group_ordering')->nullable();
            $table->integer('specification_ordering')->nullable();
            $table->boolean('special')->default(false);

            $table->text('value')->nullable();

            $table->timestamps();


            // 1. ایندکس برای جستجوی مشخصات یک محصول (پرکاربردترین)
            $table->index('product_id');

            // 2. ایندکس برای جستجوی گروه مشخصات
            $table->index('specification_group_id');

            // 3. ایندکس برای جستجوی خود مشخصات
            $table->index('specification_id');

            // 4. ایندکس برای فیلتر مشخصات ویژه
            $table->index('special');

            // 5. ایندکس ترکیبی برای مشخصات ویژه یک محصول
            $table->index(['product_id', 'special']);

            // 6. ایندکس ترکیبی برای مشخصات یک محصول با ترتیب گروه
            $table->index(['product_id', 'group_ordering']);

            // 7. ایندکس ترکیبی برای مشخصات یک گروه در یک محصول
            $table->index(['product_id', 'specification_group_id']);

            // 8. ایندکس برای مرتب‌سازی بر اساس زمان
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
        Schema::dropIfExists('product_specification');
    }
}
