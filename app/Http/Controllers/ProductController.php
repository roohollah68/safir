<?php

namespace App\Http\Controllers;

use App\Models\Good;
use App\Models\Order;
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
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        return view('product.productList', [
            'warehouses' => Warehouse::all(),
            'goods' => Good::all()->keyBy('id'),
        ]);
    }

    public function getData(Request $req)
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        $products = Product::where('warehouse_id', $req->warehouseId)->with('good')->get()->keyBy('id');
        return $products;

    }

    public function showAddForm()
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        $good = new Good();
        return view('product.addEditProduct', [
            'good' => $good,
            'edit' => false,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function showEditForm($id)
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        $product = Product::with('good')->findOrfail($id);
        return view('product.addEditProduct', [
            'product' => $product,
            'good' => $product->good,
            'edit' => true,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function storeNew(Request $req)
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        $req->price = +str_replace(",", "", $req->price);
        $req->PPrice = +str_replace(",", "", $req->PPrice);
        request()->validate([
            'name' => 'required|unique:goods|string|max:255|min:4',
            'price' => 'required',
        ]);

        Good::create([
            'name' => $req->name,
            'price' => $req->price,
            'productPrice' => $req->PPrice,
            'category' => $req->category,
        ]);

        return redirect()->route('productList');
    }

    public function editProduct(Request $req, $id)
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
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
        $product->save();
        $good->save();

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
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        if (Product::find($id)->delete())
            return 'ok';
        else
            return 'error';

    }

    public function deletePhoto($id)
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        Good::find($id)->update([
            'photo' => ''
        ]);
    }

    public function addToProducts($id, Request $req)
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        $product = Product::where('good_id', $id)->where('warehouse_id', $req->warehouseId)->first();
        if ($product) {
            abort(403);
        }
        $product = Product::withTrashed()->where('good_id', $id)->where('warehouse_id', $req->warehouseId)->first();
        if ($product) {
            $product->restore();
        } else {
            $product = Product::create([
                'good_id' => $id,
                'available' => false,
                'warehouse_id' => $req->warehouseId,
                'quantity' => 0
            ]);
        }

        return $product;
    }

    public function transfer()
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        $products = Product::with('good')->get()->keyby('id');
        return view('product.transfer', [
            'warehouses' => Warehouse::all()->keyBy('id'),
            'products' => $products,
        ]);
    }

    public function transferSave(Request $req)
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        DB::beginTransaction();
        $products1 = Product::where('warehouse_id', $req->warehouseId1)->get()->keyBy('id');
        $warehouses = Warehouse::all()->keyBy('id');
        $hasProduct = false;
        $productList = [];
        $orders = '';
        foreach ($products1 as $id => $product) {
            if ($req[$id] > 0) {
                $productList[$id] = $product;
                $hasProduct = true;
                $orders .= ' ' . $product->name . ' ' . +$req[$id] . 'عدد' . '،';
            }
        }
        if (!$hasProduct)
            return $this->errorBack('محصولی انتخاب نشده است!');

        $order = auth()->user()->orders()->create([
            'name' => 'انتقال بین انبارها',
            'phone' => 123456789,
            'address' => 'انبار ' . $warehouses[$req->warehouseId2]->name,
            'zip_code' => 123456789,
            'orders' => $orders,
            'desc' => "انتقال از انبار {$warehouses[$req->warehouseId1]->name} به انبار {$warehouses[$req->warehouseId2]->name} ",
            'receipt' => null,
            'total' => 0,
            'customerCost' => 0,
            'paymentMethod' => null,
            'deliveryMethod' => null,
            'customer_id' => null,
            'confirm' => true,
            'state' => 10,
            'counter' => 'approved',
            'warehouse_id' => +$req->warehouseId1,
        ]);
        app('Telegram')->sendOrderToBale($order, env('GroupId'));

        $order2 = auth()->user()->orders()->create([
            'name' => 'انتقال بین انبارها',
            'phone' => 123456789,
            'address' => 'انبار ' . $warehouses[$req->warehouseId2]->name,
            'zip_code' => 123456789,
            'orders' => $orders,
            'desc' => "انتقال از انبار {$warehouses[$req->warehouseId1]->name} به انبار {$warehouses[$req->warehouseId2]->name} ",
            'receipt' => null,
            'total' => 0,
            'customerCost' => 0,
            'paymentMethod' => null,
            'deliveryMethod' => null,
            'customer_id' => null,
            'confirm' => true,
            'state' => 10,
            'counter' => 'approved',
            'warehouse_id' => +$req->warehouseId2,
        ]);
        app('Telegram')->sendOrderToBale($order2, env('GroupId'));

        foreach ($productList as $id => $product) {
            $order->orderProducts()->create([
                'product_id' => $id,
                'name' => $product->name,
                'number' => -$req[$id],
                'price' => 0,
            ]);
            $product2 = Product::where('warehouse_id', $req->warehouseId2)->where('good_id', $product->good_id)->first();
            $order2->orderProducts()->create([
                'product_id' => $product2->id,
                'name' => $product2->name,
                'number' => $req[$id],
                'price' => 0,
            ]);
            $product->update([
                'quantity' => $product->quantity - $req[$id],
            ]);
            $product2->update([
                'quantity' => $product2->quantity + $req[$id],
            ]);
            $product->productChange()->create([
                'change' => -$req[$id],
                'desc' => 'انتقال به انبار ' . $warehouses[$req->warehouseId2]->name,
                'quantity' => $product->quantity
            ]);
            $product2->productChange()->create([
                'change' => $req[$id],
                'desc' => 'انتقال از انبار ' . $warehouses[$req->warehouseId1]->name,
                'quantity' => $product2->quantity
            ]);
        }
        DB::commit();
        return redirect()->route('productList');
    }

    public function goods()
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        $goods = Good::with('products')->get()->keyBy('id');
        return view('product.goodsManagement', [
            'goods' => $goods,
            'warehouses' => Warehouse::all()->keyBy('id'),
        ]);
    }

    public function changeAvailable($id)
    {
        if (!auth()->user()->meta('warehouse'))
            abort(401);
        $product = Product::findOrFail($id);
        $product->update([
            'available' => !$product->available,
        ]);
        return $product;
    }

    public function fastEdit($id)
    {
        $product = Product::find($id);
        return view('product.productFastEdit', [
            'product' => $product,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function production($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $products = Product::where('warehouse_id', $id)->where('available', true)
            ->whereRaw('products.quantity < products.alarm')->with('good')->get()->keyBy('id');
        return view('product.productionPlan', [
            'products' => $products,
            'ware' => $warehouse,
        ]);
    }
}
