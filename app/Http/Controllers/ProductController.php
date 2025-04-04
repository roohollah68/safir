<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Good;
use App\Models\Keysungood;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function showProducts()
    {
        Helper::access('warehouse');
        return view('product.productList', [
            'warehouses' => Warehouse::all(),
            'goods' => Good::all()->keyBy('id'),
        ]);
    }

    public function getData(Request $req)
    {
        Helper::access('warehouse');
        $products = Product::where('warehouse_id', $req->warehouseId)->with('good.formulations')->get()->keyBy('id');
        return $products;

    }

    public function showAddForm()
    {
        Helper::access('editWarehouse');
        $good = new Good();
        return view('product.addEditProduct', [
            'good' => $good,
            'edit' => false,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function showEditForm($id)
    {
        Helper::access('warehouse');
        $product = Product::with(['good', 'productChange.order'])->findOrfail($id);
        echo view('product.addEditProduct', [
            'product' => $product,
            'good' => $product->good,
            'edit' => true,
            'warehouses' => Warehouse::all()->keyBy('id'),
        ]);
    }

    public function storeNew(Request $req, $id = null)
    {
        Helper::access('editWarehouse');
        DB::beginTransaction();
        $req->merge(['price' => str_replace(",", "", $req->price)]);
        $req->merge(['productPrice' => str_replace(",", "", $req->productPrice)]);
        if ($id) {
            $product = Product::findOrFail($id)->fill([
                'alarm' => $req->alarm,
                'high_alarm' => $req->high_alarm,
                'available' => ($req->available == 'true'),
            ]);
            $productChange = $product->productChange()->make();
            if (+$req->value == +$product->quantity)
                $productChange->desc = 'اضافه کردن موجودی به اندازه ' . $req->add . ' عدد';
            else
                $productChange->desc = 'اصلاح موجودی به مقدار' . $req->value . ' عدد';
            $change = (+$req->value + $req->add) - $product->quantity;
            $product->quantity += $change;
            $productChange->change = $change;
            $productChange->quantity = $product->quantity;
            $product->save();
            if ($change != 0)
                $productChange->save();
            $good = $product->good;
        } else
            $good = new Good();
        request()->validate([
            'name' => 'required|string|max:255|min:4|unique:goods,name,' . $good->id,
            'price' => 'required|integer',
            'productPrice' => 'integer',
        ]);
        $good->fill($req->all())->save();
        $good->goodMeta()->firstOrNew([
            'supplier_inf' => $req->supplier_inf,
        ]);

        DB::commit();
        if ($req->fast)
            return ['با موفقیت ذخیره شد.', $product];
        else
            return redirect()->route('productList');
    }

    public function deleteProduct($id)
    {
        Helper::access('editWarehouse');
        if (Product::find($id)->delete())
            return 'ok';
        else
            return 'error';

    }

    public function deletePhoto($id)
    {
        Helper::access('editWarehouse');
        Good::find($id)->update([
            'photo' => ''
        ]);
    }

    public function addToProducts($id, Request $req)
    {
        Helper::access('editWarehouse');
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
        Helper::access('editWarehouse');
        $products = Product::with('good')->get()->keyby('id');
        return view('product.transfer', [
            'warehouses' => Warehouse::all()->keyBy('id'),
            'products' => $products,
        ]);
    }

    public function transferSave(Request $req)
    {
        Helper::access('editWarehouse');
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
        Helper::access('warehouse');
        $goods = Good::with('products')->get()->keyBy('id');
        return view('product.goodsManagement', [
            'goods' => $goods,
            'warehouses' => Warehouse::all()->keyBy('id'),
        ]);
    }

    public function changeAvailable($id)
    {
        Helper::access('editWarehouse');
        $product = Product::findOrFail($id);
        $product->update([
            'available' => !$product->available,
        ]);
        return $product;
    }

    public function fastEdit($id)
    {
        Helper::access('editWarehouse');
        $product = Product::with('good')->find($id);
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

    public function warehouseManager()
    {
        Helper::access('warehouse');
        return view('product.warehouseManager', [
            'warehouses' => Warehouse::all()->keyBy('id'),
            'users' => User::where('verified', true)->get()->keyBy('id'),
        ]);
    }

    public function saveWarehouseManager(Request $req)
    {
        Helper::access('editWarehouse');
        $warehouses = Warehouse::all()->keyBy('id');
        $users = User::where('verified', true)->get()->keyBy('id');
        foreach ($warehouses as $id => $warehouse) {
            $warehouse->user_id = null;
            if ($req['user-' . $id]) {
                if ($users[$req['user-' . $id]]) {
                    $warehouse->user_id = $req['user-' . $id];
                }
            }
            $warehouse->save();
        }
        return redirect('/warehouse/manager');
    }

    public function tags()
    {
        Helper::access('warehouse');
        $goods = Good::whereIn('category', ['final', 'other'])->get()->keyBy('id');
        $keysungoods = Keysungood::all()->keyBy('good_id');
        return view('product.tagManagement', [
            'goods' => $goods,
            'keysungoods' => $keysungoods,
        ]);
    }

    public function saveTags(Request $req, $id)
    {
        Helper::access('editWarehouse');
        if (is_string($req->tag) && $req->tag == 0)
            $req->merge(['tag' => '0000000000000']);

        Helper::access('warehouse');
        $good = Good::findOrFail($id);
        $req->validate([
            'tag' => 'numeric|digits:13|nullable',
            'vat' => 'boolean',
            'isic' => 'numeric|digits:7|nullable'
        ], [
            'tag.numeric' => 'شناسه کالا باید عدد باشد',
            'tag.digits' => 'شناسه کالا باید عدد 13 رقمی باشد',
        ]);
        $good->update($req->all());
        return $good;
    }

    public function deleteGood($id)
    {
        Helper::access('editWarehouse');
        DB::beginTransaction();
        Helper::access('warehouse');
        $good = Good::findOrFail($id);
        $products = $good->products();
        $products->delete();
        $good->delete();
        DB::commit();
        return 'ok';
    }
}
