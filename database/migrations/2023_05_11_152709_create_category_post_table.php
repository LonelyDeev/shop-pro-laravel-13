<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCategoryPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_post', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            // 1. ایندکس یکتا برای جلوگیری از تکرار
            $table->unique(['post_id', 'category_id']);

            // 2. ایندکس برای جستجوی دسته‌بندی‌های یک پست
            $table->index('post_id');

            // 3. ایندکس برای جستجوی پست‌های یک دسته‌بندی
            $table->index('category_id');
        });

        $posts = DB::table('posts')->whereNotNull('category_id')->select(['id', 'category_id'])->get();

        foreach ($posts as $post) {
            // جلوگیری از درج تکراری
            $exists = DB::table('category_post')
                ->where('post_id', $post->id)
                ->where('category_id', $post->category_id)
                ->exists();

            if (!$exists) {
                DB::table('category_post')->insert([
                    'post_id'  => $post->id,
                    'category_id' => $post->category_id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_post');
    }
}
