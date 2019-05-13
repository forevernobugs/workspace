<?php

namespace App\Http\Controllers\Mobile\V1_0;

use App\Http\Controllers\MobileApiController;
use App\Http\Controllers\SuperController;
use App\Models\Logs\UserLoginLog;
use App\User;

/**
 * Created by Sublime3.
 * User: Zhangdahao
 * Date: 2018/5/3
 * Time: 16:02
 */
class LoginController extends SuperController
{
    public function login()
    {
        $userName = $this->getInput('login_name')->isString()->value();
        $password = $this->getInput('user_pass')->isString()->value();

        // 查询是否可以继续登录
        $client_ip = hGetClientIp();
        if ($client_ip != '180.169.216.194') {
            $time = time() - 1800;
            $date_time = date('Y-m-d H:i:s', $time);
            $where = [
                ['ip_address','=',$client_ip],
                ['login_name','=',$userName],
                ['login_time','>',$date_time],
                ['login_succeed','=',0]
            ];
            $error_count = UserLoginLog::getUserLog($where);
            checkLogic(count($error_count) <= 10, '密码错误太频繁了，请30分钟后再试！');
        }

        //核实用户
        $userInfo = User::checkUser($userName, $password);
        if (is_string($userInfo)) {
            UserLoginLog::saveLog($userName, false, $userInfo);
            return hError($userInfo);
        } else {
            $newToken = $userInfo->updateToken();
            UserLoginLog::saveLog($userName, true, '登录成功');
            $data = [
                'user_id'    =>    $userInfo->id,
                'username'    =>    $userInfo->username,
                'login_name'    =>    $userInfo->login_name,
                'mobile'    =>    $userInfo->mobile,
                'email'    =>    $userInfo->email,
                'token'    =>    $newToken,
            ];
            return hSucceed('登陆成功', $data);
        }
    }
}
