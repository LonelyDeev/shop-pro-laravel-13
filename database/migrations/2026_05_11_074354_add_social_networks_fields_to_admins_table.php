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
            if (!Schema::hasColumn('admins', 'instagram')) {
                $table->string('instagram')->nullable()->after('status');

                $table->index('instagram');
            }
            if (!Schema::hasColumn('admins', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('instagram');

                $table->index('whatsapp');
            }
            if (!Schema::hasColumn('admins', 'eitaa')) {
                $table->string('eitaa')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('admins', 'telegram')) {
                $table->string('telegram')->nullable()->after('eitaa');

                $table->index('telegram');
            }
            if (!Schema::hasColumn('admins', 'twitter')) {
                $table->string('twitter')->nullable()->after('telegram');
            }
            if (!Schema::hasColumn('admins', 'facebook')) {
                $table->string('facebook')->nullable()->after('twitter');
            }
            if (!Schema::hasColumn('admins', 'rubika')) {
                $table->string('rubika')->nullable()->after('facebook');
            }
            if (!Schema::hasColumn('admins', 'bale')) {
                $table->string('bale')->nullable()->after('rubika');
            }
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
