<?php
/**
 * LayoutServiceProvider.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/07.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Foundation;


use BaiSam\Commands\FormMakeCommand;
use BaiSam\Commands\GridMakeCommand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ServiceProvider;

class LayoutServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * @var array
     */
    protected $commands = [
        'command.form.make',
        'command.grid.make'
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        RedirectResponse::macro('success', function ($title, $message) {
            return $this->with('success', new MessageBag(compact('title', 'message')));
        });
        RedirectResponse::macro('error', function ($title, $message) {
            return $this->with('error', new MessageBag(compact('title', 'message')));
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->registerFormMakeCommand();
            $this->registerGridMakeCommand();

            $this->commands($this->commands);
        }

        $this->registerLayout();

        $this->registerFormBuilder();

        $this->registerGridBuilder();

        // 分页返回当前完整路径，并移除page,_token,op参数
        Paginator::currentPathResolver(function () {
            return remove_query_arg(['page', '_token','op'], $this->app['request']->fullUrl());
        });
    }

    protected function registerLayout()
    {
        $this->app->singleton('layout', function ($app) {
            return new Layout($app, $app['resources']);
        });

        $this->app->alias('layout', '\BaiSam\Foundation\Layout');
    }

    protected function registerFormBuilder()
    {
        $this->app->singleton('form.builder', function ($app) {
            return new FormBuilder($app, $app['form.helper'], $app['events']);
        });

        $this->app->alias('form.builder', '\BaiSam\Foundation\FormBuilder');
    }

    protected function registerGridBuilder()
    {
        $this->app->singleton('grid.builder', function ($app) {
            return new GridBuilder($app, $app['grid.helper'], $app['events']);
        });

        $this->app->alias('grid.builder', '\BaiSam\Foundation\GridBuilder');
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerFormMakeCommand()
    {
        $this->app->singleton('command.form.make', function ($app) {
            return new FormMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerGridMakeCommand()
    {
        $this->app->singleton('command.grid.make', function ($app) {
            return new GridMakeCommand($app['files']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'layout',
            'form.builder',
            'grid.builder',
            'command.form.make',
            'command.grid.make'
        ];
    }

}