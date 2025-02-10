<?php

namespace App\Http\Controllers;

use App\Models\Good;
use Illuminate\Http\Request;

class KeysunController extends Controller
{
    public function good()
    {
        $goods = Good::where('tag','>', pow(10,12))->get()->keyBy('id');
        return view('keysun.good' , compact('goods'));
    }
}
