<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSellerEcontractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_econtracts', function (Blueprint $table) {
            $table->id();
            $table->longText('header')->nullable();
            $table->longText('content');
            $table->timestamps();
        });
        DB::table('seller_econtracts')->insert(
            array(
                'header' => null,
                'content' => 'متن قرار داد'
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_econtracts');
    }
}
