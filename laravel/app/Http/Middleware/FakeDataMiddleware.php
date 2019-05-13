<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 20/03/2018
 * Time: 18:00
 */

namespace App\Http\Middleware;

use Closure;

class FakeDataMiddleware
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
        if ('debug' != _ENV_FILE_PATH_) {
            return hError('此接口仅在非正式环境运行');
        }
        return $next($request);
    }
}
