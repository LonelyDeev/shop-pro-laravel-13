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
        Schema::table('prices', function (Blueprint $table) {
            if (!Schema::hasColumn('prices', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained('warehouses')->onDelete('set null');
                $table->index('warehouse_id');
            }

            // فیلدهای جدید برای انبارداری پیشرفته
            if (!Schema::hasColumn('prices', 'reserved_stock')) {
                $table->integer('reserved_stock')->default(0)->after('stock'); // موجودی رزرو شده برای سبد خرید
            }
            if (!Schema::hasColumn('prices', 'sold_count')) {
                $table->integer('sold_count')->default(0)->after('reserved_stock');
            }
            if (!Schema::hasColumn('prices', 'location_code')) {
                $table->string('location_code')->nullable()->after('sold_count'); // موقعیت در انبار (قفسه، ردیف)
            }
            if (!Schema::hasColumn('prices', 'last_stock_update')) {
                $table->timestamp('last_stock_update')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['warehouse_id', 'reserved_stock', 'sold_count', 'location_code', 'last_stock_update']);
        });
    }
};
