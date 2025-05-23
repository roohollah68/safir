<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Product;
use App\Models\User;
use App\Models\Websites;
use Illuminate\Support\Facades\DB;

class WoocommerceController extends Controller
{
    public function addWebsiteOrder($website)
    {
        DB::beginTransaction();

        $request = json_decode(file_get_contents('php://input'));
        if (env('APP_ENV') == 'local') {
            $request = json_decode(file_get_contents('woo/1404-2-28_12-07-33 _ dorateashop _ اذین کیانی.txt'));
        }
        if (!isset($request->billing))
            return 'not used';
        file_put_contents('woo/' . verta(null, "Asia/Tehran")->
            format('Y-n-j_H-i-s') . ' _ ' . $website . ' _ ' . $request->billing->first_name .
            ' ' . $request->billing->last_name . '.txt', file_get_contents('php://input'));

        $currency = ['IRHT' => 10000, 'IRT' => 10][$request->currency];
        $desc = '';

        if ($request->payment_method == 'cod')
            $desc = $request->payment_method_title . ' - ' . number_format($request->total * $currency, 0, '.', '/') . ' ریال';
        $user = User::where('username', $website)->first();

        $web = Websites::where('website_id', $request->id)->where('website', $website)->first();
        $metaData = (object)collect($request->meta_data)->keyBy('key')->map(fn($data) => $data->value)->all();
        $deliveryTime = $metaData->_delivery_time_novin ?? '';
        $deliveryMethod = ($request->shipping_lines[0] ?? (object)[])->method_title ?? '';
        $house_num = isset($metaData->_billing_house_num) ? ' پلاک: ' . $metaData->_billing_house_num : '';
        $unit_num = isset($metaData->_billing_unit_num) ? ' واحد: ' . $metaData->_billing_unit_num : '';
        $orderData = [
            'name' => $request->billing->first_name . ' ' . $request->billing->last_name,
            'phone' => $request->billing->phone,
            'address' => $request->billing->state . '، ' . $request->billing->city . '، '
                . $request->billing->address_1 ,
            'zip_code' => $request->billing->postcode,
            'orders' => '',
            'desc' => $request->customer_note . ($desc ? ' - ' . $desc : ''),
            'total' => $request->total * $currency,
            'customerCost' => 0,
            'paymentMethod' => $request->payment_method_title,
            'deliveryMethod' => $deliveryMethod . ' _ ' . $deliveryTime,
            'counter' => 'approved',
            'confirm' => true,
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
                    $order->restore();
                    $this->orderProducts($order, $request, $website, $currency);
                    (new TelegramController())->deleteOrderFromBale($order, '5742084958');
                    if ($website == 'dorateashop')
                        $this->dorateashop($order, $request);
                    else {
                        $baleReq = (new TelegramController())->sendOrderToBale($order, env('GroupId'));
                        if ($baleReq) {
                            $order->bale_id = $baleReq->result->message_id;
                        }
                        $order->save();
                    }
                }
            } else {
                $order = $web->order()->first();
                $order->update($orderData);
                if (!$order->deleted_at) {
                    (new TelegramController())->deleteOrderFromBale($order, env('GroupId'));
                    $order->delete();
                    (new TelegramController())->sendOrderToBale($order, '5742084958');
                    $order->save();
                    if ($order->state) {
                        (new OrderController)->changeState($order->id, 0);
                    }
                    $order->orderProducts()->delete();
                }
            }
        } else if ($request->status == 'processing' || $request->status == 'completed') {
            $order = $user->orders()->create($orderData);
            $web = $order->website()->create([
                'website' => $website,
                'website_id' => $request->id,
                'status' => $request->status,
            ]);
            (new CommentController)->create($order, $user, 'سفارش ایجاد شد');
            $order->save();
            $this->orderProducts($order, $request, $website, $currency);
            if ($website == 'dorateashop')
                $this->dorateashop($order, $request);
            else
                (new TelegramController())->sendOrderToBale($order, env('GroupId'));
        }
        DB::commit();
        return 'order saved!';
    }

    public function viewFile()
    {
        $file = '1404-2-28_11-56-21 _ dorateashop _ علیرضا قربانی';
        $data = json_decode(file_get_contents("woo/{$file}.txt"));
        dd($data);
    }

    public function orderProducts($order, $request, $website)
    {
        $text = '';
        $currency = ['IRHT' => 10000, 'IRT' => 10][$request->currency];
        foreach ($request->line_items as $item) {
            $product_id = (int)filter_var($item->sku, FILTER_SANITIZE_NUMBER_INT);
            $product = Product::find($product_id);
            if (!$product || substr($item->sku, 0, 1) != 's') {
                $text .= '❌ آیدی نامنطبق: ' . $item->name . ' -> ' . $item->sku . ' ' . $product_id . '* *';
            }
            $order->orderProducts()->create([
                'product_id' => $product ? $product_id : 9531,
                'name' => $product ? $product->name : $item->name,
                'number' => $item->quantity,
                'price' => round(+$item->total / $item->quantity * $currency),
            ]);
        }
        if ($text) {
            $websiteTitle = config('websites')[$website];
            $text = 'بررسی مطابقت محصولات: ' . $websiteTitle . ' ' . $request->billing->first_name . ' ' . $request->billing->last_name . "\r\n" . $text;
            $this->sendTextToBale($text, '5742084958');
        }
    }

    public function dorateashop($order, $request) //حذف سفارش سایت دوراتی شاپ و اضافه کردن به سفارش خانم موسوی
    {
        $website_id = $order->website->website_id;
        $website = Websites::where('website_id', $website_id)->where('website', 'moosavi')->first();
        if ($website) {
            $order->delete();
            return;
        }
        $user = User::find(10);
        $products = Product::with('good')
            ->find($order->orderProducts->pluck('product_id'))
            ->keyBy('id');
        $products = (new OrderController())->calculateDiscount($products, $user);
        $newOrder = $order->replicate();
        echo $order->name . ' ( ' . $order->id . ' )<br>';
        echo 'جمع فاکتور سایت: ' . number_format($order->total) . ' ریال ' . '<br>';
        $newOrder->fill([
            'user_id' => 10,
            'paymentMethod' => 'credit',
            'desc' => $newOrder->desc . " (دوراتی شاپ)",
            'total' => 0,
        ])->save();
        foreach ($order->orderProducts->keyBy('product_id') as $id => $orderProduct) {
            $product = $products[$id];
            $discount = +$product->discount;
            $price = round((100 - $discount) * $product->good->price / 100);
            $newOrder->total += $price * (+$orderProduct->number);
            $newOrder->orderProducts()->create([
                'name' => $product->good->name,
                'price' => $price,
                'product_id' => $id,
                'number' => $orderProduct->number,
                'discount' => $discount,
            ]);
            echo 'سایت: ' . $orderProduct->name . ' تعداد: ' . +$orderProduct->number . ' تخفیف: ' . +$orderProduct->discount . ' قیمت واحد: ' .
                number_format($orderProduct->price) . ' کل: ' . number_format($orderProduct->price * $orderProduct->number) . '<br>';

            echo 'سفیر: ' . $product->good->name . ' تعداد: ' . +$orderProduct->number . ' تخفیف: ' . $discount . ' قیمت واحد: ' .
                number_format($price) . ' کل: ' . number_format($price * $orderProduct->number) . '<br>';
        }
        echo 'سفیر: هزینه ارسال:' . number_format(Helper::settings()->{'peykeShahri'}) . '<br>';
//        $newOrder->total += Helper::settings()->{'peykeShahri'};
        $newOrder->total += $request->shipping_total;
        echo $newOrder->name . ' ( ' . $newOrder->id . ' )<br>';
        echo 'جمع فاکتور سفیر: ' . number_format($newOrder->total) . ' ریال ' . '<br>';
        echo '=============================<br><br>';
        $user->update([
            'balance' => $user->balance - $newOrder->total
        ]);
        $newOrder->transactions()->create([
            'user_id' => 10,
            'amount' => $newOrder->total,
            'balance' => $user->balance,
            'type' => false,
            'description' => 'ثبت سفارش (دوراتی شاپ)',
        ]);
        (new TelegramController())->sendOrderToBale($newOrder, env('GroupId'));
        $newOrder->save();
        $newOrder->website()->create([
            'website' => 'moosavi',
            'website_id' => $website_id,
            'status' => $order->website->status,
        ]);
        (new CommentController)->create($newOrder, $user, 'سفارش ایجاد شد');
        (new CommentController)->create($newOrder, $user, 'شماره سفارش سایت: ' . $website_id);
        $order->delete();
    }
}
