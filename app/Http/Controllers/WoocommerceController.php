<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Websites;
use Illuminate\Support\Facades\DB;

class WoocommerceController extends Controller
{
    public function addWebsiteOrder($website)
    {
        DB::beginTransaction();

        //$this->sendMessageToBale(["text" =>file_get_contents('php://input')],'1444566712');
        $request = json_decode(file_get_contents('php://input'));
        if (env('APP_ENV') == 'local')
            $request = json_decode(file_get_contents('woo/1403-4-22_05-48-03 _ peptina _ کوثر امیرخانی.txt'));
        if (!isset($request->billing))
            return 'not used';
        file_put_contents('woo/' . verta(null, "Asia/Tehran")->
            format('Y-n-j_H-i-s') . ' _ ' . $website . ' _ ' . $request->billing->first_name .
            ' ' . $request->billing->last_name . '.txt', file_get_contents('php://input'));

        $websiteTitle = [
            'matchano' => 'ماچانو',
            'peptina' => 'پپتینا',
            'matchashop' => 'ماچا شاپ',
            'berrynocom' => 'برینو',
        ][$website];

        $orders = '';
        $products = array();
        $text = 'بررسی مطابقت محصولات: ' . $websiteTitle . ' ' . $request->billing->first_name . ' ' . $request->billing->last_name . '
';
        $hasInconsistent = false;
        foreach ($request->line_items as $item) {
            $product_id = (int)filter_var($item->sku, FILTER_SANITIZE_NUMBER_INT);
            $product = Product::find($product_id);
            if ($product && substr($item->sku, 0, 1) == 's') {
                $orders .= ' ' . $product->name . ' ' . $item->quantity . 'عدد' . '،';
                $products[$product->id] = [$item->quantity, $product];
            } else {
                $orders .= ' ' . $item->name . ' ' . $item->quantity . 'عدد' . '،';
                $hasInconsistent = true;
                $text .= '❌ آیدی نامنطبق: ' . $item->name . ' -> ' . $item->sku . ' ' . $product_id . '* *';
            }
        }
        if ($hasInconsistent)
            $this->sendTextToBale($text, '5742084958');

        $desc = '';
        if ($website == 'matchano')
            $request->total = $request->total * 10000;
        else
            $request->total = $request->total * 10;

        if ($request->payment_method == 'cod')
            $desc = $request->payment_method_title . ' - ' . number_format($request->total, 0, '.', '/') . ' ریال';
        $user = User::where('username', $website)->first();

        $web = Websites::where('website_id', $request->id)->where('website', $website)->first();
        $deliveryTime = '';
        $metaData = collect($request->meta_data)->keyBy('key');
        if (isset($metaData['_delivery_time_novin']))
            $deliveryTime = $metaData['_delivery_time_novin']->value;
        $orderData = [
            'name' => $request->billing->first_name . ' ' . $request->billing->last_name,
            'phone' => $request->billing->phone,
            'address' => $request->billing->city . ' ' . $request->billing->address_1,
            'zip_code' => $request->billing->postcode,
            'orders' => $orders,
            'desc' => $request->customer_note . ($desc ? ' - ' . $desc : ''),
            'total' => $request->total,
            'customerCost' => 0,
            'paymentMethod' => $request->payment_method_title,
            'deliveryMethod' => $request->shipping_lines[0]->method_title . ' _ ' . $deliveryTime,
            'counter' => 'approved',
            'confirm' => true,
        ];
        if ($web) {
            $web->update([
                'status' => $request->status,
            ]);
            if ($request->status == 'processing' || $request->status == 'completed') {
                $order = $web->order()->withTrashed()->first();
                $order->update($orderData);
                if ($order->deleted_at) {
                    app('Telegram')->deleteOrderFromBale($order, '5742084958');
                    $order->restore();
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
                }
            } else {
                $order = $web->order()->first();
                $order = $order->update($orderData);
                if (!$order->deleted_at) {
                    app('Telegram')->deleteOrderFromBale($order, env('GroupId'));
                    $order->delete();
                    $order->bale_id = app('Telegram')->sendOrderToBale($order, '5742084958')->result->message_id;
                    $order->save();
                    $order->orderProducts()->delete();
                    foreach ($products as $id => $data) {
                        $product = $data[1];
                        $product->update([
                            'quantity' => $product->quantity + $data[0],
                        ]);
                        $product->productChange()->create([
                            'order_id' => $order->id,
                            'change' => $data[0],
                            'quantity' => $product->quantity,
                            'desc' => 'لغو خرید اینترنتی سایت ' . $websiteTitle . ' خریدار: ' . $order->name,
                        ]);
                    }
                }
            }
        } else {
            $order = $user->orders()->create($orderData);

            $web = $order->website()->create([
                'website' => $website,
                'website_id' => $request->id,
                'status' => $request->status,
            ]);

            if ($request->status == 'processing' || $request->status == 'completed') {
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
                $order->delete();
                if ($request->status != 'pending') {
                    $order->bale_id = app('Telegram')->sendOrderToBale($order, '5742084958');
                    $order->save();
                }
            }
        }

        DB::commit();
        return 'order saved!';
    }
}
