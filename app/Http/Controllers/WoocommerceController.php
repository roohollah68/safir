<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Automattic\WooCommerce\Client;
use App\Http\Controllers\TelegramController as Telegram;
use Illuminate\Support\Facades\DB;

class WoocommerceController extends Controller
{
    public function addPeptinaOrder($website)
    {

        DB::beginTransaction();

//        $this->sendMessageToBale(["text" =>json_encode(json_decode(file_get_contents('php://input')))],'1444566712');
//        die();
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

        app('Telegram')->sendOrderToBale($order);
        $order->delete();
//        TelegramController::sendOrderToBale($order);
        DB::commit();
        return 'hi';
    }
}
