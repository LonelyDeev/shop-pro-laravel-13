<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('name');
            $table->string('mobile');

            $table->unsignedBigInteger('province_id');
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');

            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');

            $table->string('postal_code');
            $table->text('address');
            $table->string('location', 255)->nullable();
            $table->string('description', 1000)->nullable();

            $table->unsignedBigInteger('shipping_cost');
            $table->unsignedBigInteger('price');
            $table->string('status')->default('unpaid');
            $table->string('shipping_status')->default('w-pending');
            $table->string('tracking_code')->nullable();

            $table->timestamps();

            // ایندکس‌های مورد نیاز
            $table->index('user_id');
            $table->index('seller_id');
            $table->index('status');
            $table->index('shipping_status');
            $table->index('tracking_code');
            $table->index('created_at');
            $table->index('mobile');

            $table->index(['user_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['province_id', 'status'], 'province_status_idx');
            $table->index(['city_id', 'status'], 'city_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
