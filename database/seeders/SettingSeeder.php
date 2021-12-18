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

        Setting::create([
            'name'=>'peykCost',
            'value'=>'25'
        ]);

        Setting::create([
            'name'=>'postCost',
            'value'=>'20'
        ]);

        Setting::create([
            'name'=>'freeDelivery',
            'value'=>'500'
        ]);


    }
}
