<?php

namespace App\Http\Controllers;

//use App\Models\Customer;
//use App\Models\CustomerTransaction;
//use App\Models\Good;
use App\Helper\Helper;
use App\Models\CouponLink;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Oldcustomer;
use App\Models\Order;
//use App\Models\Product;
//use App\Models\ProductData;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Setting;
//use App\Models\User;
//use GuzzleHttp\Psr7\Query;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Withdrawal;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
//use Illuminate\Support\Carbon;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

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
        $withdrawals = Withdrawal::all()->keyBy('id');
        $suppliers = Supplier::all()->keyBy('name');
        foreach ($withdrawals as $id => $withdrawal){
            if(isset($suppliers[$withdrawal->account_name])){
                $supplier = $suppliers[$withdrawal->account_name];
            }else{
                $supplier = Supplier::create([
                    'name' => $withdrawal->account_name,
                    'account' => $withdrawal->account_number,
                    'code' => $withdrawal->cheque_id,
                ]);
            }
            $withdrawal->update(['supplier_id' => $supplier->id]);
        }
    }

}


