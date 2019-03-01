<?php
/**
 * Layout.php.
 * User: feng
 * Date: 2018/5/24
 */

namespace BaiSam\Support\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Class Layout
 *
 * @method static \BaiSam\UI\Layout\Builder         title($title)
 * @method static \BaiSam\UI\Layout\Builder         description($subtitle)
 * @method static \BaiSam\UI\Layout\Builder         help($help)
 * @method static \BaiSam\UI\Layout\Builder         addMeta($name, $content)
 * @method static \BaiSam\UI\Layout\Builder         row($content = null)
 * @method static \BaiSam\UI\Layout\Component\Breadcrumb  breadcrumb($name = null)
 * @method static \BaiSam\UI\Layout\Component\Navigation  navigation($name = null)
 * @method static \BaiSam\UI\Layout\Content         content($content = null)
 *
 * @package BaiSam\Support\Facades
 */
class Layout extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'layout';
    }
}