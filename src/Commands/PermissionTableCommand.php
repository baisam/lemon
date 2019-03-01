<?php
/**
 * PermissionTableCommand.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/11.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Commands;


use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;

class PermissionTableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'permission:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the permission database table';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new cache table command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $fullPath = $this->createBaseMigration();

        $stub = $this->files->get(__DIR__.'/../Resources/migrations/permission.stub');
        $stub = str_replace(
            ['{roles_table}', '{permissions_table}', '{permission_roles_table}',
             '{model_roles_table}', '{model_permissions_table}'],
            [config('permission.roles_table'), config('permission.permissions_table'),
             config('permission.permission_roles_table'), config('permission.model_roles_table'),
             config('permission.model_permissions_table')],
            $stub
        );


        $this->files->put($fullPath, $stub);

        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the table.
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'setup_permission_tables';

        $path = $this->laravel->databasePath().'/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }

}