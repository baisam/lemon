<?php
/**
 * MenuClearCommand.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/08.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Commands;


use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MenuClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'menu:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the menu cache file';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new route clear command instance.
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
        $this->files->delete($this->laravel->bootstrapPath('cache') .'/menus.php');

        $this->info('Menu cache cleared!');
    }

}