<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecTypeSpecificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spec_type_specification', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('specification_id');
            $table->foreign('specification_id')->references('id')->on('specifications')->onDelete('cascade');

            $table->unsignedBigInteger('specification_group_id');
            $table->foreign('specification_group_id')->references('id')->on('specification_groups')->onDelete('cascade');

            $table->unsignedBigInteger('spec_type_id');
            $table->foreign('spec_type_id')->references('id')->on('spec_types')->onDelete('cascade');

            $table->integer('group_ordering')->nullable();
            $table->integer('specification_ordering')->nullable();

            $table->timestamps();

            // 1. ایندکس برای جستجوی مشخصات یک نوع محصول
            $table->index('spec_type_id');

            // 2. ایندکس برای جستجوی گروه مشخصات
            $table->index('specification_group_id');

            // 3. ایندکس برای جستجوی خود مشخصات
            $table->index('specification_id');

            // 4. ایندکس ترکیبی برای مشخصات یک نوع محصول با ترتیب
            $table->index(['spec_type_id', 'group_ordering']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spec_type_specification');
    }
}
