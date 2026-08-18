<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('meta_title')->nullable();
            $table->text('meta_description', 500)->nullable();
            $table->boolean('published')->default(false);
            $table->string('image_alt')->nullable();


            // 1. ایندکس برای فیلتر وضعیت انتشار
            $table->index('published');

            // 2. ایندکس ترکیبی با وضعیت انتشار و دسته‌بندی (برای صفحه محصولات)
            $table->index(['published', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('meta_title');
            $table->dropColumn('meta_description');
            $table->dropColumn('published');
            $table->dropColumn('image_alt');
        });
    }
}
