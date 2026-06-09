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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // کد خودکار مثل WH-0001
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('manager_name')->nullable();

            // ارتباط با فروشنده (nullable برای انبار اصلی فروشگاه)
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('cascade');

            // وضعیت
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['main', 'seller', 'temp'])->default('main');

            // اطلاعات اضافی
            $table->string('province_id')->nullable();
            $table->string('city_id')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('seller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
