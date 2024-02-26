<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        return view('userList', ['users' => User::all()]);
    }


    public function confirm($id)
    {
        User::find($id)->update(['verified' => true]);
        return redirect()->route('manageUsers');
    }

    public function suspend($id)
    {
        User::where('id', $id)->where('role', 'user')->update(['verified' => false]);
        return redirect()->route('manageUsers');
    }

    public function edit($id = null)
    {
        if ($this->superAdmin())
            return view('editUser', ['user' => User::find($id)]);
        else
            return view('editUser', ['user' => auth()->user()]);
    }

    public function update(Request $request, $id = null)
    {
        if (!$this->superAdmin())
            $id = auth()->user()->id;
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|min:5',
            'phone' => 'required|string|max:11|min:11',
        ]);
        $request->phone = $this->number_Fa_En($request->phone);

        User::find($id)->update([
            'name' => $request->name,
            'username' => $request->username,
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
        if ($this->superAdmin())
            return redirect()->route('manageUsers');
        return redirect()->route('listOrders');
    }
}
