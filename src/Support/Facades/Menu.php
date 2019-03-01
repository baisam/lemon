<?php
/**
 * Layout.php.
 * User: feng
 * Date: 2018/5/24
 */

namespace BaiSam\Support\Facades;


use Illuminate\Support\Facades\Facade;

class Menu extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'menu';
    }
}