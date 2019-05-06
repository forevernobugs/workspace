<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use App\User;

/**
 * 移动端请求验证中间件
 * @author zhangdahao
 */
class MobileApiMiddleware
{

    /**
     * 移动端请求验证中间件
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $params = $request->all();
        // 验证参数
        $requisite = ['rg_ver', 'rg_id', 'token', 'user_id'];
        foreach ($requisite as $key) {
            if (!isset($params[$key]) || empty($params[$key])) {
                return hError($key.'不能为空');
            }
        }

        //校验用户
        $token = DB::table('t_user')->where('id', $params['user_id'])->value('token');
        checkLogic($token === $params['token'], '用户认证失败');

        return $next($request);
    }
}
