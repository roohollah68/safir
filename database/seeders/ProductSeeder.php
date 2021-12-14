<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'name'=>'ماچا',
            'price'=>'100',
            'available'=>true,
            'photo' => 'wf52FOD7MCRQHRTmVPmIKinHGIp3Sp8p6kQaxFJh.png'
        ]);
    }
}
