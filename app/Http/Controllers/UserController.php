<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        if ($this->isAdmin())
            return view('userList', ['users' => User::all()]);
        else
            abort(404);

    }

    public function delete($id)
    {
        if ($this->isAdmin()){
            User::where('id', $id)->where('role', 'user')->where('verified', false)->delete();
            return redirect()->route('manageUsers');
        }
        else
            abort(404);

    }

    public function confirm($id)
    {
        if ($this->isAdmin()){
            User::find($id)->update(['verified' => true]);
            return redirect()->route('manageUsers');
        }
        else
            abort(404);

    }

    public function suspend($id)
    {
        if ($this->isAdmin()){
            User::where('id', $id)->where('role', 'user')->update(['verified' => false]);
            return redirect()->route('manageUsers');
        }
        else
            abort(404);

    }

    public function edit($id = null)
    {
        if ($this->isAdmin())
            return view('editUser', [ 'user' => User::find($id)]);
        else
            return view('editUser', [ 'user' => auth()->user()]);
    }

    public function update( Request $request ,$id = null)
    {
        if (!$this->isAdmin())
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
        if (auth()->user()->role != 'admin')
            return redirect()->route('listOrders');
        return redirect()->route('manageUsers');
    }
}
