<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_options', function (Blueprint $table) {
            $table->id();
            $table->string('option_name');
            $table->text('option_value')->nullable();

            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');

            $table->unique(['option_name', 'admin_id']);

            // 1. ایندکس برای جستجوی آپشن‌های یک ادمین
            $table->index('admin_id');

            // 2. ایندکس برای جستجوی نام آپشن
            $table->index('option_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_options');
    }
}
