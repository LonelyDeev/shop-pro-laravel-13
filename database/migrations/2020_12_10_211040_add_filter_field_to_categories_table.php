<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilterFieldToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'filter_type')) {
                $table->enum('filter_type', ['inherit', 'none', 'filterId'])->default('inherit');

                // 1. ایندکس برای فیلتر نوع
                $table->index('filter_type');
            }
            if (!Schema::hasColumn('categories', 'filter_id')) {
                $table->unsignedBigInteger('filter_id')->nullable();
                $table->foreign('filter_id')->references('id')->on('filters')->onDelete('set null');

                // 2. ایندکس برای فیلتر خارجی
                $table->index('filter_id');
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
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign('categories_filter_id_foreign');
            $table->dropColumn('filter_id');
            $table->dropColumn('filter_type');
        });
    }
}
