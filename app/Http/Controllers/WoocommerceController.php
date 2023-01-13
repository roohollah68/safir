<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Automattic\WooCommerce\Client;

class WoocommerceController extends Controller
{
    public function addPeptinaOrder()
    {
        $woocommerce = new Client('https://peptina.com',
            'ck_b434203ee938bfbaa214a8ba4dfe772b9d164971',
            'cs_acc8f1e49db9e14688bfd60e731239fdcf521f69',
            [
                'version' => 'wc/v3',
            ]);
        $result = $woocommerce->get('https://peptina.com/wp-json/wc/v3/orders', []);
        dd($result);
    }
}
