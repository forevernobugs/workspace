<?php

namespace App\Http\Controllers\Mobile\V1_0;

use App\Http\Controllers\MobileApiController;
use App\Models\Logs\UserLoginLog;
use App\User;

/**
 * Created by Sublime3.
 * User: Zhangdahao
 * Date: 2018/5/6
 * Time: 18:02
 */
class UserController extends MobileApiController
{
    public function getUserDetail(User $user)
    {
        $user_id = $this->getInput('user_id')->isNumeric()->value();
        $userInfo = $user->getUserDetail($this->input['user_id']);
        return hSucceed('', $userInfo);
    }
}
