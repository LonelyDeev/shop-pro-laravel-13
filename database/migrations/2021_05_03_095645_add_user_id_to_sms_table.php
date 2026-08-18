<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms', function (Blueprint $table) {
            if (!Schema::hasColumn('sms', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('type');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

                // 1. ایندکس برای جستجوی پیامک‌های یک کاربر
                $table->index('user_id');
            }
            if (!Schema::hasColumn('sms', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable()->after('user_id');
                $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');

                // 2. ایندکس برای جستجوی پیامک‌های یک فروشنده
                $table->index('seller_id');
            }
            if (!Schema::hasColumn('sms', 'response')) {
                $table->text('response')->nullable()->after('seller_id');
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
        Schema::table('sms', function (Blueprint $table) {
            $table->dropForeign('sms_user_id_foreign');
            $table->dropForeign('sms_seller_id_foreign');
            $table->dropColumn('user_id');
            $table->dropColumn('seller_id');
            $table->dropColumn('response');
        });
    }
}
