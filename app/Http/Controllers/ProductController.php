<?php

namespace App\Http\Controllers;

use App\Models\Good;
use App\Models\Product;
use App\Models\ProductChange;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function showProducts()
    {

        return view('productList', [
            'warehouses' => Warehouse::all(),
            'goods' => Good::all()->keyBy('id'),
        ]);
    }

    public function getData(Request $req)
    {
        $a = time();
        $products = Product::where('warehouse_id', $req->warehouseId)->
        select('id', 'good_id', 'available', 'warehouse_id', 'quantity', 'alarm', 'high_alarm');
//        $goods = Good::where('id','>=', 0);
//        if (!$req->available) {
//            $products = $products->where('available', false);
//        }
//        if (!$req->unavailable) {
//            $products = $products->where('available', true);
//        }
//        if (!$req->final) {
////            $goods = $goods->where('category','<>', 'final');
//            $products = $products->whereHas('good', function (Builder $query) {
//                $query->where('category','<>', 'final');
//            });
//        }
//        if (!$req->raw) {
////            $goods = $goods->where('category','<>', 'raw');
//            $products = $products->whereHas('good', function (Builder $query) {
//                $query->where('category','<>', 'raw');
//            });
//        }
//        if (!$req->pack) {
////            $goods = $goods->where('category','<>', 'pack');
//            $products = $products->whereHas('good', function (Builder $query) {
//                $query->where('category','<>', 'pack');
//            });
//        }
//        if (!$req->low) {
//            $products = $products->whereRaw('products.alarm < products.quantity');
//        }
//        if (!$req->high) {
//            $products = $products->whereRaw('products.high_alarm > products.quantity');
//        }
//        if(!$req->normal){
//            $products = $products->whereRaw('products.high_alarm < products.quantity')->
//            whereRaw('products.alarm > products.quantity');
//        }
        $products = $products->get()->keyBy('id');
//        $goods = $goods->get()->keyBy('id');

        return $products;

    }

    public function showAddForm()
    {
        $good = new Good();
        return view('addEditProduct', [
            'good' => $good,
            'edit' => false,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function showEditForm($id)
    {
        $product = Product::with('good')->findOrfail($id);
        return view('addEditProduct', [
            'product' => $product,
            'good' => $product->good,
            'edit' => true,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function storeNew(Request $req)
    {
        $req->price = +str_replace(",", "", $req->price);
        $req->PPrice = +str_replace(",", "", $req->PPrice);
        request()->validate([
//            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|unique:goods|string|max:255|min:4',
            'price' => 'required',
        ]);
//        $photo = '';
//        if ($req->file("photo")) {
//            $photo = $req->file("photo")->store("", 'p-photo');
//        }
//        $available = $req->available == 'true';

//        $product = Product::where('name', $req->name)->where('location', $req->location)->first();
//        if ($product) {
//            return $this->errorBack('این محصول تکراری است.');
//        } else
        $good = Good::create([
            'name' => $req->name,
            'price' => $req->price,
            'productPrice' => $req->PPrice,
//                'available' => $available,
//                'photo' => $photo,
            'category' => $req->category,
//                'location' => $req->location,
//                'quantity' => $req->quantity,
//                'alarm' => $req->alarm,
//                'high_alarm' => $req->high_alarm,
        ]);
//        if ($req->quantity > 0) {
//            $product->productChange()->create([
//                'change' => $product->quantity,
//                'quantity' => $product->quantity,
//                'desc' => 'مقدار اولیه',
//            ]);
//        }
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
        $product = Product::with('good')->findOrFail($id);
        $good = $product->good;
        $productChange = new ProductChange();
        $productChange->product_id = $product->id;
//        if (!$product->photo)
//            $product->photo = '';
//        if ($req->file("photo")) {
//            $product->photo = $req->file("photo")->store("", 'p-photo');
//        }
        $good->name = $req->name;
        $good->price = $req->price;
        $good->productPrice = $req->PPrice;
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
        $good->category = $req->category;
        if ($product->warehouse_id != $req->warehouseId) {
            if (Product::where('good_id', $good->id)->where('warehouse_id', $req->warehouseId)->first()) {
                return $this->errorBack('محصول در انبار مقصد تعریف شده است!');
            }
        }
        $product->save();
        $good->save();
//        Product::where('name', $product->name)->update([
//            'price' => $product->price,
//            'productPrice' => $product->productPrice,
//            'category' => $product->category,
//        ]);
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

    public function addToProducts($id, Request $req)
    {
        $product = Product::where('good_id', $id)->where('warehouse_id', $req->warehouseId)->first();
        if ($product) {
            abort(403);
        }
        $product = Product::create([
            'good_id' => $id,
            'available' => false,
            'warehouse_id' => $req->warehouseId,
            'quantity' => 0
        ]);
        return $product;
    }

    public function transfer($id)
    {
        $product = Product::with('warehouse')->findOrFail($id);
        $products = Product::where('good_id', $product->good_id)->where('id', '<>', $id)->with('warehouse')->get();
        return view('transfer', [
            'warehouses' => Warehouse::all(),
            'product' => $product,
            'products' => $products,
            'warehouse2' => $product->warehouse,
        ]);
    }

    public function transferSave($id, Request $req)
    {
        DB::beginTransaction();
        $product = Product::with('warehouse')->findOrFail($id);
        $product2 = Product::with('warehouse')->findOrFail($req->productId);
        $transfer = $req->value;
        if ($transfer <= 0)
            return;
        $product->update([
            'quantity' => $product->quantity - $transfer,
        ]);
        $product->productChange()->create([
            'change' => - $transfer,
            'desc' => 'انتقال به انبار '.$product2->warehouse->name,
            'quantity' => $product->quantity
        ]);
        $product2->update([
            'quantity' => $product2->quantity + $transfer,
        ]);
        $product2->productChange()->create([
            'change' => $transfer,
            'desc' => 'انتقال از انبار '.$product->warehouse->name,
            'quantity' => $product2->quantity
        ]);
        DB::commit();
        return $product;
    }

}
