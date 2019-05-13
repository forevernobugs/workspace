<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * 请求验证中间件
 * @author Jason
 */
class VerifyMiddleware
{

    /**
     * 请求验证中间件
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $params = $request->all();
        // 验证参数
        $requisite = ['user_id', 'token', 'time', 'sign', 'version', 'device_type'];
        foreach ($requisite as $key) {
            if (!isset($params[$key]) || empty($params[$key])) {
                return hError('值不能为空');
            }
        }
        // 验证请求时间
        if (abs(time() - intval($params['time'])) > 6) {
            return hError('请求超时');
        }
        // 验证版本及设备类型（暂无）


        // 验证用户信息
        $user = DB::table('t_user')->where('id', $params['user_id'])
            ->select('token', 'enabled', 'is_new')->first();

        if (empty($user)) {
            return hError('用户不存在');
        }
        if (empty($user->enabled)) {
            return hError('用户已停用'.json_encode($user));
        }
        // 验证数据库token
        if (empty($user->token)) {
            return hError('用户token为空');
        }
        // 验证签名
        if (strtolower(md5($user->token . $params['time'] . $params['time'] . $user->token)) != $params['sign']) {
            return hError('签名错误');
        }
        //验证是否是新用户
        if (1 == $user->is_new && !strstr($request->getRequestUri(), 'system/update_password')) {
            return response()->json(['code' => 302, 'msg' => '新用户必须重新修改密码', 'url' => 'system/update_password']);
        }
        return $next($request);
    }
}
