<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilterablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filterables', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('filter_id');
            $table->foreign('filter_id')->references('id')->on('filters')->onDelete('cascade');
            $table->bigInteger('filterable_id');
            $table->string('filterable_type');

            $table->integer('ordering')->nullable();
            $table->boolean('active');
            $table->timestamps();


            // 1. ایندکس برای جستجوی فیلترهای یک آیتم (محصول، مقاله، ...)
            $table->index(['filterable_id', 'filterable_type']);

            // 2. ایندکس برای جستجوی آیتم‌های یک فیلتر خاص
            $table->index('filter_id');

            // 3. ایندکس برای فیلتر وضعیت فعال
            $table->index('active');

            // 4. ایندکس ترکیبی برای فیلتر فعال یک آیتم
            $table->index(['filterable_id', 'filterable_type', 'active']);

            // 5. ایندکس ترکیبی برای فیلترهای فعال یک فیلتر خاص
            $table->index(['filter_id', 'active']);

            // 6. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('filterables');
    }
}
