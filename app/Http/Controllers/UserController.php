<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function home()
    {
        return redirect()->route('listOrders');
    }

    public function show()
    {
        Helper::access('usersEdit');
        return view('userList', ['users' => User::where('id' , '<>' , 122)->get()]);
    }

    public function confirm($id)
    {
        Helper::access('usersEdit');
        User::find($id)->update(['verified' => true]);
        return redirect()->route('manageUsers');
    }

    public function suspend($id)
    {
        Helper::access('usersEdit');
        User::where('id', $id)->update(['verified' => false]);
        return redirect()->route('manageUsers');

    }

    public function delete($id)
    {
        Helper::access('usersEdit');
        User::where('id', $id)->delete();
        Customer::where('user_id' , $id)->update([
            'user_id' => 6,
        ]);
        Order::where('user_id' , $id)->update([
            'user_id' => 6,
        ]);
        return redirect()->route('manageUsers');

    }

    public function addUser()
    {
        Helper::access('usersEdit');
        $user = new User();
        return view('editUser', [
            'user' => $user,
            'edit' => false,
        ]);
    }

    public function edit($id = null)
    {
        $warehouses = Warehouse::all();
        if (auth()->user()->meta('usersEdit') && $id)
            return view('editUser', [
                'user' => User::find($id),
                'edit' => true,
                'warehouses' => $warehouses,
            ]);
        else
            return view('editUser', [
                'user' => auth()->user(),
                'edit' => true,
                'warehouses' => $warehouses,
            ]);
    }

    public function insertUser(Request $request)
    {
        Helper::access('usersEdit');
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|min:5',
            'phone' => 'required|string|max:11|min:11',
            'password' => 'required|string|min:8',
        ]);
        $request->phone = Helper::number_Fa_En($request->phone);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'username' => $request->username,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'verified' => true,
        ]);

        return redirect()->route('listOrders');
    }

    public function update(Request $request, $id = null)
    {
        if (!auth()->user()->meta('usersEdit'))
            $id = auth()->user()->id;
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|min:5',
            'phone' => 'required|string|max:11|min:11',
            'NuRecords' => 'integer|min:1|max:3000'
        ]);
        $request->phone = Helper::number_Fa_En($request->phone);

        User::find($id)->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        if ($request->password) {
            $request->validate([
                'password' => 'required|string|min:8',
            ]);
            User::find($id)->update([
                'password' => Hash::make($request->password),
            ]);
        }

        if ($request->NuRecords) {
            UserMeta::updateOrCreate(
                ['user_id' => $id, 'name' => 'NuRecords'],
                ['value' => $request->NuRecords]
            );
        }

        if ($request->warehouseId) {
            UserMeta::updateOrCreate(
                ['user_id' => $id, 'name' => 'warehouseId'],
                ['value' => $request->warehouseId]
            );
        }

        if (auth()->user()->meta('usersEdit')) {
            User::find($id)->update([
                'username' => $request->username,
                'role' => $request->role,
            ]);
            foreach (config('userMeta.access') as $access => $desc) {
                UserMeta::updateOrCreate(
                    ['user_id' => $id, 'name' => $access],
                    ['value' => $request[$access]]
                );
            }
            return redirect()->route('manageUsers');
        }
        return redirect()->route('listOrders');
    }
}
