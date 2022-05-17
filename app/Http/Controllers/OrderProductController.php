<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderProductController extends Controller
{
    public function show(Request $request)
    {
        $start = isset($request->start)?$request->start:30;
        $end = isset($request->end)?$request->end:0;
        echo $start;
        $products = Product::all()->keyBy('id');

        foreach ($products as $id => $product) {
            $orderProducts = OrderProduct::where('created_at', '>', now()->subDays($start))->
            where('created_at', '<', now()->subDays($end))->where('product_id', $id)->get();
            $products[$id]->number = 0;
            $products[$id]->total = 0;
            foreach ($orderProducts as $orderProduct) {
                $products[$id]->number += $orderProduct->number;
                $products[$id]->total += $orderProduct->number * $products[$id]->price;
            }
        }
        return view('statistic', ['products' => $products, 'start' => $start, 'end' => $end]);
    }
}
