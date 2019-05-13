<?php

namespace App\Models\Logs;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 2018/4/19
 * Time: 15:31
 */
class UserLoginLog extends BaseModel
{
    protected $table = 't_user_login_log';

    /**
     * 获取登陆日志
     * @param $loginName
     * @param $remark
     */
    public static function saveLog($loginName, $succeed, $remark)
    {
        DB::table('t_user_login_log')->insert([
            'login_name' => $loginName,
            'ip_address' => hGetClientIp(),
            'browser_type' => '',
            'browser_version' => '',
            'login_succeed' => $succeed,
            'login_time' => hdate(),
            'remark' => $remark,
        ]);
    }

    /**
     * 获取用户登陆日志
     * @param $para
     * @return
     * @internal param $loginName
     */
    public static function getUserLog($para)
    {
        return UserLoginLog::where($para)->get();
    }

}