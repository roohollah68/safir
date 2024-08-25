<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMeta;
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
        User::where('id', $id)->update(['verified' => false]);
        return redirect()->route('manageUsers');
    }

    public function addUser()
    {
        $user = new User();
        return view('editUser', [
            'user' => $user,
            'edit' => false,
        ]);
    }

    public function edit($id = null)
    {
        if ($this->superAdmin() && $id)
            return view('editUser', [
                'user' => User::find($id),
                'edit' => true,
            ]);
        else
            return view('editUser', [
                'user' => auth()->user(),
                'edit' => true,
            ]);
    }

    public function insertUser(Request $request)
    {
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
        if (!$this->superAdmin())
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
                ['user_id'=>$id , 'name'=>'NuRecords'],
                ['value' => $request->NuRecords]
            );
        }

        if ($request->city) {
            UserMeta::updateOrCreate(
                ['user_id'=>$id , 'name'=>'city'],
                ['value' => $request->city]
            );
        }

        if ($this->superAdmin()) {
            User::find($id)->update([
                'username' => $request->username,
                'role' => $request->role,
            ]);
            return redirect()->route('manageUsers');
        }
        return redirect()->route('listOrders');
    }
}
