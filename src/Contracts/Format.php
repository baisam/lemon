<?php
/**
 * Format.php
 * BaiSam BaiSam
 *
 * Created by realeff on 2018/09/29.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Contracts;

use Closure;

interface Format
{

    /**
     * 格式化数据
     * @param string|Closure $format
     * @return $this
     */
    public function format($format);

}