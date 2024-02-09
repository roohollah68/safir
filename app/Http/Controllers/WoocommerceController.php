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

        //$this->sendMessageToBale(["text" =>file_get_contents('php://input')],'1444566712');
//        die();
        $request = json_decode(file_get_contents('php://input'));
//        $request = json_decode(file_get_contents('woo/woo' . '274006' . '.html'));
        file_put_contents('woo/woo' . rand(100000, 1000000) . '.html', file_get_contents('php://input'));
        $orders = '';
        $chatId = '5742084958';
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
            $this->sendTextToBale($text, $chatId);
        //return 'order saved!';

        if ($request->payment_method == 'cod') {
            if ($website == 'matchano') {
                $websiteTitle = 'ماچانو';
                $request->total = $request->total * 10000;
                $desc = $request->payment_method_title . ' - ' . number_format($request->total, 0, '.', '/') . ' ریال';
            } elseif ($website == 'peptina' || $website == 'berrynocom') {
                $request->total = $request->total * 10;
                $desc = $request->payment_method_title . ' - ' . number_format($request->total, 0, '.', '/') . ' ریال';
                if ($website == 'peptina')
                    $websiteTitle = 'پپتینا';
                else
                    $websiteTitle = 'برینو';
            } else {
                $desc = $request->payment_method_title . ' - ' . number_format($request->total) . ' ' . $request->currency_symbol;
                $websiteTitle = '?';
            }
        } else
            $desc = '';
        $user = User::where('username', $website)->first();
        $order = $user->orders()->create([
            'name' => $request->billing->first_name . ' ' . $request->billing->last_name,
            'phone' => $request->billing->phone,
            'address' => $request->billing->city . ' ' . $request->billing->address_1,
            'zip_code' => $request->billing->postcode,
            'orders' => $orders,
            'desc' => $request->customer_note . ' - ' . $request->shipping_lines[0]->method_title . ' - ' . $desc,
            'total' => $request->total,
            'customerCost' => 0,
            'paymentMethod' => 'admin',
            'deliveryMethod' => 'admin',
        ]);


        if ($request->status == 'processing') {
            app('Telegram')->sendOrderToBale($order, '4521394649');
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
