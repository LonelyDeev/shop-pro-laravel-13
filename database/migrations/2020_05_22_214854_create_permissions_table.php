<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();

            $table->unsignedBigInteger('permission_id')->nullable();
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->integer('ordering')->nullable();
            $table->boolean('active')->default(true);

            $table->string('title')->nullable();


            // 1. ایندکس برای جستجوی دسترسی والد (زیردسته‌ها)
            $table->index('permission_id');

            // 2. ایندکس برای فیلتر وضعیت فعال
            $table->index('active');

            // 3. ایندکس برای مرتب‌سازی بر اساس ترتیب
            $table->index('ordering');

            // 4. ایندکس برای جستجوی عنوان
            $table->index('title');

            // 5. ایندکس ترکیبی برای دسترسی‌های فعال با ترتیب
            $table->index(['active', 'ordering']);

            // 6. ایندکس ترکیبی برای دسترسی‌های فعال یک والد
            $table->index(['permission_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
