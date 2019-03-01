<?php
/**
 * PermissionCreateCommand.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/16.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Commands;


use BaiSam\Exceptions\RoleException;
use BaiSam\Models\Permission;
use BaiSam\Models\Role;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PermissionCreateCommand extends Command
{
    protected $name = 'permission:create';

    protected $description = 'Create a permission';

    public function handle()
    {
        $name = $this->argument('name');
        $display_name = $this->argument('display');
        $scope = $this->option('scope');
        $domain = $this->option('domain');
        $role_name = $this->option('role');

        $data = ['name'  => $name, 'scope' => $scope];
        if ($display_name) {
            $data['display_name'] = $display_name;
        }
        if ($domain) {
            $data['domain'] = $domain;
        }

        $permission = Permission::create($data);

        $this->info("Permission `{$permission->name}` created");

        if ($role_name) {
            // 授权给指定角色
            $model = new Role();
            if ($domain) {
                $model->setDomain($domain);
            }

            $role = $model->findByName($role_name);
            if (!$role) {
                throw new RoleException("Role `{$role_name}` does not exist.");
            }
            $role->grantPermissions($permission);
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'The name of the permission.'),
            array('display', InputArgument::OPTIONAL, 'The display name of the permission.')
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['scope', null, InputOption::VALUE_REQUIRED, 'Set the permission scope.', 'default'],
            ['role', null, InputOption::VALUE_OPTIONAL, 'Grant permission to the role.', ],
            ['domain', null, InputOption::VALUE_OPTIONAL, 'Set domain to the permission.', ]
        ];
    }

}