<?php
/**
 * RoleMiddleware.php
 * BaiSam admin
 *
 * Created by realeff on 2018/10/16.
 * Copyright ©2018 Jiangxi baisam information technology co., LTD. All rights reserved.
 */


namespace BaiSam\Middleware;


use Closure;
use BaiSam\Exceptions\UnauthorizedException;
use Illuminate\Contracts\Auth\Factory as Auth;

class VerifyRole
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param $request
     * @param \Closure $next
     * @param mixed ...$roles
     * @return mixed
     * @throws UnauthorizedException
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $this->auth->authenticate();

        if (!$user->hasAnyRoles(...$roles)) {
            throw new UnauthorizedException('User does not have the right roles.');
        }

        return $next($request);
    }
}