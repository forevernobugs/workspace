<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MessageModel;

/**
 * 附加中间件
 * @author hzy
 */
class AdditionalMiddleware
{

    /**
     * 消息中间件
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (!method_exists($response, 'getData')) {
            return $response;
        }
        $data = $response->getData();
        if (!isset($data->data) || empty($data->data) || !isset($data->data->additional) || empty($data->data->additional)) {
            return $response;
        }
        $data->data->additional = $this->get_additional($request);
        $response->setData($data);
        return $response;
    }

    /**
     * 获取附加消息统计
     * @param type $request
     * @return type
     */
    public function get_additional($request)
    {
        $result = [
            'message_count' => 0
        ];
        // 验证用户ID
        $params = $request->all();
        if (empty($params) || !isset($params['user_id']) || empty($params['user_id'])) {
            return $result;
        }
        $result['message_count'] = MessageModel::get_count($params['user_id']);
        return $result;
    }
}
