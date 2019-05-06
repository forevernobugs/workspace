<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 16/01/2018
 * Time: 17:55
 */

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InternalApiLogMiddleware
{
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

            DB::table('ruigu_api_request_log')->insert([
                'logType'=>2,
                'requestGuid'=>'',
                'requestApiName'=>$request->path(),
                'requestApiUrl'=>$request->url(),
                'createdOn'=>time(),
                'createdBy'=>'',
                'requestPara'=>$start.'-'.time().' '.$ip. ' '.$input,
                'response'=>$content
            ]);
        } catch (\Exception $ex) {
            Log::error('插入api请求日志失败：'.hFormatException($ex));
        }
        return $response;
    }
}
