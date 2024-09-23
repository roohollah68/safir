<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Order;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function create($order, $user, $text = null, $photo = null)
    {
        if (gettype($user) == 'object') {
            $user = $user->id;
        }
        if (gettype($order) == 'object') {
            $order = $order->id;
        }
        Comment::create([
            'order_id' => $order,
            'user_id' => $user,
            'text' => $text,
            'photo' => $photo,
        ]);
    }

    public function view($id)
    {
        $order = Order::withTrashed()->findOrFail($id);
        $comments = $order->comments()->with('user')->get();
        return view('comment', [
            'order' => $order,
            'comments' => $comments,
        ]);
    }

    public function add($id, Request $req)
    {
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp,pdf|max:3048',
        ]);
        $message = auth()->user()->name. ': '.$req->text . "
```[مشاهده سفارش]". (new TelegramController)->createOrderMessage(Order::find($id)) ."```"."
```[مشاهده کامنت های قبلی]". strip_tags($this->view($id))."```";



        if($req->file("photo")) {
            $photo = $req->file("photo")->store("", "comment");
            $content = array("caption" => $message,  "photo" => env('APP_URL') . "comment/{$photo}");
            $this->sendPhotoToBale($content, env('CommentId'));
        }
        else {
            $photo = null;
            $this->sendTextToBale($message, env('CommentId'));
        }
        $text = $req->text;
        if(!$text && !$photo)
            abort(403);
        $this->create($id,auth()->user(),$text,$photo);
        return 'ok';
    }
}


