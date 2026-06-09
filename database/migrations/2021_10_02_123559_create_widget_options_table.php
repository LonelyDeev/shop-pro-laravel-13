<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('widget_id');
            $table->foreign('widget_id')->references('id')->on('widgets')->onDelete('cascade');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            // 1. ایندکس برای جستجوی آپشن‌های یک ویجت (پرکاربردترین)
            $table->index('widget_id');

            // 2. ایندکس برای جستجوی کلید آپشن
            $table->index('key');

            // 3. ایندکس ترکیبی برای یک آپشن خاص یک ویجت
            $table->index(['widget_id', 'key']);

            // 4. ایندکس یکتا برای جلوگیری از تکرار کلید در یک ویجت
            $table->unique(['widget_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widget_options');
    }
}
