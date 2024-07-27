<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup as IKM;
use App\Keyboards\Keyboard;

class DepositController extends Controller
{
    public $req, $chat_id, $bot;

    public function depositList()
    {
        if ($this->superAdmin()) {
            $deposits = Deposit::all();
            $users = User::with('deposits')->get()->keyBy('id');
            return view('depositList', ['deposits' => $deposits, 'users' => $users]);
        } else {
            $deposits = auth()->user()->deposits()->get();
            return view('depositList', ['deposits' => $deposits]);
        }
    }

    public function newForm(Request $req)
    {
        return view('addEditDeposit', ['deposit' => false,'req' => $req->all()]);
    }

    public function storeNew(Request $req)
    {
        $req->amount = str_replace(",","",$req->amount);
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'amount' => 'required',
        ]);
        $photo = '';
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", 'deposit');
        } elseif ($req->file) {
            $photo = $req->file;
        }

        auth()->user()->deposits()->create([
            'amount' => $req->amount,
            'desc' => $req->desc,
            'photo' => $photo
        ]);
        return redirect()->route('DepositList');
    }

    public function deleteDeposit($id)
    {
        if ($this->superAdmin()) {
            Deposit::where('confirmed', 'false')->findOrFail($id)->delete();
        } else {
            auth()->user()->deposits()->where('confirmed', 'false')->findOrFail($id)->delete();
        }
    }

    public function editDeposit($id)
    {
        if ($this->superAdmin()) {
            $deposit = Deposit::where('confirmed', 'false')->findOrFail($id);
            return view('addEditDeposit', ['deposit' => $deposit]);
        } else {
            $deposit = auth()->user()->deposits()->where('confirmed', 'false')->findOrFail($id);
            return view('addEditDeposit', ['deposit' => $deposit]);
        }
    }

    public function updateDeposit($id, Request $req)
    {
        $req->amount = str_replace(",","",$req->amount);
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'amount' => 'required',
        ]);
        if ($this->superAdmin()) {
            $deposit = Deposit::where('confirmed', 'false')->findOrFail($id);
        } else {
            $deposit = auth()->user()->deposits()->where('confirmed', 'false')->findOrFail($id);
        }
        $photo = $deposit->photo;
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", 'deposit');
        }

        $deposit->update([
            'amount' => $req->amount,
            'desc' => $req->desc,
            'photo' => $photo
        ]);
        return redirect()->route('DepositList');
    }

    public function changeConfirm($id)
    {
        if ($this->superAdmin()) {
            $deposit = Deposit::find($id);
            $deposit->update([
                'confirmed' => !$deposit->confirmed
            ]);
            $user = User::find($deposit->user_id);
            if ($deposit->confirmed) {
                $user->update([
                    'balance' => $user->balance + $deposit->amount
                ]);
                $deposit->transactions()->create([
                    'user_id' => $user->id,
                    'amount' => $deposit->amount,
                    'balance' => $user->balance,
                    'type' => true,
                    'description' => 'ثبت واریزی',
                ]);
            } else {
                $user->update([
                    'balance' => $user->balance - $deposit->amount
                ]);
                $deposit->transactions()->create([
                    'user_id' => $user->id,
                    'amount' => $deposit->amount,
                    'balance' => $user->balance,
                    'type' => false,
                    'description' => 'حذف واریزی',
                ]);
            }
            return $deposit->confirmed;
        }
    }

    public function receive()
    {
        $this->bot = new BotApi(env('TelegramDeposit'));

        $this->req = json_decode(file_get_contents('php://input'));
        $this->chat_id = $this->req->message->from->id;
        $user = User::where('telegram_id', $this->chat_id)->first();
        if ($user) {
            $type = $this->detect_type();
            if ($type == 'photo') {
                $this->new_order_receipt($user);

            }
            if ($type == 'text') {
                $message = 'برای ثبت واریزی تصویر رسید بانکی را به همین ربات بفرستید.';
                $this->bot->sendMessage($this->chat_id, $message);
            }

        } else {
            $message = 'حساب تلگرام شما ثبت نشده است، لطفا ابتدا در ربات @Safir_sefaresh_bot ثبت نام کنید.';
            $this->bot->sendMessage($this->chat_id, $message);
        }

    }

    public function detect_type()
    {
        if (isset($this->req->message->text))
            return 'text';
        if (isset($this->req->message->photo))
            return 'photo';
    }

    public function new_order_receipt($user)
    {
        $file_id = end($this->req->message->photo)->file_id;
        $caption = "برای ثبت جزئیات مربوط به این رسید روی لینک زیر کلیک کنید";
        $url = env('APP_URL') . "deposit/add/{$user->id}/{$user->telegram_code}/{$file_id}";
        $keyboard = new IKM(Keyboard::register_user($url, "ثبت فاکتور مربوط به این رسید"));
        $this->bot->sendPhoto($this->chat_id, $file_id, $caption, $this->req->message->message_id, $keyboard);
    }

