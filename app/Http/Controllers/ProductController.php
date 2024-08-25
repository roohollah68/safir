<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function showProducts(Request $req)
    {
        $city = $req->city ?: 't';
//        $products = Product::where('location', $city)->get()->keyBy('id');
        $products = Product::all()->keyBy('id');
        return view('productList', [
            'products' => $products,
            'location' => $city,
        ]);
    }

    public function showAddForm()
    {
        $product = new Product();
        return view('addEditProduct', [
            'product' => $product,
            'edit' => false,
        ]);
    }

    public function showEditForm($id)
    {
        $product = Product::findOrfail($id);
        return view('addEditProduct', [
            'product' => $product,
            'edit' => true,
        ]);
    }

    public function storeNew(Request $req)
    {
        $req->price = +str_replace(",", "", $req->price);
        $req->PPrice = +str_replace(",", "", $req->PPrice);
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|max:255|min:4',
            'price' => 'required',
        ]);
        $photo = '';
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", 'p-photo');
        }
        $available = $req->available == 'true';

        $product = Product::where('name', $req->name)->where('location', $req->location)->first();
        if ($product) {
            return $this->errorBack('این محصول تکراری است.');
        } else
            $product = Product::create([
                'name' => $req->name,
                'price' => $req->price,
                'productPrice' => $req->PPrice,
                'available' => $available,
                'photo' => $photo,
                'category' => $req->category,
                'location' => $req->location,
                'quantity' => $req->quantity,
                'alarm' => $req->alarm,
                'high_alarm' => $req->high_alarm,
            ]);
        if ($req->quantity > 0) {
            $product->productChange()->create([
                'change' => $product->quantity,
                'quantity' => $product->quantity,
                'desc' => 'مقدار اولیه',
            ]);
        }
        return redirect()->route('productList');
    }

    public function editProduct(Request $req, $id)
    {
        DB::beginTransaction();
        $req->price = str_replace(",", "", $req->price);
        $req->PPrice = +str_replace(",", "", $req->PPrice);
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|max:255|min:4',
            'price' => 'required',
        ]);
        $product = Product::findOrFail($id);
        $productChange = new ProductChange();
        $productChange->product_id = $product->id;
//        if (!$product->photo)
//            $product->photo = '';
//        if ($req->file("photo")) {
//            $product->photo = $req->file("photo")->store("", 'p-photo');
//        }
        $product->name = $req->name;
        $product->price = $req->price;
        $product->productPrice = $req->PPrice;
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
        $product->high_alarm = $req->high_alarm;
        $product->available = ($req->available == 'true');
        $product->category = $req->category;
        $product->location = $req->location;
        $product->save();
        Product::where('name', $product->name)->update([
            'price' => $product->price,
            'productPrice' => $product->productPrice,
            'category' => $product->category,
        ]);
        if ($productChange->change != 0)
            $productChange->save();
        DB::commit();
        if ($req->fast)
            return ['با موفقیت ذخیره شد.', $product];
        else
            return redirect('/product/edit/' . $id);
    }

    public function deleteProduct($id)
    {
        if (Product::find($id)->delete())
            return 'ok';
        else
            return 'error';

    }

    public function deletePhoto($id)
    {
        Product::find($id)->update([
            'photo' => ''
        ]);
    }

}
