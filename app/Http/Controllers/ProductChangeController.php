<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ProductChangeController extends Controller
{
    public function addQuantity($id)
    {
        $product = Product::findOrFail($id);
        $productChanges = $product->productChange()->get()->keyBy('id');
        foreach ($productChanges as $id => $productChange)
            $productChanges[$id]->created_at = verta($productChange->created_at)->timezone('Asia/tehran')->formatJalaliDatetime();
        return view('addEditProductChange', [
            'product' => $product,
            'productChanges' => $productChanges,
        ]);
    }

    public function insertRecord($id, Request $req)
    {
        DB::beginTransaction();
        $product = Product::findOrFail($id);
        $productChange = new ProductChange();
        $productChange->product_id = $product->id;
        if ($req->addType == 'add') {
            $productChange->change = +$req->add;
            $product->quantity += $req->add;
            $productChange->desc = 'اضافه کردن موجودی به اندازه ' . $req->add . ' عدد';
        } else {
            $productChange->change = +$req->value - (+$product->quantity);
            $product->quantity = $req->value;
            $productChange->desc = 'اصلاح موجودی به مقدار' . $req->value . ' عدد';
        }
        $productChange->quantity = $product->quantity;
        $product->alarm = $req->alarm;
        $product->available = ($req->available == 'true');
        $product->save();
        if ($productChange->change != 0)
            $productChange->save();
        DB::commit();
        return redirect('/productQuantity/add/' . $id);
    }

    public function deleteRecord($id)
    {
        $productChange = ProductChange::findOrFail($id);
        if ($productChange->isDeleted)
            return redirect()->back();
        $product = $productChange->product()->first();
        $product->quantity -= $productChange->change;
        $product->save();
        $productChange->isDeleted = true;
        $productChange->save();
        $newProductChange = $productChange->replicate();
        $newProductChange->desc = 'حذف رکورد';
        $newProductChange->change = -$productChange->change;
        $newProductChange->quantity = $product->quantity;
        $newProductChange->save();
        return redirect('/productQuantity/add/' . $product->id);
    }

    public function productAlarm()
    {
        $products = Product::all();
        $message = 'لیست محصولات با موجودی کم:/n';
        foreach ($products as $product) {
            if ($product->available && ($product->alarm > $product->quantity)) {
                $message .= $product->name . ' -> ' . $product->quantity . '/n';
            }
            $content = array( "text" => $message);
            $chatId = '';
            $this->sendMessageToBale($content, $chatId);

        }
    }
}