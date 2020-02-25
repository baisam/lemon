<?php
/**
 * Grid.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/06.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Support;


use ArrayAccess;
use Traversable;
use BaiSam\UI\Grid\Toolbar;
use BaiSam\UI\Grid\Builder;
use BaiSam\UI\Grid\Helper as GridHelper;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Contracts\Support\Renderable;

/**
 * Class Grid
 *
 * @method string                               getId()
 * @method \BaiSam\UI\Grid\Builder              setKeyName($key)
 * @method \BaiSam\UI\Grid\Column               column($name, $title)
 * @method \BaiSam\UI\Grid\Builder              complex($name, $title, $column, ...$columns)
 * @method \BaiSam\UI\Grid\Builder              hidden(...$columns)
 * @method \BaiSam\UI\Grid\Builder              fixation($column)
 * @method \BaiSam\UI\Grid\Builder              setPerPage($perPage)
 * @method \BaiSam\UI\Grid\Builder              pagination($total, $perPage = 20)
 * @method \BaiSam\UI\Grid\Builder              disablePagination()
 * @method \BaiSam\UI\Grid\Builder              sort(...$columns)
 * @method \BaiSam\UI\Grid\Filter               filter($name, $label)
 * @method \BaiSam\UI\Grid\Toolbar              toolbar($name = '', \Closure $callback = null)
 * @method void                                 rightToolbar(Toolbar $toolbar)
 * @method void                                 topToolbar(Toolbar $toolbar)
 * @method \BaiSam\UI\Grid\Builder              setEmptyString($str)
 * @method \BaiSam\UI\Grid\Builder              config($name, $value = null)
 *
 * @package BaiSam\Foundation
 */
class Grid implements Renderable
{
    /**
     * 标题
     * @var string
     */
    protected $title;

    /**
     * 表数据模型
     * @var Eloquent
     */
    protected $model;

    /**
     * 表编译器
     * @var Builder
     */
    protected $builder;

    /**
     * @var GridHelper
     */
    protected $helper;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var 分页
     */
    protected $perPages;
    /**
     * @var mixed
     */
    protected $data;

    /**
     * Grid constructor.
     *
     * @param string $id
     * @param string $title
     * @param array $config
     */
    public function __construct(string $id, string $title = null, $config = null)
    {
        $this->builder = new Builder($id, $config);

        if (isset($title)) {
            $this->title = $title;
        }
    }

    /**
     * 表格标题
     *
     * @return string
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * 设置表格标题
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * 仅在实例化时设置Form Helper
     *
     * @param GridHelper $helper
     * @return $this
     */
    public function setHelper(GridHelper $helper)
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * 仅在实例化时设置Form Request
     *
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * 构建表格
     */
    public function buildGrid()
    {
        //
    }

    /**
     * 构建表格列信息
     */
    public function buildColumns()
    {
        //
    }

    /**
     * 构建表格过滤表单
     */
    public function buildFilters()
    {
        //
    }

    public function setPerPages($perPages = [10, 20, 50, 100])
    {
        $this->perPages = $perPages;
        if ($this->request && $this->request->has('perpage')) {
            $this->setPerPage($this->request->get('perpage'));
        }
    }

    /**
     * 表格默认工具条
     *
     * @param Toolbar $toolbar
     */
    public function leftToolbar(Toolbar $toolbar)
    {
        //
    }

    /**
     * @return Eloquent
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * @param Eloquent|Arrayable|array $model
     * @return $this
     */
    public function setData($model)
    {
        //TODO 这里是否考虑绑定表单数据处理程序
        if ($model instanceof Eloquent || $model instanceof \Illuminate\Database\Eloquent\Builder) {
            //TODO relations
            $this->model = $model;
            $this->data = $model;
        }
        else if ($model instanceof ArrayAccess ||
            $model instanceof Arrayable ||
            $model instanceof Traversable || is_array($model)) {
            $this->data = $model;
        }

        return $this;
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        if (isset($this->model)) {
            //TODO 处理过滤条件
        }

        $this->builder->setTitle($this->title());
        $this->builder->setData($this->data);

        if (isset($this->perPages)) {
            $this->builder->perPages = $this->perPages;
        }

        return $this->builder->render();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->builder, $method), $arguments);
    }
}