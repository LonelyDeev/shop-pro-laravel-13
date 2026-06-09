<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('mobile',11)->unique();
            $table->string('password');
            $table->enum('mobile_verification',['YES','NO'])->default('NO')->nullable();
            $table->integer('code_mobile_verification')->nullable();
            $table->string('remember_token')->nullable();
            $table->enum('status',['ACTIVE','INACTIVE'])->default('ACTIVE')->nullable();
            $table->enum('status_register',['business-details','documents','complete'])->default('business-details')->nullable();
            $table->enum('status_documents',['Accept','Reject','Waiting'])->default('Waiting')->nullable();
            $table->enum('status_work',['ACTIVE','Stop','EditProfile'])->default('ACTIVE')->nullable();
            $table->timestamps();


            // 1. ایندکس برای فیلتر وضعیت فروشنده (فعال/غیرفعال)
            $table->index('status');

            // 2. ایندکس برای فیلتر وضعیت کاری (فعال/توقف/در حال ویرایش)
            $table->index('status_work');

            // 3. ایندکس برای فیلتر وضعیت مدارک (تایید/رد/در انتظار)
            $table->index('status_documents');

            // 4. ایندکس برای فیلتر وضعیت ثبت‌نام
            $table->index('status_register');

            // 5. ایندکس برای تایید موبایل
            $table->index('mobile_verification');

            // 6. ایندکس ترکیبی برای وضعیت + وضعیت کاری (فروشندگان فعالی که در حال کار هستند)
            $table->index(['status', 'status_work']);

            // 7. ایندکس ترکیبی برای وضعیت + وضعیت مدارک (فروشندگان منتظر تایید مدارک)
            $table->index(['status', 'status_documents']);

            // 8. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');

            // 9. ایندکس برای کد تایید موبایل (اگر در جستجو استفاده می‌شود)
            $table->index('code_mobile_verification');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sellers');
    }
}
