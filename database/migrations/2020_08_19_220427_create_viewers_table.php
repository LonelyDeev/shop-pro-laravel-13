<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viewers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip')->nullable();
            $table->text('page_path')->nullable();
            $table->text('product_path')->nullable();
            $table->text('path')->nullable();
            $table->boolean('auth')->default(false);
            $table->text('options')->nullable();
            $table->tinyInteger('status')->nullable()->default('1');
            $table->timestamps();


            // 1. ایندکس برای جستجوی بازدیدهای یک آی پی
            $table->index('ip');

            // 2. ایندکس برای فیلتر وضعیت احراز هویت
            $table->index('auth');

            // 3. ایندکس برای فیلتر وضعیت
            $table->index('status');

            // 4. ایندکس برای مرتب‌سازی بر اساس زمان
            $table->index('created_at');

            // 5. ایندکس ترکیبی برای بازدیدهای یک آی پی با وضعیت خاص
            $table->index(['ip', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viewers');
    }
}
