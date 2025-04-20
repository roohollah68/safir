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
    public function list()
    {
        Helper::access('usersEdit');
        return view('userList', ['users' => User::where('id', '<>', 122)->get()]);
    }

    public function confirm($id)
    {
        Helper::access('usersEdit');
        User::find($id)->update(['verified' => true]);
        return redirect()->route('usersList');
    }

    public function suspend($id)
    {
        Helper::access('usersEdit');
        User::where('id', $id)->update(['verified' => false]);
        return redirect()->route('usersList');

    }

    public function delete($id)
    {
        Helper::access('usersEdit');
        User::where('id', $id)->delete();
        Customer::where('user_id', $id)->update([
            'user_id' => 6,
        ]);
        Order::where('user_id', $id)->update([
            'user_id' => 6,
        ]);
        return redirect()->route('usersList');

    }

    public function add()
    {
        Helper::access('usersEdit');
        $user = new User();
        return view('addEditUser', [
            'user' => $user,
            'edit' => false,
        ]);
    }

    public function edit($id = null)
    {
        $user = auth()->user();
        if ($user->meta('usersEdit'))
            $user = User::find($id);
        return view('addEditUser', [
            'user' => $user,
            'edit' => true,
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function insertUser(Request $req)
    {
        Helper::access('usersEdit');
        $req->merge(['credit' => str_replace(",", "", $req->credit)]);
        $req->validate([
            'name' => 'required|string|max:255|unique:users',
            'username' => 'required|unique:users|string|max:255|min:5',
            'phone' => 'required|string|max:11|min:11',
            'password' => 'required|string|min:8',
        ]);
        $req->phone = Helper::number_Fa_En($req->phone);

        User::create($req->merge([
            'password' => Hash::make($req->password),
            'verified' => true,
        ])->all());
        return redirect()->route('listOrders');
    }

    public function update(Request $request, $id = null)
    {
        $user = auth()->user();
        $request->merge(['credit' => str_replace(",", "", $request->credit)]);
        if (!$user->meta('usersEdit'))
            $id = $user->id;
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name,' . $id,
            'username' => 'required|string|max:255|min:5|unique:users,username,' . $id,
            'phone' => 'required|string|max:11|min:11',
            'NuRecords' => 'integer|min:1',
            'credit' => 'numeric',
        ]);
        $request->phone = Helper::number_Fa_En($request->phone);

        User::find($id)->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'credit' => $request->credit,
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

        UserMeta::updateOrCreate(
            ['user_id' => $id, 'name' => 'discount'],
            ['value' => $request->discount]
        );

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
            return redirect()->route('usersList');
        }
        return redirect()->route('listOrders');
    }

    public function accesslist(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'permission' => 'required|string',
                'value' => 'required|boolean',
            ]);

            UserMeta::updateOrCreate(
                ['user_id' => $request->user_id, 'name' => $request->permission],
                ['value' => $request->value]
            );

            return response()->json(['message' => 'Permission updated successfully.']);
        }

        $users = User::all();
        return view('accessList', ['users' => $users]);
    }

    public function accesslist2(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'permission' => 'required|string',
                'value' => 'required|boolean',
            ]);

            UserMeta::updateOrCreate(
                ['user_id' => $request->user_id, 'name' => $request->permission],
                ['value' => $request->value]
            );

            return response()->json(['message' => 'Permission updated successfully.']);
        }

        $users = User::all();
        return view('accessList2', ['users' => $users]);
    }

    public function changeAcount($id)
    {
        Helper::access('usersEdit');
        auth()->logout();
        auth()->login(User::find($id));
        return redirect()->route('');
    }
}
