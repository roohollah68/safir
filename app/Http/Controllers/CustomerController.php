<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function customersList()
    {
        $customers = auth()->user()->customers()->get();
        return view('customerList' , ['customers'=> $customers]);
    }

    public function addForm()
    {
        return view('addEditCustomer', ['customer' => false]);
    }

    public function storeNewCustomer(Request $request)
    {
        request()->validate([
            'name' => 'required|string|min:3',
            'phone' => 'required|string|max:11|min:11',
            'address' => 'required|string',
        ]);
        $request->phone = $this->number_Fa_En($request->phone);
        $request->zip_code = $this->number_Fa_En($request->zip_code);


//      check duplicate name
        $customer = auth()->user()->customers()->where('name',$request->name);
        if($customer->count()){
            $customer->first()->update([
                'phone' => $request->phone,
                'address' => $request->address,
                'zip_code' => $request->zip_code,
            ]);
        }else{
            auth()->user()->customers()->create([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'zip_code' => $request->zip_code,
            ]);
        }
        return redirect()->route('CustomerList');
    }

    public function deleteCustomer($id)
    {
        auth()->user()->customers()->find($id)->delete();
    }

    public function showEditForm($id)
    {
        $customer = auth()->user()->customers()->find($id);
        return view('addEditCustomer', ['customer' => $customer]);
    }

    public function updateCustomer($id , Request $request)
    {
        request()->validate([
            'name' => 'required|string|min:3',
            'phone' => 'required|string|max:11|min:11',
            'address' => 'required|string',
        ]);

        $request->phone = $this->number_Fa_En($request->phone);
        $request->zip_code = $this->number_Fa_En($request->zip_code);

        auth()->user()->customers()->find($id)->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
        ]);
        return redirect()->route('CustomerList');
    }
}
