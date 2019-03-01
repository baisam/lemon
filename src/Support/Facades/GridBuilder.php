<?php
/**
 * GridBuilder.php
 * BaiSam admin
 *
 * Created by realeff on 2018/11/02.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Support\Facades;


use Illuminate\Support\Facades\Facade;

class GridBuilder extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'grid.builder';
    }

}