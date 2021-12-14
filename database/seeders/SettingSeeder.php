<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'name'=>'minCoupon',
            'value'=>'10'
        ]);

        Setting::create([
            'name'=>'loadOrders',
            'value'=>'400'
        ]);

        Setting::create([
            'name'=>'negative',
            'value'=>'500'
        ]);
    }
}
