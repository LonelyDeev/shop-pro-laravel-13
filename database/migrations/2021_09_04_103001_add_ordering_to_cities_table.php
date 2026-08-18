<?php

use App\Models\Province;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderingToCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            if (!Schema::hasColumn('cities', 'ordering')) {
                $table->integer('ordering')->nullable();
            }
            if (!Schema::hasColumn('cities', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('cities', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (Schema::hasColumn('cities', 'ordering')) {
            foreach (Province::all() as $province) {
                $ordering = 1;

                foreach ($province->cities as $city) {
                    $city->update([
                        'ordering' => $ordering++
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('ordering');
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });
    }
}
