<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeightAndReserveToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'weight')) {
                $table->integer('weight')->nullable()->after('shipping_status');

                // 2. ایندکس برای محدوده وزنی سفارش‌ها
                $table->index('weight');
            }
            if (!Schema::hasColumn('orders', 'reserve')) {
                $table->boolean('reserve')->default(false)->after('weight');


                // 1. ایندکس برای جستجوی سفارش‌های رزرو شده
                $table->index('reserve');
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
            $table->dropColumn('weight');
            $table->dropColumn('reserve');
        });
    }
}
