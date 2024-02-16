<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Product;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;

class OrderProductController extends Controller
{
    public function showStatistic(Request $request)
    {
        if(isset($request->start)) {
            $start = $request->start;
        }else{
            $start = $this->number_En_Fa(verta("-1 month")->format('Y/m/d'));
        }
        $v = Verta::parse($this->number_Fa_En($start));
        $from = Carbon::createFromTimestamp($v->timestamp);

        if(isset($request->end)){
            $end = $request->end;
        }else{
            $end = $this->number_En_Fa(verta()->format('Y/m/d'));
        }
        $v = Verta::parse($this->number_Fa_En($end));
        $to = Carbon::createFromTimestamp($v->timestamp+24*3600);
        $products = Product::all()->keyBy('id');
        $totalSale = 0;

        foreach ($products as $id => $product) {
            $orderProducts = OrderProduct::where('created_at', '>', $from)->
            where('created_at', '<', $to)->where('product_id', $id)->get();
            $products[$id]->number = 0;
            $products[$id]->total = 0;
            foreach ($orderProducts as $orderProduct) {
                $products[$id]->number += $orderProduct->number;
                $products[$id]->total += $orderProduct->number * $orderProduct->price;
            }
            $totalSale += $products[$id]->total;
        }
        return view('statistic', ['products' => $products, 'start' => $start, 'end' => $end , 'totalSale' => $totalSale]);
    }
}
