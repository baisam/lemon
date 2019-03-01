<?php
/**
 * MenuCacheCommand.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/08.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Commands;


use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;

class MenuCacheCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'menu:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a menu cache file for faster menu registration';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new route command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('menu:clear');

        $menus = $this->getFreshApplication()['menu']->getMenus();

        if (count($menus) == 0) {
            return $this->error("Your application doesn't have any menus.");
        }

        foreach ($menus as $menu) {
            $menu->prepareForSerialization();
        }

        $this->files->put(
            $this->laravel->bootstrapPath('cache') .'/menus.php', $this->buildMenuCacheFile($menus)
        );

        $this->info('Routes cached successfully!');
    }

    /**
     * Get a fresh application instance.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected function getFreshApplication()
    {
        return tap(require $this->laravel->bootstrapPath().'/app.php', function ($app) {
            $app->make(ConsoleKernelContract::class)->bootstrap();
        });
    }

    /**
     * Build the menu cache file.
     *
     * @param  array  $menus
     * @return string
     */
    protected function buildMenuCacheFile(array $menus)
    {
        $stub = $this->files->get(__DIR__.'/stubs/menus.stub');

        return str_replace('{{menus}}', base64_encode(serialize($menus)), $stub);
    }
}