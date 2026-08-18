<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers_info', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('seller_id')->unique()->nullable();
            $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('cascade');

            $table->enum('private_business',['private','business'])->default('private')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('birth_day')->nullable();
            $table->enum('gender',['male','female'])->default('male')->nullable();
            $table->integer('identity_card_number')->nullable();
            $table->string('national_identity_number',11)->nullable();

            $table->string('company_name')->nullable();
            $table->string('company_type')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->string('company_national_identity_number',11)->nullable();
            $table->string('company_economic_number',12)->nullable();

            $table->string('seller_contract_number')->unique()->nullable();
            $table->string('contract_start_date')->nullable();
            $table->string('contract_end_date')->nullable();

            $table->integer('state_id');
            $table->integer('city_id');
            $table->text('address');
            $table->string('post_code',10);
            $table->string('location')->nullable();
            $table->string('phone',11);
            $table->string('mobile',11);
            $table->string('business_name');
            $table->string('shaba_number');
            $table->bigInteger('main_supply_category_id');
            $table->integer('number_of_products');
            $table->integer('vat_free')->nullable();
            $table->text('vat_image')->nullable();
            $table->text('card_image')->nullable();
            $table->text('card_image_back')->nullable();
            $table->string('website')->nullable();
            $table->text('logo')->nullable();
            $table->text('bio')->nullable();
            $table->string('operation')->nullable();
            $table->integer('satisfaction')->nullable();
            $table->tinyInteger('econtract')->default('0');
            $table->enum('status_documents',['Accept','Reject','Waiting'])->default('Waiting')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی اطلاعات یک فروشنده
            $table->index('seller_id');

            // 2. ایندکس برای جستجوی استان
            $table->index('state_id');

            // 3. ایندکس برای جستجوی شهر
            $table->index('city_id');

            // 4. ایندکس برای جستجوی کد پستی
            $table->index('post_code');

            // 5. ایندکس برای جستجوی موبایل
            $table->index('mobile');

            // 6. ایندکس برای جستجوی تلفن
            $table->index('phone');

            // 7. ایندکس برای جستجوی نام کسب و کار
            $table->index('business_name');

            // 8. ایندکس برای وضعیت مدارک
            $table->index('status_documents');

            // 9. ایندکس ترکیبی برای استان و شهر
            $table->index(['state_id', 'city_id']);

            // 10. ایندکس برای شماره شبا
            $table->index('shaba_number');

            // 11. ایندکس برای شماره ملی
            $table->index('national_identity_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sellers_info');
    }
}
