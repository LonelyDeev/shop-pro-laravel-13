<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('price_id')->nullable()->constrained('prices')->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('set null');

            $table->enum('type', ['in', 'out', 'reserve', 'unreserve', 'adjustment']);
            $table->integer('quantity');
            $table->integer('before_stock');
            $table->integer('after_stock');
            $table->string('reference')->nullable(); // order_id, purchase_id, etc.
            $table->text('description')->nullable();
            $table->json('attributes')->nullable();
            $table->string('operator_type')->nullable(); // admin, seller, system
            $table->unsignedBigInteger('operator_id')->nullable();

            $table->timestamps();

            $table->index(['product_id','price_id', 'warehouse_id']);
            $table->index('created_at');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
