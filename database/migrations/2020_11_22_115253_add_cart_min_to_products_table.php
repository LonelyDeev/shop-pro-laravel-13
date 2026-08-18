<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCartMinToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'cart_min')) {
                $table->integer('cart_min')->nullable();
            }
        });

        Schema::table('prices', function (Blueprint $table) {
            if (!Schema::hasColumn('prices', 'cart_min')) {
                $table->integer('cart_min')->nullable();
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
            $table->dropColumn('cart_min');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('cart_min');
        });
    }
}
