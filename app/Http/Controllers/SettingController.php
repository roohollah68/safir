<?php

namespace App\Http\Controllers;


use App\Helper\Helper;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Good;
use App\Models\Keysungood;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function showSettings()
    {
        Helper::meta('manageSafir');
        $setting = $this->settings();
        return view('settings', ['setting' => $setting,]);
    }

    public function editSettings(Request $req)
    {
        Helper::meta('manageSafir');
        foreach ($req->all() as $name => $value) {
            $value = str_replace(",", "", $value);
            Setting::where('name', $name)->update([
                'value' => $value
            ]);
        }
        return redirect(route('settings'));
    }

    public function invoiceData()
    {
        Helper::meta('usersEdit');
        return view('invoiceData', [
            'setting' => $this->settings(),
            'warehouses' => Warehouse::all()->keyBy('id'),
        ]);
    }

    public function invoiceDataSave(Request $req)
    {
        Helper::meta('usersEdit');
        foreach ($req->all() as $name => $value) {
            Setting::where('name', $name)->update([
                'value' => $value
            ]);
        }
        return redirect('/invoiceData');
    }

    public function clearRoute()
    {
        Artisan::call('route:cache');
        Artisan::call('config:clear');
    }

    public function command()
    {
//        Withdrawal::where('counter_confirm',1)->where('manager_confirm' , '<>' , 1)->update([
//            'counter_confirm'=>0,
//        ]);
//        Withdrawal::where('counter_confirm',2)->update([
//            'manager_confirm'=>2,
//        ]);



        foreach (Customer::with(['orders', 'transactions'])->get() as $customer) {
            if ($customer->balance() != $customer->balance) {
                echo $customer->name . $customer->balance() . '=>' . $customer->balance . '<br>';
                $customer->balance = $customer->balance();
//                $customer->save();
            }
        }

//        $this->combineCustomers();

//        $orders = Order::with('orderProducts')->where('user_id', 132)->get();
//        foreach ($orders as $order) {
//            (new WoocommerceController())->dorateashop($order);
//        }

    }

    public function combineCustomers()
    {
        $froms = [5098];
        $to = 4670;
        foreach ($froms as $from) {
            Order::where('customer_id', $from)->update(['customer_id' => $to]);
            CustomerTransaction::where('customer_id', $from)->update(['customer_id' => $to]);
            Customer::find($from)->delete();
        }

//        $froms = [4212];
//        $to = 2275;
//        foreach ($froms as $from) {
//            Order::where('customer_id', $from)->update(['customer_id' => $to]);
//            CustomerTransaction::where('customer_id', $from)->update(['customer_id' => $to]);
//            Customer::find($from)->delete();
//        }
    }

    public function importKeysun()
    {
        $rows = $this->csvToArray('export.csv');
        foreach ($rows as $row) {
            Keysungood::updateOrCreate([
                'good_id' => $row["شناسه کالا/خدمت(داخلی)"],
            ], [
                'tag' => $row["شناسه کالا/خدمت عمومی/اختصاصی"],
                'vat' => $row["نرخ مالیات"],
                'name' => str_replace('...', '', strip_tags($row["شرح تجاری کالا/خدمت"])),
            ]);
        }
    }

    public function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}


