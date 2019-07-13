<?php
/**
 * UploadController.php
 * BaiSam huixin
 *
 * Created by realeff on 2019/02/26.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Controllers;


use BaiSam\UI\Form\Traits\Upload;

trait UploadController
{
    use Upload {
        initUpload as baseInitUpload;
    }

    protected $helper;


    protected function initUpload($name = 'default')
    {
        if (!isset($this->helper)) {
            // make form helper
            $this->helper = app('form.helper');
        }

        $this->baseInitUpload($name);
    }

    //TODO 初始化上传配置
    //TODO 开始上传文件
    //TODO 上传文件数据
    //TODO 结束上传文件
    //TODO 上传选择配置，包括访问路经、存储路径格式、存储器类型、文件类型、上传大小限制、文件上传数量限制等

}