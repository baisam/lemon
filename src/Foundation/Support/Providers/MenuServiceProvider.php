<?php
/**
 * MenuServiceProvider.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/07.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Foundation\Support\Providers;


use BaiSam\Foundation\Layout\Menu;
use BaiSam\Commands\MenuCacheCommand;
use BaiSam\Commands\MenuClearCommand;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
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
        'command.menu.cache',
        'command.menu.clear'
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if ($this->menusAreCached()) {
            $this->loadCachedMenus();
        }
        else {
            $this->loadMenus();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->registerMenuCacheCommand();

            $this->commands($this->commands);
        }

        $this->registerMenu();

        $this->registerNavigation();
    }

    protected function menusAreCached()
    {
        return $this->app->make('files')->exists($this->app->bootstrapPath('cache') .'/menus.php');
    }

    protected function loadCachedMenus()
    {
        $this->app->booted(function () {
            require $this->app->bootstrapPath('cache') .'/menus.php';
        });
    }

    protected function loadMenus()
    {
        if (method_exists($this, 'imports')) {
            $this->app->call([$this, 'imports']);
        }
    }


    protected function registerMenu()
    {
        $this->app->singleton('menu', function ($app) {
            return new Menu($app);
        });

        $this->app->alias('menu', 'BaiSam\Foundation\Layout\Menu' );
    }

    protected function registerNavigation()
    {
        foreach (array_keys(config('ui.menus', [])) as $name) {
            $this->app->singleton('menu.'. $name, function ($app) use($name) {
                return $app['menu']->make($name);
            });
        }
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMenuCacheCommand()
    {
        $this->app->singleton('command.menu.cache', function ($app) {
            return new MenuCacheCommand($app['files']);
        });

        $this->app->singleton('command.menu.clear', function ($app) {
            return new MenuClearCommand($app['files']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        $provides = [
            'menu',
            'command.menu.cache',
            'command.menu.clear'
        ];
        foreach (array_keys(config('ui.menus', [])) as $name) {
            $provides[] = 'menu.'. $name;
        }

        return $provides;
    }
}