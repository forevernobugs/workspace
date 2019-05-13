<?php

namespace App\Http\Controllers\Open;

use App\Http\Controllers\SuperController;
use App\Models\Logs\UserLoginLog;
use App\Models\Permission\Menu;
use App\User;

/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 16:14
 */
class LoginController extends SuperController
{
    public function login()
    {
        $userName = $this->getInput('username', '请输入用户名')->isString()->value();
        $password = $this->getInput('password', '请输入密码')->isString()->value();

        $userInfo = User::checkUser($userName, $password);

        if (is_string($userInfo)) {
            UserLoginLog::saveLog($userName, false, $userInfo);
            return hError($userInfo);
        } else {
            $newToken = $userInfo->updateToken();
            //获取menu
            $menuTree = Menu::getUseMenuTree($userInfo->id);
            $loginDate = [
                'user_id' => $userInfo->id,
                'token' => $newToken,
                'username' => $userInfo->username,
                'tree' => $menuTree,
            ];
            return hSucceed('登陆成功', $loginDate);
        }
    }

    public function resetPassword()
    {
        //TODO
    }


}