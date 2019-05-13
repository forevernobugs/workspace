<?php

namespace App\Models\Logs;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Created by sublime3.
 * Index: zhangdahao
 * Date: 2018/4/19
 * Time: 15:31
 */
class OperationLog extends BaseModel
{
    protected $table = 't_operation_log';

    /**
     * 保存操作日志
     * @param $loginName
     * @param $remark
     */
    public static function saveLog($org_id, $user_name, $operation_type, $operation, $operation_level)
    {
        DB::table('t_operation_log')->insert([
            'org_id' => $org_id,
            'user_name' => $user_name,
            'operation_type' => $operation_type,
            'operation' => $operation,
            'opreation_time' => hDate(),
            'operation_level' => $operation_level,
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