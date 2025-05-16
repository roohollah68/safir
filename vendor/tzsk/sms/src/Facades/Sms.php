<?php

namespace Tzsk\Sms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tzsk\Sms\Sms
 */
class Sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        file_put_contents('res.txt','lkgslgslselfk');
        return 'tzsk-sms';
    }
}
