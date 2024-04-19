<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class WoocommerceController extends Controller
{
    public function addPeptinaOrder($website)
    {

        DB::beginTransaction();

        $test = false;
        //$this->sendMessageToBale(["text" =>file_get_contents('php://input')],'1444566712');
        $request = json_decode(file_get_contents('php://input'));
        if($test)
            $request = json_decode(file_get_contents('woo/woo' . '968142' . '.html'));
        file_put_contents('woo/' .verta(null,"Asia/Tehran")->format('Y-n-j_H-i').' _ '.$website.' _ '. $request->billing->first_name . ' ' . $request->billing->last_name . '.txt', file_get_contents('php://input'));
        $orders = '';
        $products = array();
        $text = 'بررسی مطابقت محصولات: ' . $website . ' ' . $request->billing->first_name . ' ' . $request->billing->last_name . '
';
        $hasInconsistent = false;
        foreach ($request->line_items as $item) {
            $orders = $orders . ' ' . $item->name . ' ' . $item->quantity . 'عدد' . '،';
            if (substr($item->sku, 0, 1) == 's') {
                $product_id = (int)filter_var($item->sku, FILTER_SANITIZE_NUMBER_INT);
                $product = Product::find($product_id);
                if ($product)
                    $products[$product->id] = [$item->quantity, $product];
                else {
                    $hasInconsistent = true;
                    $text .= '❌ آیدی نامنطبق:
 ' . $item->name . ' -> ' . $item->sku . '
 ' . $product_id . '
 ';
                }

            } else {
                $hasInconsistent = true;
                $text .= '❌ محصول نامنطبق:
 ' . $item->name . ' -> ' . $item->sku . '
 ';
            }
        }
        if ($hasInconsistent)
            $this->sendTextToBale($text, '5742084958');

        $websiteTitle = "?";
        $desc = '';
        if ($website == 'matchano') {
            $websiteTitle = 'ماچانو';
            $request->total = $request->total * 10000;
            if ($request->payment_method == 'cod')
                $desc = $request->payment_method_title . ' - ' . number_format($request->total, 0, '.', '/') . ' ریال';
        } elseif ($website == 'peptina' || $website == 'berrynocom') {
            $request->total = $request->total * 10;
            if ($website == 'peptina')
                $websiteTitle = 'پپتینا';
            else
                $websiteTitle = 'برینو';
            if ($request->payment_method == 'cod')
                $desc = $request->payment_method_title . ' - ' . number_format($request->total, 0, '.', '/') . ' ریال';
        }

        $user = User::where('username', $website)->first();
        $order = $user->orders()->create([
            'name' => $request->billing->first_name . ' ' . $request->billing->last_name,
            'phone' => $request->billing->phone,
            'address' => $request->billing->city . ' ' . $request->billing->address_1,
            'zip_code' => $request->billing->postcode,
            'orders' => $orders,
            'desc' => $request->customer_note . ' - '  . $desc,
            'total' => $request->total,
            'customerCost' => 0,
            'paymentMethod' => $request->payment_method_title,
            'deliveryMethod' => $request->shipping_lines[0]->method_title,
        ]);


        if ($request->status == 'processing') {
            $order->bale_id = app('Telegram')->sendOrderToBale($order, env('GroupId'))->result->message_id;
            $order->save();
            foreach ($products as $id => $data) {
                $product = $data[1];
                $order->orderProducts()->create([
                    'product_id' => $product->id,
                    'verified' => true,
                    'name' => $product->name,
                    'number' => $data[0],
                    'price' => $product->price,
                ]);
                $product->update([
                    'quantity' => $product->quantity - $data[0],
                ]);
                $product->productChange()->create([
                    'order_id' => $order->id,
                    'change' => -$data[0],
                    'quantity' => $product->quantity,
                    'desc' => 'خرید اینترنتی سایت ' . $websiteTitle . ' خریدار: ' . $order->name,
                ]);
            }
        } else {
            if ($request->status != 'pending')
                app('Telegram')->sendOrderToBale($order, '5742084958');
            $order->forceDelete();
        }
        DB::commit();
        return 'order saved!';
    }
}
