<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 01/09/2017
 * Time: 10:01
 */

namespace App\Http\Middleware;

use App\Common\Signature;
use Closure;
use Illuminate\Support\Facades\Log;

class DirectVerifyMiddleware
{
    public function handle($request, Closure $next)
    {
        //直接验证，无需登录直接验证
        $para = $request->all();
        checkLogic(isset($para['signature']), '请传入签名');
        checkLogic(isset($para['timestamp']), '请传入时间戳');
        checkLogic(isset($para['key']), '请传入key');
        checkLogic(isset($para['nonce']), '请传入nonce');

        $time =  time();
        if (abs($time - $para['timestamp']) > 60) {
            Log::info($time.'   '. $para['timestamp']);
            return response()->json(['code' => 401, 'msg' =>'请求超时'.$time]);
        }

        $secret = env('Fin_Sign_Secret', '');
        $key = env('Fin_Sign_Key', '');
        
        checkLogic(!empty($secret), '服务器验证配置错误');
        checkLogic(!empty($key), '服务器验证配置错误,Key not found');

        $signature = Signature::generate(['nonce'=>$para['nonce'],
            'key'=>$key, 'timestamp'=>$para['timestamp'] ,'secret'=>$secret]);

        if ($signature != $para['signature']) {
            Log::info($signature.'   '. $para['signature']);
            return hError('验证失败'.$signature.' 传入：'.$para['signature']);
        }
        return $next($request);
    }
}
