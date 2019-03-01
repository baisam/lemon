<?php
/**
 * Content.php
 * BaiSam admin
 *
 * Created by realeff on 2018/11/04.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Contracts;


interface Content
{

    /**
     * 渲染内容
     *
     * @param array $data
     * @return \Illuminate\Contracts\View\View|string
     */
    public function view(array $data);

}