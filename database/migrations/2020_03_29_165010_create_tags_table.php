<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('view_count')->default(0)->nullable();
            $table->timestamps();


            // 2. ایندکس برای جستجوی نام تگ
            $table->index('name');

            // 3. ایندکس برای مرتب‌سازی بر اساس پربازدیدترین
            $table->index('view_count');

            // 4. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');

            // 5. ایندکس ترکیبی برای نام + slug (جستجوی سریع)
            $table->index(['name', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
