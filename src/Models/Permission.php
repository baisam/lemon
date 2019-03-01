<?php
/**
 * Permission.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/15.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Models;


use BaiSam\Exceptions\PermissionException;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        $attributes['domain'] = $attributes['domain'] ?? config('permission.domain', 'default');
        $this->setDomain($attributes['domain']);

        $this->setTable(config('permission.permissions_table'));

        parent::__construct($attributes);
    }

    /**
     * A permission can be applied to roles.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            config('permission.permission_roles_table')
        );
    }

    /**
     * 设置权限域
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        static::addGlobalScope('domain', function ($query) use($domain) {
            $query->where(config('permission.permissions_table').'.domain', $domain);
        });
    }

    /**
     * 根据权限名称查找
     *
     * @param string $name
     *
     * @return Permission
     */
    public function findByName(string $name)
    {
        return $this->where('name', $name)->first();
    }

    public static function create(array $attributes = [])
    {
        $attributes['domain'] = $attributes['domain'] ?? config('permission.domain', 'default');

        if (static::where('name', $attributes['name'])->where('domain', $attributes['domain'])->first()) {
            throw new PermissionException("{$attributes['name']} permission already exists.");
        }

        return parent::query()->create($attributes);
    }

    /**
     * 根据权限的名称(以及可选的domain)查找或创建权限。
     *
     * @param string $name
     * @param string|null $domain
     *
     * @return Permission
     */
    public static function findOrCreate(string $name, $domain = null)
    {
        $domain = $domain ?? config('permission.domain', 'default');

        $permission = static::where('name', $name)->where('domain', $domain)->first();

        if (! $permission) {
            return parent::query()->create(['name' => $name, 'domain' => $domain]);
        }

        return $permission;
    }

}