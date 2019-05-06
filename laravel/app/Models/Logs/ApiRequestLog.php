<?php

namespace App\Models\Logs;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Created by sublime3.
 * Index: zhangdahao
 * Date: 2019/1/3
 * Time: 13:53
 */
class ApiRequestLog extends BaseModel
{
    protected $table = 't_api_request_log';

    /**
     * 保存操作日志
     * @param $loginName
     * @param $remark
     */
    public static function saveLog($log_type, $api_name, $api_url, $para, $response)
    {
        DB::table('t_api_request_log')->insert([
            'log_type' => $log_type,
            'api_name' => $api_name,
            'api_url' => $api_url,
            'para' => $para,
            'created_on' => time(),
            'response' => $response
        ]);
    }

    /**
     * 获取操作日志
     * @param $para
     * @return
     * @internal param $loginName
     */
    public static function getOperationLog($para)
    {
        return OperationLog::where($para)->get();
    }

}