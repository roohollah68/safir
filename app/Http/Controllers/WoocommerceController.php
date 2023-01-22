<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Automattic\WooCommerce\Client;

class WoocommerceController extends Controller
{
    public function addPeptinaOrder($website)
    {

        $request = json_decode(file_get_contents('php://input'));
//        $woocommerce = new Client('https://peptina.com',
//            'ck_b434203ee938bfbaa214a8ba4dfe772b9d164971',
//            'cs_acc8f1e49db9e14688bfd60e731239fdcf521f69',);
//        $request = $woocommerce->get('orders/7290');
        $orders = '';
        foreach ($request->line_items as $item) {
            $orders = $orders . '*' . $item->name . ' ' . $item->quantity . 'عدد' . '*';
        }

        $user = User::where('username', 'peptina')->first();
        $order = $user->orders()->create([
            'name' => $request->billing->first_name,
            'phone' => $request->billing->phone,
            'address' => $request->billing->city . ' ' . $request->billing->address_1,
            'zip_code' => $request->billing->postcode,
            'orders' => $orders,
            'desc' => $request->customer_note . ' - ' . $request->payment_method_title. ' - ' . $request->total,
//            'receipt' => $request->receipt,
            'total' => $request->total,
            'customerCost' => 0,
            'paymentMethod' => 'admin',
            'deliveryMethod' => 'admin',
        ]);


        TelegramController::sendOrderToBale($order);
        return 'hi';
    }
}
