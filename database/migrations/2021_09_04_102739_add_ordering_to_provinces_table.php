<?php

use App\Models\Province;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderingToProvincesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provinces', function (Blueprint $table) {
            if (!Schema::hasColumn('provinces', 'ordering')) {
                $table->integer('ordering')->nullable();

                $table->index('ordering');
            }
            if (!Schema::hasColumn('provinces', 'is_active')) {
                $table->boolean('is_active')->default(true);

                $table->index('is_active');
            }
            if (!Schema::hasColumn('provinces', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (Schema::hasColumn('provinces', 'ordering')) {
            $ordering = 1;

            $provinces = Province::all();

            foreach ($provinces as $province) {
                $province->update([
                    'ordering' => $ordering++
                ]);
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
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn('ordering');
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });
    }
}
