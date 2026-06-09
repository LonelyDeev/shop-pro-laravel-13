<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_points', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->enum('type', ['positive', 'negative']);

            $table->unsignedBigInteger('review_id');
            $table->foreign('review_id')->references('id')->on('reviews')->onDelete('cascade');

            $table->timestamps();

            // 1. ایندکس برای جستجوی نکات یک نظر
            $table->index('review_id');

            // 2. ایندکس برای فیلتر نوع نکات (مثبت/منفی)
            $table->index('type');

            // 3. ایندکس ترکیبی برای نکات مثبت یک نظر
            $table->index(['review_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_points');
    }
}
