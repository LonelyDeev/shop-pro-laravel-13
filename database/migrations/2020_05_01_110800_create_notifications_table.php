<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();


            // 1. ایندکس برای فیلتر نوع اعلان
            $table->index('type');

            // 2. ایندکس برای فیلتر اعلان‌های خوانده/نخوانده
            $table->index('read_at');

            // 3. ایندکس ترکیبی برای اعلان‌های خوانده نشده یک نوع خاص
            $table->index(['type', 'read_at']);

            // 4. ایندکس برای مرتب‌سازی بر اساس زمان ایجاد
            $table->index('created_at');

            // توجه: notifiable_id و notifiable_type توسط morphs() ایندکس می‌شوند
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
