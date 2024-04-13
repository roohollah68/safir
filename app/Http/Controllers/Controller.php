<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\BaleAPIv2;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function number_Fa_En($Number)
    {
        foreach (['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'] as $en => $fa) {
            $Number = str_replace($fa, '' . $en, $Number);
        }
        return $Number;
    }

    public function number_En_Fa($Number)
    {
        foreach (['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'] as $en => $fa) {
            $Number = str_replace($en, '' . $fa, $Number);
        }
        return $Number;
    }

    public function role()
    {
        return auth()->user()->role;
    }

    public function superAdmin()
    {
        return auth()->user()->superAdmin();
    }

    public function admin()
    {
        return auth()->user()->admin();
    }

    public function safir()
    {
        return auth()->user()->safir();
    }

    public function print()
    {
        return auth()->user()->print();
    }

    public function warehouse()
    {
        return auth()->user()->warehouse();
    }

    public function addCityToAddress($order)
    {
        if ($order->customer_id) {
            $city = $order->customer()->first()->city()->first();
            if ($city->id > 0)
                $order->address = $city->province()->first()->name . '- ' . $city->name . '- ' . $order->address;
        }
        return $order;
    }

    public function settings()
    {
        $sets = Setting::all();
        $res = [];
        foreach ($sets as $set) {
            $res[$set->name] = $set->value;
        }
        return (object)$res;
    }

    public function errorBack($error)
    {
        return redirect()->back()->withInput()->withErrors([$error]);
    }

    public function deliveryCost($deliveryMethod)
    {
        $deliveryCosts = [
            'peyk' => $this->settings()->peykCost,
            'post' => $this->settings()->postCost,
            'paskerayeh' => 0,
        ];
        return $deliveryCosts[$deliveryMethod];
    }

    public function sendMessageToBale($array, $chatId)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        $array["chat_id"] = $chatId;
        return json_decode($bot->sendText($array));
    }

    public function sendTextToBale($text, $chatId)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        $array = array('chat_id' => $chatId, "text" => $text);
        return json_decode($bot->sendText($array));
    }

    public function sendPhotoToBale($array, $chatId)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        $array["chat_id"] = $chatId;
        return json_decode($bot->sendPhoto($array));
    }

    public function sendDocumentToBale($array, $chatId)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        $array["chat_id"] = $chatId;
        return json_decode($bot->sendDocument($array));
    }

    public function deleteFromBale($chatId, $message_id)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        return json_decode($bot->deleteMessage([
            "message_id" => $message_id,
            "chat_id" => $chatId,
        ]));
    }

    public function editText($content)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        return json_decode($bot->editText($content));
    }
}
