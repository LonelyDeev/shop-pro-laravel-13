<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToViewersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('viewers', function (Blueprint $table) {
            if (!Schema::hasColumn('viewers', 'admin_id')) {
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
                $table->index('admin_id');
            }

            if (!Schema::hasColumn('viewers', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->index('user_id');
            }

            if (!Schema::hasColumn('viewers', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable();
                $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');
                $table->index('seller_id');
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
        Schema::table('viewers', function (Blueprint $table) {
            $table->dropForeign('viewers_admin_id_foreign');
            $table->dropForeign('viewers_user_id_foreign');
            $table->dropForeign('viewers_seller_id_foreign');
            $table->dropColumn('admin_id');
            $table->dropColumn('user_id');
            $table->dropColumn('seller_id');
        });
    }
}
