<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('disk')->default('public')->after('size');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('size');

            // 1. ایندکس برای فیلتر وضعیت
            $table->index('status');

            // 2. ایندکس برای دیسک ذخیره‌سازی
            $table->index('disk');

            // 3. ایندکس برای جستجوی عنوان
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('disk');
            $table->dropColumn('status');
        });
    }
}
