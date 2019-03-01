<?php
/**
 * Layout.php
 * BaiSam admin
 *
 * Created by realeff on 2018/05/26.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Foundation;


use BadMethodCallException;
use BaiSam\Support\Form;
use BaiSam\UI\UIRepository;
use BaiSam\UI\Layout\Builder;
use Illuminate\Container\Container;

/**
 * Class Layout
 *
 * @method \BaiSam\UI\Layout\Builder         title($title)
 * @method \BaiSam\UI\Layout\Builder         description($subtitle)
 * @method \BaiSam\UI\Layout\Builder         help($help)
 * @method \BaiSam\UI\Layout\Builder         addMeta($name, $content)
 * @method \BaiSam\UI\Layout\Row             row($content = null)
 * @method \BaiSam\UI\Layout\Component\Breadcrumb  breadcrumb($name = null)
 * @method \BaiSam\UI\Layout\Component\Navigation  navigation($name = null)
 * @method \BaiSam\UI\Layout\Content         content($content = null)
 *
 * @package BaiSam\Foundation
 */
class Layout
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var UIRepository
     */
    protected $resource;

    /**
     * 共享这个布局实例
     * @var array
     */
    protected $layouts;

    /**
     * 当前布局
     * @var Builder
     */
    protected $builder;

    public function __construct(Container $container, UIRepository $resource)
    {
        $this->container = $container;
        $this->resource = $resource;
    }

    /**
     * Get resource for the layout.
     *
     * @return \BaiSam\UI\UIRepository
     */
    public function resource()
    {
        return $this->resource->getInstance();
    }

    /**
     * @param string|null $name
     * @return \BaiSam\UI\Layout\Builder
     */
    public function create($name = null)
    {
        return new Builder($this->container, $name);
    }

    /**
     * 生成布局,并将这个布局共享.
     * @param string $name
     * @param boolean $share
     * @return \BaiSam\UI\Layout\Builder
     */
    public function make($name, $share = false)
    {
        if ( ! isset($this->layouts[$name]) ) {
            $this->layouts[$name] = new Builder($this->container, $name);
        }

        if ($share) {
            $this->builder = $this->layouts[$name];
        }

        return $this->layouts[$name];
    }

    /**
     * 渲染为Form中的内容
     * @param \BaiSam\Support\Form $form
     * @return \BaiSam\UI\Layout\Builder
     */
    public function form(Form $form)
    {
        $rows = $this->builder->rows();
        $form->content($rows);

        $this->builder->empty();
        $this->builder->content($form);

        return $this->builder;
    }

    /**
     * 渲染当前或指定名称的布局
     *
     * @param string|null $name
     * @param mixed $content
     * @return \BaiSam\UI\Layout\Builder
     */
    public function render($name = null, $content = null)
    {
        if (empty($this->builder) && empty($name)) {
            return null;
        }

        $builder = $this->builder;
        if (!empty($name) && isset($this->layouts[$name])) {
            $builder = $this->layouts[$name];
        }
        else if (isset($name)) {
            $builder = $this->create($name);
        }

        if (isset($content)) {
            $builder->content($content);
        }

        return $builder;
    }

    public function __call($method, $arguments)
    {
        if (empty($this->builder)) {
            throw new BadMethodCallException('The method ['. $method .'] does not exist, make first and set to share.');
        }

        return call_user_func_array([$this->builder, $method], $arguments);
    }
}