<?php
/**
 * LayoutController.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/06.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Controllers;


use BaiSam\UI\Layout\Builder;
use BaiSam\Support\Facades\Layout;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Request;

trait LayoutController
{
    /**
     * 导航搜索表单
     * @var bool
     */
    protected $searchForm = false;

    /**
     * Action代理
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        $layout = Layout::make($this->layout ?? 'app', true);

        if (app('auth')->guard()->check()) {
            $layout->navbar('navbar')
                ->navigation('menu')
                ->link(' 退出', route('logout'))->icon('sign-out')->attribute('role', 'logout');

        }

        // 重置布局状态
        $layout->flushState();

        // 初始化布局
        if (method_exists($this, 'initLayout')) {
            $this->initLayout($layout);
        }

        // 初始化面包屑导航
        $layout->breadcrumb('breadcrumb')->link('首页', '/')->icon('home');

        // 加载内容导航
        if (method_exists($this, 'navigation')) {
            $this->navigation($layout->navigation('navigation'));
        }

        // call
        $output = $this->{$method}(...array_values($parameters));

        if ($this->searchForm && ! Request::pjax()) {
            $this->buildSearchFormForNav($layout->navbar('navbar')->form('search'));
        }

        if ($output instanceof Builder) {
            return $output;
        }

        if ($output instanceof Htmlable || $output instanceof Renderable) {
            if ( isset($output) && ! $layout->hasRows() ) {
                $layout->content($output);
            }

            return $layout;
        }

        return $output;
    }

    /**
     * 搜索表单
     * @param \BaiSam\UI\Form\Builder $form
     */
    protected function buildSearchFormForNav($form)
    {
        $form->attribute('role', 'search');

        // 搜索关键词
        $search = $form->text('q', '')->setPlaceholder('Search...');
        $search->setView('ui::partials.form.search');
        $search->searchBtn = true;
    }

    /**
     * 校验csrf token
     * @param \Illuminate\Http\Request $request
     * @throws TokenMismatchException
     */
    protected function verifyCsrfToken($request)
    {
        $token = $request->input('_token');

        if (is_null($token) || !is_string($token) || !hash_equals($request->session()->token(), $token)) {
            throw new TokenMismatchException;
        }

        // 安全考虑，需要重新生成Token
        $request->session()->regenerateToken();
    }
}