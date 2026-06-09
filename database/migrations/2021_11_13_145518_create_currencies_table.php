<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 64, 8);
            $table->softDeletes();
            $table->timestamps();

            // 1. ایندکس برای جستجوی عنوان ارز
            $table->index('title');

            // 2. ایندکس برای مرتب‌سازی بر اساس نرخ ارز
            $table->index('amount');

            // 3. ایندکس برای مرتب‌سازی بر اساس تاریخ
            $table->index('created_at');

            // 4. ایندکس برای حذف نرم
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
