<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaggablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tag_id')->unsigned();
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->bigInteger('taggable_id');
            $table->string('taggable_type');


            // 1. ایندکس برای جستجوی تگ‌های یک آیتم (پست، محصول، و ...)
            $table->index(['taggable_id', 'taggable_type']);

            // 2. ایندکس برای جستجوی آیتم‌های یک تگ خاص (پرکاربردترین)
            $table->index(['tag_id', 'taggable_type']);

            // 3. ایندکس ترکیبی کامل (برای جستجوهای پیچیده)
            $table->index(['tag_id', 'taggable_id', 'taggable_type']);

            // 4. ایندکس برای جستجوی نوع taggable (مثلاً همه تگ‌های محصولات)
            $table->index('taggable_type');

            // 5. ایندکس برای جستجوی سریع tag_id
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taggables');
    }
}
