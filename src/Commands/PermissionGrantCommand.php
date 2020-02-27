<?php
/**
 * PermissionGrantCommand.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/16.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Commands;


use InvalidArgumentException;
use BaiSam\Models\Role;
use BaiSam\Exceptions\RoleException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PermissionGrantCommand extends Command
{
    protected $name = 'permission:grant';

    protected $description = 'Grant a permission';

    public function handle()
    {
        $name = $this->argument('name');
        $role_name = $this->option('role');
        $domain = $this->option('domain');

        if (empty($role_name)) {
            throw new InvalidArgumentException("You must provide the option --role");
        }

        // 授权
        $model = new Role();
        if ($domain) {
            $model->setDomain($domain);
        }
        $role = $model->findByName($role_name);
        if (!$role) {
            throw new RoleException("Role `{$role_name}` does not exist.");
        }
        $role->grantPermissions($name);
        $this->info("Granting `{$role->name}` role's `{$name}` permission succeeded.");
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
            ['role', null, InputOption::VALUE_REQUIRED, 'Grant permission to the role.', ],
            ['domain', null, InputOption::VALUE_OPTIONAL, 'Set domain to the role.', ],
        ];
    }


}