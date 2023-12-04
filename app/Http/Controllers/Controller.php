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

    public function isAdmin()
    {
        if (auth()->user()->role == 'admin') {
            return true;
        } else {
            return false;
        }
    }

    public function userId()
    {
        return auth()->user()->id;
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
            'admin' => 0,
        ];
        return $deliveryCosts[$deliveryMethod];
    }

    public function sendMessageToBale($array, $chatId)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        $array["chat_id"] =  $chatId;
        $bot->sendText($array);
    }

    public function sendPhotoToBale($array, $chatId)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        $array["chat_id"] =  $chatId;
        $bot->sendPhoto($array);
    }

    public function sendDocumentToBale($array, $chatId)
    {
        $bot = new BaleAPIv2(env('BaleToken'));
        $array["chat_id"] =  $chatId;
        return $bot->sendDocument($array);
    }
}
