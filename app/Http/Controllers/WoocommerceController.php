<?php

namespace App\Http\Controllers;

use App\Models\User;
//use Illuminate\Http\Request;
//use Automattic\WooCommerce\Client;
use Illuminate\Support\Facades\DB;

class WoocommerceController extends Controller
{
    public function addPeptinaOrder($website)
    {

        DB::beginTransaction();

        $this->sendMessageToBale(["text" =>file_get_contents('php://input')],'1444566712');
//        die();
        $request = json_decode(file_get_contents('php://input'));
        file_put_contents('woo/woo'.rand(100000,1000000).'.html' , file_get_contents('php://input'));
        $orders = '';
        foreach ($request->line_items as $item) {
            $orders = $orders . ' ' . $item->name . ' ' . $item->quantity . 'عدد' . '،';
        }
        $desc = '';
        if($request->payment_method == 'cod'){
            if($website == 'matchano'){
                $desc = $request->payment_method_title. ' - ' . number_format($request->total*10000 , 0 , '.' , '/') . ' ریال';
            }
            elseif($website == 'peptina' || $website == 'berrynocom'){
                $desc = $request->payment_method_title. ' - ' . number_format($request->total*10, 0 , '.' , '/') .  ' ریال';
            }
            else{
                $desc = $request->payment_method_title. ' - ' . number_format($request->total) . ' ' . $request->currency_symbol;
            }
        }
        $user = User::where('username', $website)->first();
        $order = $user->orders()->create([
            'name' => $request->billing->first_name. ' ' .$request->billing->last_name,
            'phone' => $request->billing->phone,
            'address' => $request->billing->city . ' ' . $request->billing->address_1,
            'zip_code' => $request->billing->postcode,
            'orders' => $orders,
            'desc' => $request->customer_note .' - '.$request->shipping_lines[0]->method_title . ' - ' . $desc,
//            'receipt' => $request->receipt,
            'total' => $request->total,
            'customerCost' => 0,
            'paymentMethod' => 'admin',
            'deliveryMethod' => 'admin',
        ]);

        if($request->status != 'processing'){
            app('Telegram')->sendOrderToBale($order,'5742084958');
            $order->forceDelete();
        }else{
            app('Telegram')->sendOrderToBale($order,'4521394649');
        }
        DB::commit();
        return 'order saved!';
    }
}
