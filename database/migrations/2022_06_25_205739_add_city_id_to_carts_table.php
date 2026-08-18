<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCityIdToCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'city_id')) {
                $table->unsignedBigInteger('city_id')->after('user_id')->nullable();
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');

                // 1. ایندکس برای جستجوی سبدهای خرید یک شهر
                $table->index('city_id');
            }
            if (!Schema::hasColumn('carts', 'carrier_id')) {
                $table->unsignedBigInteger('carrier_id')->after('city_id')->nullable();
                $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('set null');

                // 2. ایندکس برای جستجوی سبدهای خرید با روش ارسال خاص
                $table->index('carrier_id');
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
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign('carts_city_id_foreign');
            $table->dropColumn('city_id');

            $table->dropForeign('carts_carrier_id_foreign');
            $table->dropColumn('carrier_id');
        });
    }
}
