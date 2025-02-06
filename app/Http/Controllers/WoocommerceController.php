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
        if (env('APP_ENV') == 'local') {
            $request = json_decode(file_get_contents('woo/1403-8-27_09-35-04 _ berrynocom _ سارا خوش قدم.txt'));
//            dd($request);
        }
        if (!isset($request->billing))
            return 'not used';
        file_put_contents('woo/' . verta(null, "Asia/Tehran")->
            format('Y-n-j_H-i-s') . ' _ ' . $website . ' _ ' . $request->billing->first_name .
            ' ' . $request->billing->last_name . '.txt', file_get_contents('php://input'));

        $websiteTitle = config('websites')[$website];

        $orders = '';
        $products = array();
        $text = 'بررسی مطابقت محصولات: ' . $websiteTitle . ' ' . $request->billing->first_name . ' ' . $request->billing->last_name . '
';
        $hasInconsistent = false;
        foreach ($request->line_items as $item) {
            $product_id = (int)filter_var($item->sku, FILTER_SANITIZE_NUMBER_INT);
            $product = Product::find($product_id);
            if ($product && substr($item->sku, 0, 1) == 's') {
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
            'address' => $request->billing->state . ' ' .$request->billing->city . ' ' . $request->billing->address_1,
            'zip_code' => $request->billing->postcode,
            'orders' => $orders,
            'desc' => $request->customer_note . ($desc ? ' - ' . $desc : ''),
            'total' => $request->total,
            'customerCost' => 0,
            'paymentMethod' => $request->payment_method_title,
            'deliveryMethod' => $request->shipping_lines[0]->method_title . ' _ ' . $deliveryTime,
            'counter' => 'approved',
            'confirm' => true,
//            'warehouse_id' => ($request->billing->city == 'تهران') ? 1 : 2,
            'warehouse_id' => 1,
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
                    $baleReq = app('Telegram')->sendOrderToBale($order, env('GroupId'));
                    if ($baleReq) {
                        $order->bale_id = $baleReq->result->message_id;
                    }
                    $order->save();
                    foreach ($products as $id => $data) {
                        $product = $data[1];
                        $order->orderProducts()->create([
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'number' => $data[0],
                            'price' => $product->price,
                        ]);
                    }
                }
            } else {
                $order = $web->order()->first();
                $order->update($orderData);
                if (!$order->deleted_at) {
                    app('Telegram')->deleteOrderFromBale($order, env('GroupId'));
                    $order->delete();
                    app('Telegram')->sendOrderToBale($order, '5742084958');
                    $order->save();
                    if ($order->state) {
                        (new OrderController)->changeState($order->id, 0);
                    }
                    $order->orderProducts()->delete();
                }
            }
            (new CommentController)->create($order, $user, 'سفارش دوباره ارسال شد');
        } else if ($request->status == 'processing' || $request->status == 'completed') {
            $order = $user->orders()->create($orderData);
            $web = $order->website()->create([
                'website' => $website,
                'website_id' => $request->id,
                'status' => $request->status,
            ]);
            (new CommentController)->create($order, $user, 'سفارش ایجاد شد');
            $order->save();
            foreach ($products as $id => $data) {
                $product = $data[1];
                $order->orderProducts()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'number' => $data[0],
                    'price' => $product->price,
                ]);
            }
            app('Telegram')->sendOrderToBale($order, env('GroupId'));
        }


        DB::commit();
        return 'order saved!';
    }

    public function viewFile()
    {
        $file = '1403-8-27_09-35-04 _ berrynocom _ سارا خوش قدم';
        $data = json_decode(file_get_contents("woo/{$file}.txt"));
        dd($data);
    }
}
