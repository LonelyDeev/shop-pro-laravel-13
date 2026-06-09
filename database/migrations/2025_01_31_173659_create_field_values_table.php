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
        Schema::create('field_values', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('field_id')->nullable();
            $table->foreign('field_id')->references('id')->on('filds')->onDelete('cascade');

            $table->enum('belongs_to', ['users', 'products', 'blogs'])->default('users')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();

            $table->text('value')->nullable();

            $table->timestamps();

            // 1. ایندکس برای جستجوی مقادیر یک فیلد خاص
            $table->index('field_id');

            // 2. ایندکس برای جستجوی نوع متعلقات (کاربران/محصولات/بلاگ‌ها)
            $table->index('belongs_to');

            // 3. ایندکس برای جستجوی آیتم مرتبط (کاربر/محصول/بلاگ)
            $table->index('related_id');

            // 4. ایندکس ترکیبی برای مقادیر یک آیتم خاص (پرکاربردترین)
            $table->index(['belongs_to', 'related_id']);

            // 5. ایندکس ترکیبی برای مقادیر یک فیلد خاص برای یک آیتم
            $table->index(['field_id', 'belongs_to', 'related_id']);

            // 6. ایندکس ترکیبی برای مقادیر یک نوع آیتم با فیلد خاص
            $table->index(['belongs_to', 'field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_values');
    }
};
