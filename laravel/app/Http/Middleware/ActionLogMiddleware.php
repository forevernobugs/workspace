<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 22/06/2017
 * Time: 16:40
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActionLogMiddleware
{
    /**
     *  日志中间件，记录每一次处理时间
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $start = time();
        $ip = $request->ip();
        $input = json_encode($request->input());
        $response = $next($request);

        try {
            if ($response instanceof JsonResponse) {
                $content = json_encode($response->getData());
            } elseif ($response instanceof Response) {
                $content = $response->content();
            } else {
                $content = '$response 不是JsonResponse或者Response的实例';
            }

            DB::table('t_api_request_log')->insert([
                'log_type'=>2,
                'api_name'=>$request->path(),
                'api_url'=>$request->url(),
                'created_on'=>time(),
                'para'=>$start.'-'.time().' dasdaip'.$ip. ' '.$input,
                'response'=>$content
            ]);
        } catch (\Exception $ex) {
            Log::error('插入api请求日志失败：'.hFormatException($ex));
        }
        return $response;
    }
}
