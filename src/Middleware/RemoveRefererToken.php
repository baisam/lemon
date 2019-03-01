<?php

namespace BaiSam\Middleware;

use Closure;

class RemoveRefererToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->removeCsrfToken($request);

        return $next($request);
    }

    /**
     * 移除csrf token
     * @param \Illuminate\Http\Request $request
     */
    protected function removeCsrfToken($request)
    {
        // 移除referer中的op,_token参数，避免back后退重现．
        $referrer = $request->headers->get('referer');
        if ($referrer) {
            $request->headers->set('referer', remove_query_arg(['op', '_token'], $referrer));
        }
    }
}
