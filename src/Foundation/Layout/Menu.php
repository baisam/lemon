<?php
/**
 * Menu.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/08.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Foundation\Layout;


use Closure;
use OutOfRangeException;
use BaiSam\UI\Layout\Component\Navigation;
use BaiSam\UI\Layout\Component\Menu as MenuItem;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Htmlable;

class Menu implements Htmlable
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * @var Navigation
     */
    protected $navigation;

    /**
     * 导航集合
     * @var array
     */
    protected $menus = [];

    /**
     * @var array
     */
    protected static $menuStack = [];

    /**
     * @var Closure
     */
    protected $filter;

    /**
     * View for element to render.
     *
     * @var string
     */
    protected $view = 'ui::partials.menu';

    /**
     * Menu constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->navigation = new Navigation('default');
    }

    /**
     * @param string $name
     * @return $this
     */
    public function make($name)
    {
        if (! empty(self::$menuStack)) {
            throw new OutOfRangeException('Cannot import other menu contents into this menu beyond the menu import scope.');
        }

        if ( ! isset($this->menus[$name]) ) {
            tap(new static($this->container), function ($menu) use($name) {
                $menu->navigation = new Navigation($name);
                $this->menus[$name] = $menu;
            });
        }

        return $this->menus[$name];
    }

    /**
     * Prepare the menu instance for serialization.
     *
     * @return void
     */
    public function prepareForSerialization()
    {
        self::$menuStack = null;
        unset($this->container, $this->menus, $this->filter);
    }

    /**
     * Get the underlying menus.
     *
     * @return array
     */
    public function getMenus()
    {
        return array_merge([$this], $this->menus);
    }

    /**
     * Set the menus instance.
     *
     * @param  array  $menus
     * @return void
     */
    public function setMenus(array $menus)
    {
        $menu = array_shift($menus);
        $this->navigation = $menu->navigation;

        foreach ($menus as $menu) {
            $menu->container = $this->container;
        }

        $this->menus = $menus;
    }

    /**
     * @param string $id
     * @param string $title
     * @param string|\Closure $url
     * @param string|null $icon
     * @param array|null $params
     * @param boolean|null $secure
     * @return \BaiSam\UI\Layout\Component\Menu
     */
    public function add($id, $title, $url, $icon = null, $params = [], $secure = null)
    {
        $last = last(self::$menuStack);
        if (empty($last)) {
            $last = $this->navigation;
        }

        if ($last instanceof Navigation) {
            $menu = new MenuItem($id, $title);
            $menu->weight( $last->count() );

            $last->push($menu);
        }
        else {
            $menu = $last->add($id, $title);
        }


        if ($url) {
            $menu->url($url, $params, $secure);
        }

        if (isset($icon)) {
            $menu->icon($icon);
        }

        return $menu;
    }

    /**
     * @param string $id
     * @param string $title
     * @param Closure $callback
     * @return \BaiSam\UI\Layout\Component\Menu
     */
    public function group($id, $title, Closure $callback)
    {
        $menu = $this->add($id, $title, null);

        self::$menuStack[] = $menu;

        $this->loadMenus($callback);

        array_pop(self::$menuStack);

        return $menu;
    }

    /**
     * @param \Closure|string $callback
     * @return $this
     */
    public function import($callback)
    {
        if (empty(self::$menuStack)) {
            self::$menuStack[] = $this->navigation;

            $this->loadMenus($callback);

            array_pop(self::$menuStack);
        }
        else {
            $this->loadMenus($callback);
        }

        return $this;
    }

    /**
     * Load the menus.
     *
     * @param  \Closure|string  $menus
     * @return void
     */
    protected function loadMenus($menus)
    {
        if ($menus instanceof Closure) {
            $menus($this);
        } else {
            $menu = $this;

            require $menus;
        }
    }

    /**
     * Set separation lines.
     *
     * @return $this
     */
    public function divide()
    {
        $last = last(self::$menuStack);
        if (empty($last)) {
            $last = $this->navigation;
        }

        $last->separator();

        return $this;
    }

    public function addClass($class)
    {
        $this->navigation->addClass($class);

        return $this;
    }

    public function removeClass($class)
    {
        $this->navigation->removeClass($class);

        return $this;
    }

    public function attr($name, $value)
    {
        $this->navigation->attribute($name, $value);

        return $this;
    }

    /**
     * 设置菜单过滤条件
     *
     * @param Closure $callback
     * @return $this
     */
    public function filter(Closure $callback)
    {
        // 设置过滤器
        $this->filter = $callback;

        return $this;
    }

    protected function filterMenuItem($item)
    {
        $separator = false;
        if ($item instanceof Navigation) {
            foreach ($item->items() as $index => $menu) {
                if ($menu == '##_SEPARATOR_##') {
                    if ($separator) {
                        $item->forget($index);
                    }
                    $separator = true;
                    continue;
                }

                if (call_user_func($this->filter, $menu)) {
                    $separator = false;
                    if ($menu->hasChildren()) {
                        $this->filterMenuItem($menu);
                    }
                    if (!$menu->hasChildren() && ($menu->formatUrl() == '')) {
                        $item->forget($index);
                    }
                }
                else {
                    $item->forget($index);
                }
            }
        }
        else if ($item instanceof MenuItem) {
            foreach ($item->children() as $index => $menu) {
                if ($menu == '##_SEPARATOR_##') {
                    if ($separator) {
                        $item->forget($index);
                    }
                    $separator = true;
                    continue;
                }

                if (call_user_func($this->filter, $menu)) {
                    $separator = false;
                    if ($menu->hasChildren()) {
                        $this->filterMenuItem($menu);
                    }
                    if (!$menu->hasChildren() && ($menu->formatUrl() == '')) {
                        $item->forget($index);
                    }
                }
                else {
                    $item->forget($index);
                }
            }
        }
    }

    /**
     * @return Navigation
     */
    protected function build()
    {
        $navigation = clone $this->navigation;

        // 过滤菜单项,主要用于权限处理
        if ($this->filter) {
            $this->filterMenuItem($navigation);
        }

        return $navigation;
    }

    /**
     * Set view template.
     *
     * @param string $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        $this->navigation->setView($this->view);

        return $this->build()->toHtml();
    }

    public function __toString()
    {
        return $this->toHtml();
    }
}