<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image');
            $table->integer('galleryable_id');
            $table->string('galleryable_type');
            $table->timestamps();


            // 1. ایندکس ترکیبی برای جستجوی گالری یک آیتم (پرکاربردترین)
            $table->index(['galleryable_id', 'galleryable_type']);

            // 2. ایندکس برای جستجوی نوع gallerable
            $table->index('galleryable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('galleries');
    }
}
