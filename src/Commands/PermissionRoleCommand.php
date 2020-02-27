<?php
/**
 * PermissionRoleCommand.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/16.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Commands;


use BaiSam\Models\Role;
use BaiSam\Exceptions\RoleException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PermissionRoleCommand extends Command
{
    protected $name = 'permission:role';

    protected $description = 'Create a role';

    public function handle()
    {
        $name = $this->argument('name');
        $display_name = $this->argument('display');
        $domain = $this->option('domain');
        $forget = $this->option('forget');

        if ($forget) {
            $model = new Role();
            if ($domain) {
                $model->setDomain($domain);
            }

            $role = $model->findByName($name);
            if (!$role) {
                throw new RoleException("Role `{$name}` does not exist.");
            }

            $role->delete();
            $this->info("Role `{$role->name}` deleted");
        }
        else {
            $data = ['name'  => $name];
            if ($display_name) {
                $data['display_name'] = $display_name;
            }
            if ($domain) {
                $data['domain'] = $domain;
            }

            $role = Role::create($data);

            $this->info("Role `{$role->name}` created");
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
            array('name', InputArgument::REQUIRED, 'The name of the role.'),
            array('display', InputArgument::OPTIONAL, 'The display name of the role.')
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
            ['domain', null, InputOption::VALUE_OPTIONAL, 'Set domain to the role.', ],
            ['forget', null, InputOption::VALUE_NONE, 'Forget the role.', ]
        ];
    }

}