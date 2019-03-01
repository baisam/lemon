<?php
/**
 * Role.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/15.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Models;

use BaiSam\Exceptions\RoleException;
use BaiSam\Support\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasPermissions;

    public $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        $attributes['domain'] = $attributes['domain'] ?? config('permission.domain', 'default');
        $this->setDomain($attributes['domain']);

        $this->setTable(config('permission.roles_table'));

        parent::__construct($attributes);
    }

    /**
     * A permission can be applied to permissions.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            config('permission.permission_roles_table')
        );
    }

    /**
     * 设置权限域
     *
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;

        static::addGlobalScope('domain', function ($query) use($domain) {
            $query->where(config('permission.roles_table').'.domain', $domain);
        });
    }

    /**
     * 根据角色名称查找
     *
     * @param string $name
     *
     * @return Role
     */
    public function findByName(string $name)
    {
        return $this->where('name', $name)->first();
    }

    public static function create(array $attributes = [])
    {
        $attributes['domain'] = $attributes['domain'] ?? config('permission.domain', 'default');

        if (static::where('name', $attributes['name'])->where('domain', $attributes['domain'])->first()) {
            throw new RoleException("{$attributes['name']} role already exists.");
        }

        return parent::query()->create($attributes);
    }

    /**
     * 根据角色的名称(以及可选的domain)查找或创建角色。
     *
     * @param string $name
     * @param string|null $domain
     *
     * @return Role
     */
    public static function findOrCreate(string $name, $domain = null)
    {
        $domain = $domain ?? config('permission.domain', 'default');

        $role = static::where('name', $name)->where('domain', $domain)->first();

        if (! $role) {
            return parent::query()->create(['name' => $name, 'domain' => $domain]);
        }

        return $role;
    }

}