<?php
/**
 * FormBuilder.php.
 * User: Administrator
 * Date: 2018/3/5
 */

namespace BaiSam\Support\Facades;

use Illuminate\Support\Facades\Facade;

class FormBuilder extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'form.builder';
    }
}