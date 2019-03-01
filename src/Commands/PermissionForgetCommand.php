<?php
/**
 * PermissionForgetCommand.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/16.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Commands;


use BaiSam\Exceptions\PermissionException;
use BaiSam\Exceptions\RoleException;
use BaiSam\Models\Permission;
use BaiSam\Models\Role;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PermissionForgetCommand extends Command
{
    protected $name = 'permission:forget';

    protected $description = 'Forget a permission';

    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->option('domain');
        $role_name = $this->option('role');

        if ($role_name) {
            // 撤消授权
            $model = new Role();
            if ($domain) {
                $model->setDomain($domain);
            }
            $role = $model->findByName($role_name);
            if (!$role) {
                throw new RoleException("Role `{$role_name}` does not exist.");
            }
            $role->revokePermissions($name);
            $this->info("Revoking `{$role->name}` role's `{$name}` permission succeeded.");
        }
        else {
            // 删除权限
            $model = new Permission();
            if ($domain) {
                $model->setDomain($domain);
            }
            $permission = $model->findByName($name);
            if (!$permission) {
                throw new PermissionException("Permission `{$name}` does not exist.");
            }

            $permission->delete();
            $this->info("Permission `{$permission->name}` deleted");
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
            array('name', InputArgument::REQUIRED, 'The name of the permission.')
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
            ['domain', null, InputOption::VALUE_OPTIONAL, 'Set domain to the permission.', ],
            ['role', null, InputOption::VALUE_OPTIONAL, 'Revoke permission to the role.', ],
        ];
    }

}