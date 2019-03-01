<?php
/**
 * HasPermissions.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/11.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Support\Traits;


use BaiSam\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

trait HasPermissions
{
    protected $domain = 'default';

    //TODO 监听Model移除事件
    public static function bootHasPermissions()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->permissions()->detach();
        });
    }

    /**
     * A model may have multiple direct permissions.
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions()
    {
        return $this->morphToMany(
            Permission::class,
            'model',
            config('permission.model_permissions_table')
        );
    }

    /**
     * 获取权限应用域
     *
     * @param string $domain
     * @return $this
     */
    protected function setDomain(string $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * 获取权限模型
     * @return Permission
     */
    protected function getPermissionModel()
    {
        $model = new Permission();
        $model->setDomain($this->domain);

        return $model;
    }

    /**
     * 应用权限范围查询
     *
     * @param Builder $query
     * @param string|array|Permission|Collection $permissions
     * @return Builder
     */
    public function scopePermission(Builder $query, $permissions)
    {
        $permissions = $this->collectPermissionIds($permissions);

        if (empty($permissions)) {
            return $query;
        }

        return $query->whereHas('permissions', function ($query) use ($permissions) {
            $query->whereIn(config('permission.model_permissions_table').'.permission_id', $permissions);
        });
    }

    /**
     * @param array|string|Permission ...$permissions
     * @return array
     */
    protected function collectPermissionIds($permissions)
    {
        if (is_string($permissions[0])) {
            $permissions[0] = strtr($permissions[0], ',', '|');
            if (false !== strpos($permissions[0], '|')) {
                $permissions[0] = explode('|', trim($permissions[0], "'\" "));
            }
        }

        $model = $this->getPermissionModel();

        return collect($permissions)->flatten()->map(function ($permission) use($model) {
            if ($permission instanceof Permission) {
                return $permission->id;
            }

            if (is_numeric($permission)) {
                return $permission;
            }

            $permission = $model->findByName($permission);

            return $permission ? $permission->id : 0;
        })->filter()->all();
    }

    /**
     * 授于Model的权限
     *
     * @param array|string|Permission ...$permissions
     * @return $this
     */
    public function grantPermissions(...$permissions)
    {
        $this->permissions()->sync($this->collectPermissionIds($permissions), false);

        return $this;
    }

    /**
     * 撤消Model的权限
     *
     * @param array|string|Permission ...$permissions
     * @return $this
     */
    public function revokePermissions(...$permissions)
    {
        $this->permissions()->detach($this->collectPermissionIds($permissions));

        return $this;
    }

    /**
     * 同步更新Model权限
     *
     * @param array|string|Permission ...$permissions
     * @return $this
     */
    public function syncPermissions(...$permissions)
    {
        $this->permissions()->sync($this->collectPermissionIds($permissions));

        return $this;
    }

    /**
     * 检查是否有指定的权限
     *
     * @param array|string|Permission ...$permissions
     * @return bool
     */
    public function hasPermissions(...$permissions)
    {
        return $this->permissions()->wherePivotIn('permission_id', $this->collectPermissionIds($permissions))->exists();
    }

    public function hasAnyPermissions(...$permissions)
    {
        return $this->hasPermissions(...$permissions);
    }

    /**
     * 检查是否拥有所有的权限
     * @param array|string|Permission $permissions
     * @return bool
     */
    public function hasAllPermissions(...$permissions)
    {
        $permissions = $this->collectPermissionIds($permissions)->unique();

        return $this->permissions()->wherePivotIn('permission_id', $permissions)->count() === count($permissions);
    }

    /**
     * @param array|string ...$scopes
     * @return array
     */
    protected function collectScopes($scopes)
    {
        if (is_string($scopes[0])) {
            $scopes[0] = strtr($scopes[0], ',', '|');
            if (false !== strpos($scopes[0], '|')) {
                $scopes[0] = explode('|', trim($scopes[0], "'\" "));
            }
        }

        return collect($scopes)->flatten()->filter()->all();
    }

    /**
     * 检查是否有指定范围内的权限
     *
     * @param array|string ...$scopes
     * @return bool
     */
    public function hasScopesPermissions(...$scopes)
    {
        return $this->permissions()->whereIn('scope', $this->collectScopes($scopes))->exists();
    }

}