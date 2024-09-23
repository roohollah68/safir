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
        $order = Order::find($id);
        $orderText = (new TelegramController)->createOrderMessage($order);
        $commentText = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", strip_tags($this->view($id)));
        $newComment = auth()->user()->name . ': ' . $req->text;
        $commentText .= '    '.$newComment;
        $message = $newComment . " ```[مشاهده سفارش ". $order->name ."]" . $orderText. "\n\n * کامنت ها: *  " . $commentText . "```" ;

//        . "```[مشاهده کامنت های قبلی]" . $commentText . "```"
        if ($req->file("photo")) {
            $photo = $req->file("photo")->store("", "comment");
            $content = array("caption" => $message, "photo" => env('APP_URL') . "comment/{$photo}");
            $this->sendPhotoToBale($content, env('CommentId'));
        } else {
            $photo = null;
            $this->sendTextToBale($message, env('CommentId'));
        }
        $text = $req->text;
        if (!$text && !$photo)
            abort(403);
        $this->create($id, auth()->user(), $text, $photo);
        return 'ok';
    }
}


