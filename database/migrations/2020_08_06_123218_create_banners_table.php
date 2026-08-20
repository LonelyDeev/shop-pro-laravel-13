<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->json('pages')->nullable();
            $table->json('groups')->nullable();
            $table->json('places')->nullable();

            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->boolean('published')->default(true);
            $table->string('link')->nullable();
            $table->integer('ordering')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('published');
            $table->index('ordering');
            $table->index('created_at');
            $table->index(['published', 'ordering']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banners');
    }
}
