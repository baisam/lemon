<?php
/**
 * GridBuilder.php
 * BaiSam admin
 *
 * Created by realeff on 2018/11/02.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Foundation;


use BaiSam\UI\Grid\Helper as GridHelper;
use Illuminate\Support\Str;
use Illuminate\Contracts\Container\Container;
use Illuminate\Events\Dispatcher as EventDispatcher;

class GridBuilder
{

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var GridHelper
     */
    protected $helper;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * GridBuilder constructor.
     *
     * @param Container $container
     * @param GridHelper $helper
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(Container $container, GridHelper $helper, EventDispatcher $eventDispatcher)
    {
        $this->app = $container;
        $this->helper = $helper;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $id
     * @param null $gridClass
     * @param null $model
     * @return \BaiSam\Support\Grid
     */
    public function create($id, $gridClass = null, $model = null)
    {
        if (is_null($gridClass)) {
            $gridClass = '\BaiSam\Support\Grid';
        }

        //TODO 增加缓存，提供Build性能

        $grid = $this->app->make($gridClass, ['id' => $id]);

        $grid->setHelper($this->helper);
        $grid->setRequest($this->app->make('request'));

        if (isset($model)) {
            $grid->setData($model);
        }

        // 开始构建
        $grid->buildGrid();
        $grid->buildFilters();
        $grid->buildColumns();

        // 构建工具栏
        $methods = get_class_methods($grid);
        $methods = array_filter($methods, function ($method) {
            return Str::endsWith(strtolower($method), 'toolbar');
        });
        foreach ($methods as $method) {
            $name = substr($method, 0, -7);
            $grid->$method($grid->toolbar($name));
        }

        //TODO 添加表单事件

        return $grid;
    }


    /**
     * @return \BaiSam\UI\Grid\Helper
     */
    public function getGridHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Illuminate\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

}