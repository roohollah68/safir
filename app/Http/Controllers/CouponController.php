<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponLink;
use App\Models\Good;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function couponList()
    {
        return view('coupons', [
            'coupons' => Coupon::with(['couponLinks.good', 'couponLinks.user'])->get(),
            'users' => User::all()->keyBy('id'),
        ]);
    }

    public function newForm()
    {
        return view('addEditCoupon', [
            'coupon' => new Coupon(),
            'users' => User::where('role', 'user')->get(),
            'goods' => Good::whereIn('category', ['final', 'other'])->get(),
        ]);
    }

    public function storeNew(Request $req)
    {
        DB::beginTransaction();
        request()->validate([
            'percent' => 'required|numeric|min:0|max:99'
        ]);
        $coupon = new Coupon();
        $coupon->percent = $req->percent;
        $coupon->save();
        $users = User::where('role', 'user')->get();
        $goods = Good::whereIn('category', ['final', 'other'])->get();
        foreach ($users as $user) {
            foreach ($goods as $good) {
                if ($req['user_' . $user->id] && $req['good_' . $good->id]) {
                    $coupon->couponLinks()->create([
                        'user_id' => $user->id,
                        'good_id' => $good->id,
                    ]);
                }
            }
        }
        DB::commit();
        return redirect(route('couponList'));
    }

    public function editForm($id)
    {
        return view('addEditCoupon', [
            'coupon' => Coupon::with(['couponLinks.good', 'couponLinks.user'])->find($id),
            'users' => User::where('role', 'user')->get(),
            'goods' => Good::whereIn('category', ['final', 'other'])->get(),
        ]);
    }

    public function update(Request $req, $id)
    {
        DB::beginTransaction();
        request()->validate([
            'percent' => 'required|numeric|min:0|max:99'
        ]);
        $coupon = Coupon::find($id);
        $coupon->percent = $req->percent;
        $coupon->save();

        CouponLink::where('coupon_id', $id)->delete();

        $users = User::where('role', 'user')->get();
        $goods = Good::whereIn('category', ['final', 'other'])->get();
        foreach ($users as $user) {
            foreach ($goods as $good) {
                if ($req['user_' . $user->id] && $req['good_' . $good->id]) {
                    $coupon->couponLinks()->create([
                        'user_id' => $user->id,
                        'good_id' => $good->id,
                    ]);
                }
            }
        }
        DB::commit();
        return redirect(route('couponList'));
    }

    public function deleteCoupon($id)
    {
        Coupon::destroy($id);
        CouponLink::where('coupon_id', $id)->delete();
    }
}
