<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tangtnglove <dai_hang_love@126.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Request;

/**
 * 系统通用控制器：需登录
 * @author  tangtnglove <dai_hang_love@126.com>
 */
class Common extends Rest
{
    /**
     * 初始化方法
     * @author tangtanglove
     */
    protected function _initRest()
    {
    	// 判断TOKEN
    	$token = input('token');
    	$uid   = input('uid');
    	if (empty($token)) {
    		// 返回登录页
    		return $this->error('未授权');
            die();
    	}
    	// 验证TOKEN
    	$cache = $this->checkToken($token);
    	if ($cache == -1) {
    		return $this->error('拒绝请求');
            die();
    	}elseif ($cache == 0) {
    		return $this->error('TOKEN失效');
            die();
    	}
        // 验证身份
        if ($cache != $uid) {
    		return $this->error('身份不符！');
            die();
        }
        // 定义当前用户ID
        if (!defined('UID')) {
            define('UID', $cache);
        }
    }

    /**
     * checkToken 验证TOKEN
     * @param  string $token 客户端TOKEN
     * @return integer (0为失效，1为通过，-1为失败)
     * @author tangtanglove
     */
    protected function checkToken($token){
    	$cache = cache('api_token_'.$token);
    	if (!empty($cache)) {
    		return $cache['token'] == $cache['token'] ? $cache['uid'] : -1;
    	}
    	return 0;
    }

}
