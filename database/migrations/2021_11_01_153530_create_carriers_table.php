<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarriersTable extends Migration
{
    public function up()
    {
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');

            $table->string('title');
            $table->string('image')->nullable();
            $table->string('waiting_time')->nullable();
            $table->bigInteger('max_package_weight')->nullable();
            $table->bigInteger('min_package_weight')->nullable();
            $table->bigInteger('extra_cost')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();

            $table->unsignedBigInteger('province_id');
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');

            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');

            $table->bigInteger('free_shipping_weight')->nullable();
            $table->bigInteger('free_shipping_price')->nullable();
            $table->boolean('carrige_forward')->default(false);

            $table->enum('covered_cities', ['all', 'select_city'])->default('all');

            $table->enum('delivery_time_type', ['default', 'user_select'])->default('default');
            $table->string('default_delivery_range')->nullable();
            $table->integer('user_select_ranges')->nullable()->default(7);
            $table->boolean('disable_holidays')->default(false);
            $table->boolean('disable_fridays')->default(false);
            $table->integer('start_days_after_order')->default(1);

            $table->softDeletes();
            $table->timestamps();


            // 1. ایندکس برای جستجوی فروشنده (مهمترین)
            $table->index('seller_id');

            // 2. ایندکس برای فیلتر وضعیت فعال
            $table->index('is_active');

            // 3. ایندکس برای جستجوی استان
            $table->index('province_id');

            // 4. ایندکس برای جستجوی شهر
            $table->index('city_id');

            // 5. ایندکس ترکیبی برای جستجوی استان + شهر (بهتر از دو ایندکس جدا)
            $table->index(['province_id', 'city_id']);

            // 6. ایندکس ترکیبی برای فروشنده + وضعیت فعال (برای صفحه فروشنده)
            $table->index(['seller_id', 'is_active']);

            // 7. ایندکس برای مرتب‌سازی بر اساس زمان
            $table->index('created_at');

            // 8. ایندکس برای حذف نرم (اگر زیاد از withTrashed استفاده می‌کنی)
            $table->index('deleted_at');

            // 9. ایندکس برای جستجوی عنوان (اگر نیاز به جستجو دارید)
            $table->index('title');

            // 10. ایندکس ترکیبی برای وزن (اگر مرتب‌سازی بر اساس وزن دارید)
            $table->index(['min_package_weight', 'max_package_weight']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('carriers');
    }
}
