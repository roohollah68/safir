<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponLink;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function couponList()
    {
        return view('coupons', [
            'coupons' => Coupon::with('couponLinks.good')->get(),
            'users' => User::all()->keyBy('id'),
            'products' => Product::where('warehouse_id', 1)->get()->keyBy('id')
        ]);
    }

    public function newForm()
    {
        return view('addEditCoupon', [
            'coupon' => false,
            'users' => User::where('role','user')->get(),
            'products' => Product::where('warehouse_id', 1)->whereHas('good', function (Builder $query) {
                $query->where('category', 'final');
            })->get(),
        ]);
    }

    public function storeNew(Request $req)
    {
        request()->validate([
            'percent' => 'required|numeric|min:0|max:99'
        ]);

        $coupon = Coupon::create([
            'percent' => $req->percent
        ]);

        $users = User::all();
        $products = Product::where('warehouse_id', 1)->get();
        foreach ($users as $user) {
            foreach ($products as $product) {
                if ($req['user_' . $user->id] && $req['product_' . $product->id]) {
                    $coupon->couponLinks()->create([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ]);
                }
            }
        }

        return redirect(route('addCoupon'));
    }

    public function editForm($id)
    {
        return view('addEditCoupon', [
            'coupon' => Coupon::with('couponLinks')->find($id),
            'users' => User::all(),
            'products' => Product::where('warehouse_id', 1)->get()
        ]);
    }

    public function update(Request $req, $id)
    {
        request()->validate([
            'percent' => 'required|numeric|min:0|max:99'
        ]);
        $coupon = Coupon::find($id);
        $coupon->update([
            'percent' => $req->percent
        ]);

        CouponLink::where('coupon_id', $id)->delete();

        $users = User::all();
        $products = Product::where('warehouse_id', 1)->get();
        foreach ($users as $user) {
            foreach ($products as $product) {
                if ($req['user_' . $user->id] && $req['product_' . $product->id]) {
                    $coupon->couponLinks()->create([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ]);
                }
            }
        }

        return redirect(route('couponList'));
    }

    public function deleteCoupon($id)
    {
        Coupon::destroy($id);
        CouponLink::where('coupon_id', $id)->delete();
    }
}
