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
            if (!Schema::hasColumn('files', 'title')) {
                $table->string('title')->nullable()->after('id');

                // 3. ایندکس برای جستجوی عنوان
                $table->index('title');
            }
            if (!Schema::hasColumn('files', 'disk')) {
                $table->string('disk')->default('public')->after('size');

                // 2. ایندکس برای دیسک ذخیره‌سازی
                $table->index('disk');
            }
            if (!Schema::hasColumn('files', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('size');

                // 1. ایندکس برای فیلتر وضعیت
                $table->index('status');
            }
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
