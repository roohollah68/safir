<?php

namespace App\Http\Controllers;

use App\Models\CustomerTransaction;
use App\Models\Good;
use App\Models\Keysun;
use App\Models\Keysungood;
use App\Models\Order;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KeysunController extends Controller
{
    public function list(Request $request)
    {
        $orders = Order::where('user_id', 30)
            ->where('total', '>', 0)
            ->whereIn('paymentMethod', ['به پرداخت ملت', 'پرداخت پارسیان', 'درگاه پرداخت پارسیان', 'درگاه به پرداخت ملت'])
            ->whereDate('created_at', '>=', Verta::parse($request->from ?? '1404/01/01')->DateTime())
            ->whereDate('created_at', '<=', Verta::parse($request->to ?? verta())->DateTime())
            ->with(['orderProducts.product.good', 'keysun.keysunMetas'])
            ->get();
        $orders->each(function ($order) {
            if (!$order->keysun || $order->keysun->conv == 0 || $order->keysun->conv > 1.5)
                $order->keysun = $this->createKeysunInvoice($order);
        });
        if (!$request->sent) {
            $orders = $orders->filter(fn($order) => !$order->keysun->sent);
        }

        $transactions = CustomerTransaction::where('verified', 'approved')
            ->where('official', true)
            ->where('pay_method', 'cash')
            ->whereDate('created_at', '>=', Verta::parse($request->from ?? '1404/01/01')->DateTime())
            ->whereDate('created_at', '<=', Verta::parse($request->to ?? verta())->DateTime())
            ->with(['customer.user', 'keysun.keysunMetas', 'paymentLinks.order.orderProducts'])
            ->get();

        $transactions->each(function ($transaction) {
            if (!$transaction->keysun || $transaction->keysun->conv == 0 || $transaction->keysun->conv > 1.5)
                $transaction->keysun = $this->createKeysunInvoice(null, $transaction);
        });
        if (!$request->sent) {
            $transactions = $transactions->filter(fn($transaction) => !$transaction->keysun->sent);
        }

        echo view('keysun/taxList', compact('orders', 'transactions'));
    }

    public function createKeysunInvoice($order, $transaction = null)
    {
        if ($order) {
            $list = [];
            $total = 0;
            foreach ($order->orderProducts as $oP) { //$oP = $orderProduct
                $kG = $oP->product->good->isKeysun(); //$kG = $keysungood
                if ($kG) {
                    $total += $oP->number * $oP->price;
                    if (isset($list[$kG->id])) {
                        $list[$kG->id]['price'] =
                            round(($list[$kG->id]['price'] * $list[$kG->id]['number'] + $oP->number * $oP->price) /
                                ($list[$kG->id]['number'] + $oP->number));
                        $list[$kG->id]['number'] += $oP->number;
                    } else {
                        $list[$kG->id] = [
                            'number' => +$oP->number,
                            'price' => +$oP->price
                        ];
                    }
                }
            }
            if ($order->keysun) {
                $order->keysun->keysunMetas()->delete();
                $order->keysun()->delete();
            }
            if ($total > 0) {
                $conv = round($order->total / $total, 3);
                $keysun = $order->keysun()->create([
                    'conv' => $conv,
                    'id' => $order->id + 1000000,
                ]);
                foreach ($list as $id => $value) {
                    $keysun->keysunMetas()->create([
                        'keysungood_id' => $id,
                        'number' => $value['number'],
                        'price' => round($value['price'] * $conv),
                    ]);
                }
            } else {
                $order->keysun()->create([
                    'conv' => 0,
                    'id' => $order->id + 1000000,
                ]);
            }
            return $order->keysun()->with('keysunMetas')->first();
        }
        if ($transaction) {
            $total = 0;
            $list = [];
            foreach ($transaction->paymentLinks as $paymentLink) {
                $order = $paymentLink->order;
                foreach ($order->orderProducts as $oP) { //$oP = $orderProduct
                    if ($total > $transaction->amount)
                        break;
                    $kG = $oP->product->good->isKeysun(); //$kG = $keysungood
                    if ($kG) {
                        $total += $oP->number * $oP->price;
                        if (isset($list[$kG->id])) {
                            $list[$kG->id]['price'] =
                                round(($list[$kG->id]['price'] * $list[$kG->id]['number'] + $oP->number * $oP->price) /
                                    ($list[$kG->id]['number'] + $oP->number));
                            $list[$kG->id]['number'] += $oP->number;
                        } else {
                            $list[$kG->id] = [
                                'number' => +$oP->number,
                                'price' => +$oP->price
                            ];
                        }
                    }
                }
            }
            if ($transaction->keysun) {
                $transaction->keysun->keysunMetas()->delete();
                $transaction->keysun()->delete();
            }
            if ($total > 0) {
                $conv = round($transaction->amount / $total, 3);
                $keysun = $transaction->keysun()->create([
                    'conv' => $conv,
                    'id' => $transaction->id + 3000000
                ]);
                foreach ($list as $id => $value) {
                    $keysun->keysunMetas()->create([
                        'keysungood_id' => $id,
                        'number' => $value['number'],
                        'price' => round($value['price'] * $conv),
                    ]);
                }
            } else {
                $transaction->keysun()->create([
                    'conv' => 0,
                    'id' => $transaction->id + 3000000
                ]);
            }
            return $transaction->keysun()->with('keysunMetas')->first();
        }
    }

    public function good()
    {
        $goods = Good::where('tag', '>', pow(10, 12))->get()->keyBy('id');
        $keysungoods = Keysungood::all()->keyBy('id');
        $goods = $goods->filter(fn($good) => !isset($keysungoods[$good->id]));
        return view('keysun.good', compact('goods'));
    }

    public function excelData(Request $request)
    {
        $sql = Keysun::whereIn('id', $request->ids)
            ->with(['keysunMetas.keysungood']);
        $keysuns = $sql->get();
        $sql->update(['sent' => true]);

        return [
            view('keysun.invoice1', compact('keysuns'))->render(),
            view('keysun.invoice2', compact('keysuns'))->render()
        ];

    }

    public function viewChange($id)
    {
        $keysun = Keysun::with(['order', 'transaction'])->findOrFail($id);
        $order = $keysun->order;
//        $transaction = $keysun->transaction;
        $transaction = CustomerTransaction::find($keysun->customer_transaction_id);
        return view('keysun.change', compact('order', 'keysun', 'transaction'));
    }

    public function importForm()
    {
        echo view('keysun.import');
    }

    public function import(Request $request)
    {
        // اعتبارسنجی فایل آپلود شده
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        // دریافت فایل از درخواست
        $file = $request->file('excel_file');

        // خواندن فایل اکسل و تبدیل به آرایه
        $excelData = Excel::toArray([], $file);

        // یا برای خواندن هر Sheet به صورت جداگانه
        $sheetsData = [];
        $sheetNames = Excel::load($file)->getSheetNames();

        foreach ($sheetNames as $sheetName) {
            $sheetsData[$sheetName] = Excel::toArray([], $file, $sheetName);
        }

        // خروجی به صورت آرایه
        return response()->json([
            'all_sheets_data' => $excelData,
            'per_sheet_data' => $sheetsData
        ]);
    }
}
