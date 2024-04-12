<?php

namespace App\Http\Controllers;

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


//    public function receive(Request $request)
//    {
//        $this->bot = new BotApi(env('TelegramToken'));
//
//        $this->req = json_decode(file_get_contents('php://input'));
//        $this->chat_id = $this->req->message->from->id;
//        $user = User::where('telegram_id', $this->chat_id)->first();
//        if ($user) {
//            $keyboard = new RKM(Keyboard::$user_option);
//            $message = 'برای ثبت فاکتور تصویر رسید بانکی را به همین ربات بفرستید.';
//            $type = $this->detect_type();
//            if ($type == 'text') {
//                $text = $this->req->message->text;
//                if ($text == Keyboard::$user_option[0][0])
//                    $this->see_orders(1, $user);
//                if ($text == Keyboard::$user_option[0][1])
//                    $message = 'برای افزایش اعتبار به ربات @safir_deposit_bot بروید.';
//                if ($text == Keyboard::$user_option[1][0])
//                    $this->list_orders($user);
//                if ($text == Keyboard::$user_option[1][1])
//                    $this->new_order($user);
//            }
//            if ($type == 'photo') {
//                $this->new_order_receipt($user);
//
//            }
//            $this->bot->sendMessage($this->chat_id, $message, null, false, null, $keyboard);
//
//        } else {
//            if (isset($this->req->message->contact->phone_number)) {
//                $phone = $this->req->message->contact->phone_number;
//                $phone = '0' . substr($phone, -10);
//                $user = User::where('phone', $phone)->first();
//                if ($user)
//                    $this->confirm_phone($user);
//                else
//                    $this->register_user($phone);
//            } else
//                $this->request_phone();
//        }
//
//    }

//    public function request_phone()
//    {
//        $message = 'شما هنوز احراز هویت نشده اید. برای احراز هویت شماره تلگرام باید با شماره سامانه یکی باشد. در این صورت با زدن دکمه "ارسال شماره تماس" شماره خود را بفرستید.';
//        $keyboard = new RKM(Keyboard::$request_phone);
//        $this->bot->sendMessage($this->chat_id, $message, null, false, null, $keyboard);
//    }

//    public function confirm_phone($user)
//    {
//        $user->update(['telegram_id' => $this->chat_id]);
//        $message = "
//تبریک حساب شما با موفقیت متصل شد
//اطلاعات ثبت شده از شما:
//نام و نام خانوادگی: {$user->name}
//نام کاربری: {$user->username}
//";
//        $this->bot->sendMessage($this->chat_id, $message);
//
//    }

//    public function register_user($phone)
//    {
//        $first_name = (isset($this->req->message->contact->first_name)) ? $this->req->message->contact->first_name : "";
//        $last_name = (isset($this->req->message->contact->last_name)) ? $this->req->message->contact->last_name : "";
//        $name = $last_name . ' ' . $first_name;
//        $url = env('APP_URL') . "register-from-telegram?name={$name}&phone={$phone}&telegram_id={$this->chat_id}";
//        $keyboard = new IKM(Keyboard::register_user($url, "ثبت نام"));
//        $message = "
// با این شماره تلفن حسابی وجود ندارد
//برای ایجاد حساب به لینک زیر بروید:";
//        $this->bot->sendMessage($this->chat_id, $message, null, false, null, $keyboard);
//    }

//    public function detect_type()
//    {
//        if (isset($this->req->message->text))
//            return 'text';
//        if (isset($this->req->message->photo))
//            return 'photo';
//    }

//    public function see_orders($count, $user)
//    {
//        if ($user->role == 'admin') {
//            $orders = Order::orderBy('id', 'desc')->limit($count)->get();
//        } else {
//            $orders = $user->orders()->orderBy('id', 'desc')->limit($count)->get();
//        }
//
//        foreach ($orders as $order) {
//            $message = self::createOrderMessage($order);
//            if ($order->receipt) {
//                $this->bot->sendPhoto($this->chat_id, env('APP_URL') . "receipt/{$order->receipt}", $message);
//            } else {
//                $this->bot->sendMessage($this->chat_id, $message);
//            }
//
//        }
//    }

//    public function list_orders($user)
//    {
//        $message = "برای دیدن لیست کامل فاکتورها به آدرس زیر بروید:";
//        $url = env('APP_URL') . "list-orders/{$user->id}/{$user->telegram_code}";
//        $keyboard = new IKM(Keyboard::register_user($url, "مشاهده تمام فاکتورها"));
//        $this->bot->sendMessage($this->chat_id, $message, null, false, null, $keyboard);
//    }

