<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_id')->after('status')->nullable();
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('set null');
            $table->unsignedBigInteger('discount_amount')->after('discount_id')->nullable();
            $table->unsignedBigInteger('discount_percent')->after('discount_amount')->nullable();
            $table->unsignedBigInteger('discount_price')->after('discount_percent')->nullable();

            // 1. ایندکس برای جستجوی سفارشات دارای یک تخفیف خاص
            $table->index('discount_id');

            // 2. ایندکس برای فیلتر سفارشات با تخفیف
            $table->index('discount_amount');

            // 3. ایندکس ترکیبی برای سفارشات با تخفیف مبلغی
            $table->index(['discount_id', 'discount_amount']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_discount_id_foreign');
            $table->dropColumn('discount_id');
            $table->dropColumn('discount_amount');
            $table->dropColumn('discount_percent');
            $table->dropColumn('discount_price');
        });
    }
}
