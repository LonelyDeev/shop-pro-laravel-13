<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->string('column_class')->nullable()->after('order'); // کلاس ستون (col-md-6, col-12 و...)
            $table->boolean('show_label')->default(true)->after('column_class'); // نمایش یا عدم نمایش لیبل
            $table->string('label_class')->nullable()->after('show_label'); // کلاس لیبل
            $table->string('wrapper_class')->nullable()->after('label_class'); // کلاس والد فیلد

            // 1. ایندکس برای فیلتر نمایش لیبل
            $table->index('show_label');

            // 2. ایندکس برای جستجوی کلاس ستون
            $table->index('column_class');
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn(['column_class', 'show_label', 'label_class', 'wrapper_class']);
        });
    }
};