//    public function new_order($user)
//    {
//        $message = "برای ثبت فاکتور جدید به آدرس زیر بروید:";
//        $url = env('APP_URL') . "new-order/{$user->id}/{$user->telegram_code}";
//        $keyboard = new IKM(Keyboard::register_user($url, "ثبت فاکتور جدید"));
//        $this->bot->sendMessage($this->chat_id, $message, null, false, null, $keyboard);
//    }

//    public function new_order_receipt($user)
//    {
//        $file_id = end($this->req->message->photo)->file_id;
//        $caption = "برای ثبت فاکتور مربوط به این رسید روی لینک زیر کلیک کنید";
//        $url = env('APP_URL') . "new-order-receipt/{$user->id}/{$user->telegram_code}/{$file_id}";
//        $keyboard = new IKM(Keyboard::register_user($url, "ثبت فاکتور مربوط به این رسید"));
//        $this->bot->sendPhoto($this->chat_id, $file_id, $caption, $this->req->message->message_id, $keyboard);
//    }

//    public static function savePhoto($file_id)
//    {
//        if (Storage::disk('public')->exists("$file_id.jpg")) {
//            return true;
//        }
//        $bot = new BotApi(env('TelegramToken'));
//        $file = $bot->downloadFile($file_id);
//        Storage::disk('public')->put($file_id . '.jpg', $file);
//        return true;
//    }

//    public static function sendOrderToTelegram($order, $user = null)
//    {
//        $bot = new BotApi(env('TelegramToken'));
//        if (!$user)
//            $user = $order->user()->first();
//        $message = self::createOrderMessage($order);
//        if ($user->telegram_id) {
//            if ($order->receipt) {
//                $bot->sendPhoto($user->telegram_id, env('APP_URL') . "receipt/{$order->receipt}", $message);
//            } else {
//                $bot->sendMessage($user->telegram_id, $message);
//            }
//        }
//
//    }

//    public static function sendOrderToTelegramById($id)
//    {
//        $order = Order::findOrFail($id);
//        $user = auth()->user();
//        $bot = new BotApi(env('TelegramToken'));
//        $message = self::createOrderMessage($order);
//        if ($user->telegram_id) {
//            if ($order->receipt) {
//                $bot->sendPhoto($user->telegram_id, env('APP_URL') . "receipt/{$order->receipt}", $message);
//            } else {
//                $bot->sendMessage($user->telegram_id, $message);
//            }
//            return "با موفقیت برای تلگرام شما ارسال شد";
//        }
//        return "حساب تلگرام شما ثبت نشده است.";
//
//    }

//    public static function sendOrderToTelegramAdmins($order)
//    {
//        $bot = new BotApi(env('TelegramToken'));
//        $users = User::where('role', 'admin')->get();
//        $message = self::createOrderMessage($order);
//        foreach ($users as $user) {
//            if ($user->telegram_id) {
//                if ($order->receipt) {
//                    $bot->sendPhoto($user->telegram_id, env('APP_URL') . "receipt/{$order->receipt}", $message);
//                } else {
//                    $bot->sendMessage($user->telegram_id, $message);
//                }
//            }
//        }
//
//    }

    public function sendOrderToBale($order, $chatId)
    {
        $message = self::createOrderMessage($order);
        $content = array("caption" => $message, "text" => $message, "photo" => env('APP_URL') . "receipt/{$order->receipt}");
        if ($order->receipt) {
            return $this->sendPhotoToBale($content, $chatId);
        } else {
            return $this->sendMessageToBale($content, $chatId);
        }
    }

    public function editOrderInBale($order, $chatId)
    {
        $message = $this->createOrderMessage($order);
        $content = array("message_id" => $order->bale_id, "text" => $message,"chat_id"=>$chatId);
        return $this->editText($content);

    }

    public function createOrderMessage($order)
    {
        $total = number_format($order->total);
        $customerCost = number_format($order->customerCost);
        $time = verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime();
        $time = $this->number_En_Fa($time);
        return "
نام و نام خانوادگی: *{$order->name}*
شماره همراه: {$order->phone}
آدرس: {$order->address}
سفارشات: *{$order->orders}*
کدپستی: {$order->zip_code}
توضیحات: {$order->desc}
مبلغ کل: {$total} ریال
پرداختی مشتری: {$customerCost} ریال
نحوه پرداخت: {$order->payMethod()}
نحوه ارسال: {$order->sendMethod()}
زمان ثبت: {$time}
سفیر: {$order->user()->first()->name}";

    }

    public function backUpDatabase()
    {
        $content = array("caption" => 'backup', "document" => "https://matchano.ir/safir_database_backup/safir.sql.gz");
        $chatId = '1444566712';
        print_r($this->sendDocumentToBale($content, $chatId));
    }
}
