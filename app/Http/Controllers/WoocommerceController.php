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
        $this->sendMessageToBale(["text" =>$request->billing->first_name],'1444566712');
        die();
        $request = json_decode(file_get_contents('php://input'));
        $orders = '';
        foreach ($request->line_items as $item) {
            $orders = $orders . '*' . $item->name . ' ' . $item->quantity . 'عدد' . '*';
        }
//        if()
        $desc = '';
        if($request->payment_method == 'cod'){
            $desc = ' - ' . $request->payment_method_title. ' - ' . number_format($request->total) . ' ' . $request->currency_symbol;
        }else if($request->status != 'completed'){
            return 'not completed';
        }
        $user = User::where('username', $website)->first();
        $order = $user->orders()->create([
            'name' => $request->billing->first_name. ' ' .$request->billing->last_name,
            'phone' => $request->billing->phone,
            'address' => $request->billing->city . ' ' . $request->billing->address_1,
            'zip_code' => $request->billing->postcode,
            'orders' => $orders,
            'desc' => $request->customer_note . $desc,
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
