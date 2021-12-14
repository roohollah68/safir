<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function showProducts()
    {
        $products = Product::all();
        return view('productList' , ['products'=>$products]);
    }

    public function showAddForm(){
        return view('addEditProduct' , ['product'=>false]);
    }

    public function showEditForm($id){
        $product = Product::findOrfail($id);
        return view('addEditProduct' , ['product'=>$product]);
    }

    public function storeNew(Request $req){
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'unique:products,name|required|string|max:255|min:4',
            'price' => 'required|numeric',
        ]);
        $photo = '';
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", 'p-photo');
        }

        $available = false;
        if($req->available == 'true')
            $available = true;
        Product::create([
            'name' => $req->name,
            'price' => $req->price,
            'available' => $available,
            'photo' => $photo
            ]);
        return redirect()->route('productList');
    }

    public function editProduct(Request $req , $id)
    {
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp|max:2048',
            'name' => 'required|string|max:255|min:4',
            'price' => 'required|numeric',
        ]);
        $product = Product::find($id);
        if($product->photo)
            $photo = $product->photo;
        else
            $photo = '';
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", 'p-photo');
        }
        $available = false;
        if($req->available == 'true')
            $available = true;
        $product->update([
            'name' => $req->name,
            'price' => $req->price,
            'available' => $available,
            'photo' => $photo
        ]);
        return redirect()->route('productList');
    }

    public function deleteProduct($id)
    {
        Product::find($id)->delete();
    }

    public function deletePhoto($id)
    {
        Product::find($id)->update([
            'photo' => ''
        ]);
    }

}
