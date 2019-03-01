<?php
/**
 * AuthServiceProvider.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/11.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Foundation\Support\Providers;


use BaiSam\Commands\PermissionCreateCommand;
use BaiSam\Commands\PermissionForgetCommand;
use BaiSam\Commands\PermissionGrantCommand;
use BaiSam\Commands\PermissionRoleCommand;
use BaiSam\Commands\PermissionTableCommand;
use BaiSam\Models\Permission;
use BaiSam\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $commands = [
        'command.permission.table',
        'command.permission.create',
        'command.permission.forget',
        'command.permission.role',
        'command.permission.grant',
        //'command.permission.cache'
    ];

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Publish config files
            $this->publishes([__DIR__.'/../../../Resources/config/permission.php' => config_path('permission.php')], 'config');
        }

        $this->app->resolving(Role::class, function ($role) {
            $role->setDomain(config('permission.domain', 'default'));
        });
        $this->app->resolving(Permission::class, function ($permission) {
            $permission->setDomain(config('permission.domain', 'default'));
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
            $this->registerPermissionTableCommand();
            $this->registerPermissionCreateCommand();
            $this->registerPermissionForgetCommand();
            $this->registerPermissionRoleCommand();
            $this->registerPermissionGrantCommand();

            $this->commands($this->commands);
        }

        $this->registerBladeExtensions();

        // Load and merge the config
        $this->mergeConfig();
    }

    /**
     * Merges user's and admin's configs.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../../Resources/config/permission.php', 'permission'
        );
    }

    /**
     * Register the application's policies.
     *
     * @return void
     */
    protected function registerPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }

    protected function registerPermissions()
    {
        // 注册权限检查函数
        Gate::before(function ($user, $ability) {
            if (false !== strpos($ability, '|')) {
                return $user->hasPermissions($ability);
            }
        });

        // 获取所有权限进行注册
        $permissions = Permission::all(['id', 'name', 'display_name']);
        $permissions->each(function ($permission) {
            Gate::define($permission->name, function ($user) use($permission) {
                return $user->hasPermissions($permission);
            });
        });
    }

    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function ($bladeCompiler) {
            $bladeCompiler->directive('role', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('elserole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php elseif(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasanyrole', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAnyRole({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasanyrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasallroles', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAllRoles({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasallroles', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('unlessrole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(!auth({$guard})->check() || ! auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endunlessrole', function () {
                return '<?php endif; ?>';
            });
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerPermissionTableCommand()
    {
        $this->app->singleton('command.permission.table', function ($app) {
            return new PermissionTableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerPermissionCreateCommand()
    {
        $this->app->singleton('command.permission.create', function () {
            return new PermissionCreateCommand();
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerPermissionForgetCommand()
    {
        $this->app->singleton('command.permission.forget', function () {
            return new PermissionForgetCommand();
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerPermissionRoleCommand()
    {
        $this->app->singleton('command.permission.role', function () {
            return new PermissionRoleCommand();
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerPermissionGrantCommand()
    {
        $this->app->singleton('command.permission.grant', function () {
            return new PermissionGrantCommand();
        });
    }
    //TODO 权限缓存命令
}