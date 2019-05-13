<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 矢志bu渝 <745152620@qq.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Db;
use think\Loader;
use think\Request;

/**
 * 前台用户控制器
 * @author  tangtanglove
 */
class User extends Common
{
    /**
     * 退出处理
     */
    public function logout_post_json()
    {
    	$token = input('token');
    	if(cache('api_token_'.$token,null)) {
            return $this->restSuccess('退出成功！');
        } else {
            return $this->restError('退出失败，请重试！');
        }
    }
}
