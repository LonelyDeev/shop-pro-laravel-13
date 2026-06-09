<?php

namespace Database\Seeders;

use App\Models\StaticFilter;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // انبار اصلی فروشگاه
        Warehouse::create([
            'name' => 'انبار مرکزی',
            'address' => 'تهران، خیابان اصلی',
            'is_active' => true,
            'type' => 'main'
        ]);
    }
}
