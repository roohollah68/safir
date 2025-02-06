<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup as IKM;
use TelegramBot\Api\Types\ReplyKeyboardMarkup as RKM;
use App\BaleAPIv2;

class TelegramController extends Controller
{
    public $bot;
    public $chat_id;
    public $req;



    public function sendOrderToBale($order, $chatId)
    {
        $message = self::createOrderMessage($order);
        $content = array("caption" => $message, "text" => $message, "photo" => env('APP_URL') . "receipt/{$order->receipt}");
        if ($order->receipt) {
            $response = $this->sendPhotoToBale($content, $chatId);
        } else {
            $response = $this->sendMessageToBale($content, $chatId);
        }
        if (isset($response->result)) {
            $order->bale_id = $response->result->message_id;
            $order->save();
        }
    }

    public function editOrderInBale($order, $chatId)
    {
        $message = $this->createOrderMessage($order);
        $content = array("message_id" => $order->bale_id, "text" => $message, "chat_id" => $chatId);
        return $this->editText($content);

    }

    public function deleteOrderFromBale($order, $chatId)
    {
        if ($order->bale_id)
            $this->deleteFromBale($chatId, $order->bale_id);
    }

    public function createOrderMessage($order)
    {
        $total = number_format($order->total);
        $customerCost = number_format($order->customerCost);
        $time = verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime();
        $time = Helper::number_En_Fa($time);
        return "شماره سفارش: {$order->id}
نام و نام خانوادگی: *{$order->name}*
شماره همراه: {$order->phone}
آدرس: {$order->address}
سفارشات: *{$order->orders()}*
کدپستی: {$order->zip_code}
توضیحات: {$order->desc}
مبلغ کل: {$total} ریال
پرداختی مشتری: {$customerCost} ریال
نحوه پرداخت: {$order->payMethod()}
نحوه ارسال: {$order->sendMethod()}
انبار: {$order->warehouse->name}
زمان ثبت: {$time}
سفیر: {$order->user()->first()->name}";

    }

    public function backUpDatabase()
    {
        $content = array("caption" => 'backup', "document" => "https://matchano.ir/safir_database_backup/safir.sql.gz");
        $chatId = '1444566712';
        $chatId2 = '1160743866';
        $this->sendDocumentToBale($content, $chatId);
        $this->sendDocumentToBale($content, $chatId2);
    }

    public function sms(Request $request)
    {
        $this->sendTextToBale(json_encode($request->all()),1444566712);
    }
}
