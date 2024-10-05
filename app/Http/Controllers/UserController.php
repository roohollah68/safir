<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMeta;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function home()
    {
        if (auth()->user()->role == 'warehouse')
            return redirect()->route('productList');
        return redirect()->route('listOrders');
    }

    public function show()
    {
        if (!auth()->user()->meta('usersEdit'))
            abort(401);
        return view('userList', ['users' => User::all()]);
    }

    public function confirm($id)
    {
        if (!auth()->user()->meta('usersEdit'))
            abort(401);
        User::find($id)->update(['verified' => true]);
        return redirect()->route('manageUsers');
    }

    public function suspend($id)
    {
        if (!auth()->user()->meta('usersEdit'))
            abort(401);
        User::where('id', $id)->update(['verified' => false]);
        return redirect()->route('manageUsers');

    }

    public function delete($id)
    {
        if (!auth()->user()->meta('usersEdit'))
            abort(401);
        User::where('id', $id)->delete();
        return redirect()->route('manageUsers');

    }

    public function addUser()
    {
        if (!auth()->user()->meta('usersEdit'))
            abort(401);
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
        if (!auth()->user()->meta('usersEdit'))
            abort(401);
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|min:5',
            'phone' => 'required|string|max:11|min:11',
            'password' => 'required|string|min:8',
        ]);
        $request->phone = $this->number_Fa_En($request->phone);

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
        $request->phone = $this->number_Fa_En($request->phone);

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
            return redirect()->route('manageUsers');
        }
        return redirect()->route('listOrders');
    }
}
