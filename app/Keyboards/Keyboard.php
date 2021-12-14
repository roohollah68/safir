<?php

namespace App\Keyboards;

class Keyboard
{
    public static $request_phone = array(array([
        "text" => "ارسال شماره تماس",
        "request_contact" => true
    ]));

    public static $user_option = array([
        "مشاهده آخرین فاکتور",
        "مشاهده 5 فاکتور آخر"
    ],[
        "مشاهده تمام فاکتورها",
        "ایجاد فاکتور جدید"
    ]);

    public static function register_user($url,$text)
    {
        return array(array([
            "text" => $text,
            "url" => $url
        ]));
    }



}
