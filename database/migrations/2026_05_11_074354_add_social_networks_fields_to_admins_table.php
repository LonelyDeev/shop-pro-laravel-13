<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('instagram')->nullable()->after('status');
            $table->string('whatsapp')->nullable()->after('instagram');
            $table->string('eitaa')->nullable()->after('whatsapp');
            $table->string('telegram')->nullable()->after('eitaa');
            $table->string('twitter')->nullable()->after('telegram');
            $table->string('facebook')->nullable()->after('twitter');
            $table->string('rubika')->nullable()->after('facebook');
            $table->string('bale')->nullable()->after('rubika');

            $table->index('instagram');
            $table->index('telegram');
            $table->index('whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'instagram',
                'whatsapp',
                'eitaa',
                'telegram',
                'twitter',
                'facebook',
                'rubika',
                'bale'
            ]);
        });
    }
};
