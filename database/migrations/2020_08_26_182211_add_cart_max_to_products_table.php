<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCartMaxToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'cart_max')) {
                $table->integer('cart_max')->nullable();
            }
            if (!Schema::hasColumn('products', 'price_type')) {
                $table->string('price_type')->default('multiple-price');

                $table->index('price_type');
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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cart_max');
            $table->dropColumn('price_type');
        });
    }
}
