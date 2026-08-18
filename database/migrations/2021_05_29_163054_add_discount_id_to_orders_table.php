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
            if (!Schema::hasColumn('orders', 'discount_id')) {
                $table->unsignedBigInteger('discount_id')->after('status')->nullable();
                $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('set null');

                // ایندکس برای جستجوی سفارشات دارای یک تخفیف خاص
                $table->index('discount_id');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->unsignedBigInteger('discount_amount')->after('discount_id')->nullable();

                // ایندکس برای فیلتر سفارشات با تخفیف
                $table->index('discount_amount');
            }
            if (!Schema::hasColumn('orders', 'discount_percent')) {
                $table->unsignedBigInteger('discount_percent')->after('discount_amount')->nullable();
            }
            if (!Schema::hasColumn('orders', 'discount_price')) {
                $table->unsignedBigInteger('discount_price')->after('discount_percent')->nullable();
            }

            // ایندکس ترکیبی باید بعد از ایجاد هر دو ستون ساخته شود
            if (Schema::hasColumn('orders', 'discount_id') && Schema::hasColumn('orders', 'discount_amount')) {
                $table->index(['discount_id', 'discount_amount']);
            }
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
