<?php
/**
 * HasRoles.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/11.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Support\Traits;


use BaiSam\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

trait HasRoles
{
    use HasPermissions {
        HasPermissions::hasPermissions as hasDirectPermissions;
        HasPermissions::hasAllPermissions as hasAllDirectPermissions;
        HasPermissions::hasScopesPermissions as hasDirectScopesPermissions;
    }

    /**
     * 监听Model移除事件
     */
    public static function bootHasRoles()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->roles()->detach();
        });
    }

    /**
     * A model may have multiple roles.
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles()
    {
        return $this->morphToMany(
            Role::class,
            'model',
            config('permission.model_roles_table')
        );
    }

    /**
     * 应用角色范围查询
     *
     * @param Builder $query
     * @param string|array|Role|Collection $roles
     * @return Builder
     */
    public function scopeRole(Builder $query, $roles)
    {
        $roles = $this->collectRoleIds($roles);

        if (empty($roles)) {
            return $query;
        }

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->whereIn(config('permission.model_roles_table').'.role_id', $roles);
        });
    }

    /**
     * 获取角色模型
     * @return Role
     */
    protected function getRoleModel()
    {
        $model = new Role();
        $model->setDomain($this->domain);

        return $model;
    }

    /**
     * @param array|string|Role ...$roles
     * @return array
     */
    protected function collectRoleIds(...$roles)
    {
        if (is_string($roles[0])) {
            $roles[0] = strtr($roles[0], ',', '|');
            if (false !== strpos($roles[0], '|')) {
                $roles[0] = explode('|', trim($roles[0], "'\" "));
            }
        }

        $model = $this->getRoleModel();

        return collect($roles)->flatten()->map(function ($role) use($model) {
            if ($role instanceof Role) {
                return $role->id;
            }

            if (is_numeric($role)) {
                return $role;
            }

            $role = $model->findByName($role);

            return $role ? $role->id : 0;
        })->filter()->all();
    }

    /**
     * 给Model附加角色
     *
     * @param array|string|Role ...$roles
     *
     * @return $this
     */
    public function attachRoles(...$roles)
    {
        $this->roles()->sync($this->collectRoleIds($roles), false);

        return $this;
    }

    /**
     * 移除Model角色
     *
     * @param array|string|Role ...$roles
     *
     * @return $this
     */
    public function removeRoles(...$roles)
    {
        $this->roles()->detach($this->collectRoleIds($roles));

        return $this;
    }

    /**
     * 同步更新Model角色
     *
     * @param array|string|Role ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        $this->roles()->sync($this->collectRoleIds($roles));

        return $this;
    }

    /**
     * 检查是否有指定的角色
     *
     * @param array|string|Role ...$roles
     * @return bool
     */
    public function hasRoles(...$roles)
    {
        return $this->roles()->wherePivotIn('role_id', $this->collectRoleIds($roles))->exists();
    }

    public function hasAnyRoles(...$roles)
    {
        return $this->hasRoles(...$roles);
    }

    /**
     * 检查是否拥有所有指定的角色
     *
     * @param array|string|Role ...$roles
     * @return bool
     */
    public function hasAllRoles(...$roles)
    {
        $roles = $this->collectRoleIds($roles)->unique();

        return $this->roles()->wherePivotIn('role_id', $roles)->count() === count($roles);
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

        // 获取权限相关的角色
        $rolesWithPermissions = $this->getRolesWithPermissions($permissions);

        return $query->where(function ($query) use($permissions, $rolesWithPermissions) {
            $query->whereHas('permissions', function ($query) use ($permissions) {
                //直接授权
                $query->whereIn(config('permission.model_permissions_table').'.permission_id', $permissions);
            });
            if (count($rolesWithPermissions) > 0) {
                // 间接授权
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions) {
                    $query->whereIn(config('permission.model_roles_table').'.role_id', $rolesWithPermissions);
                });
            }
        });
    }

    protected function getRolesWithPermissions($permissions)
    {
        $permissions = $this->collectPermissionIds($permissions);

        $model = $this->getPermissionModel();

        return collect($permissions)->map(function ($permission) use($model) {
            $permission = $model->find($permission);

            return $permission ? $permission->roles->pluck('id') : [];
        })->flatten()->unique();
    }

    public function hasPermissions(...$permissions)
    {
        if ($this->hasDirectPermissions(...$permissions)) {
            return true;
        }

        // 获取权限相关的角色
        $rolesWithPermissions = $this->getRolesWithPermissions($permissions);

        return count($rolesWithPermissions) > 0 && $this->hasRoles($rolesWithPermissions);
    }

    public function hasAllPermissions(...$permissions)
    {
        // 获取权限相关的角色
        $rolesWithPermissions = $this->getRolesWithPermissions($permissions);

        return $this->hasAllDirectPermissions(...$permissions) &&
            (count($rolesWithPermissions) > 0 && $this->hasAllRoles($rolesWithPermissions));
    }

    protected function getRolesWithScopesPermissions($scopes)
    {
        $model = $this->getPermissionModel();
        $permissions = $model->whereIn('scope', $this->collectScopes($scopes))->get();

        return collect($permissions)->map(function ($permission) {
            return $permission ? $permission->roles->pluck('id') : [];
        })->flatten()->unique();
    }

    public function hasScopesPermissions(...$scopes)
    {
        if ($this->hasDirectScopesPermissions(...$scopes)) {
            return true;
        }

        // 获取指定范围权限相关的角色
        $rolesWithScopesPermissions = $this->getRolesWithScopesPermissions($scopes);

        return count($rolesWithScopesPermissions) > 0 && $this->hasRoles($rolesWithScopesPermissions);
    }

}