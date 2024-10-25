<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;

//use Illuminate\Support\Facades\Storage;
//use TelegramBot\Api\BotApi;
//use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup as IKM;
//use App\Keyboards\Keyboard;

class DepositController extends Controller
{
//    public $req, $chat_id, $bot;

    public function depositList()
    {
        if (auth()->user()->meta('manageSafir')) {
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
        return view('addEditDeposit', ['deposit' => false, 'req' => $req->all()]);
    }

    public function storeNew(Request $req)
    {
        $req->amount = str_replace(",", "", $req->amount);
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
        if (auth()->user()->meta('manageSafir')) {
            Deposit::where('confirmed', 'false')->findOrFail($id)->delete();
        } else {
            auth()->user()->deposits()->where('confirmed', 'false')->findOrFail($id)->delete();
        }
    }

    public function editDeposit($id)
    {
        if (auth()->user()->meta('manageSafir')) {
            $deposit = Deposit::where('confirmed', 'false')->findOrFail($id);
            return view('addEditDeposit', ['deposit' => $deposit]);
        } else {
            $deposit = auth()->user()->deposits()->where('confirmed', 'false')->findOrFail($id);
            return view('addEditDeposit', ['deposit' => $deposit]);
        }
    }

    public function updateDeposit($id, Request $req)
    {
        $req->amount = str_replace(",", "", $req->amount);
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'amount' => 'required',
        ]);
        if (auth()->user()->meta('manageSafir')) {
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
        Helper::meta('manageSafir');
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
