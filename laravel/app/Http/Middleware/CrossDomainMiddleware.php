<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/7
 * Time: 19:20
 */

namespace App\Http\Middleware;


use Closure;

/**
 * 测试环境下全部允许跨域访问
 * Class AllowCrossDomainMiddleware
 * @package App\Http\Middleware
 */
class CrossDomainMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
        $response->header('Access-Control-Allow-Credentials', 'true');
        $response->header('test', '123');
        if (hIsTestEnv()) {

        }
        return $response;

    }

}