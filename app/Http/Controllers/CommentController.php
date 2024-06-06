<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function showComments($id)
    {
        $order = Order::with('comments')->findOrFail($id);
        return view('comments');
    }

    public function insert()
    {

    }
}
