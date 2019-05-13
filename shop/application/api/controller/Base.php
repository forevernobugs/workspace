<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tangtnglove <dai_hang_love@126.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Db;
use think\Loader;
use think\Request;

/**
 * 系统基础控制器：不需登录
 * @author  tangtnglove <dai_hang_love@126.com>
 */
class Base extends Rest
{
    /**
     * api用户登录
     * @author  tangtnglove <dai_hang_love@126.com>
     */
    public function login_post_json()
    {
        $username = input('post.username');
        $password = input('post.password');
        // 实例化验证器
        $validate = Loader::validate('Login');
        // 验证数据
        $data = ['username'=>$username,'password'=>$password];
        // 加载语言包
        $validate->loadLang();
        // 验证
        if(!$validate->check($data)){
            $this->restError($validate->getError());
        }
        $where['username'] = $username;
        $where['status']   = 1;
        $userInfo = Db::name('Users')->where($where)->find();
        if ($userInfo && $userInfo['password'] === minishop_md5($password,$userInfo['salt'])) {
            $user['id']        = $userInfo['id'];
            $user['salt']      = $userInfo['salt'];
            // 生成token
            $result['token'] = $this->creatToken($user);
            $result['uid']   = $user['id'];
            $result['code']  = 1;
            // 返回数据
            return json($result, 200);
        } else {
            return $this->restError('登陆失败！');
        }

    }

    /**
     * creatToken 生成TOKEN
     * @param  array &$user  用户信息数组
     * @return string        TOKEN
     * @author tangtnglove <dai_hang_love@126.com>
     */
    protected function creatToken(&$user){
        if (is_array($user)) {
            $data['uid']     = $user['id'];
            $data['token']   = minishop_md5($user['id'].time(),$user['salt']);
            //将TOKEN缓存
            cache('api_token_'.$data['token'], $data, config('token_valid'));
            return $data['token'];
        }
        return $this->restError('生成token失败！');
    }

    /**
     * checkLogin 非继承Common的类运用此方法判断登录
     * @param string $token TOKEN
     * @author tangtnglove <dai_hang_love@126.com>
     */
    protected function checkLogin($uid,$token){
        //判断TOKEN
        if (empty($token) || empty($uid)) {
            //返回登录页
            return $this->restError('未授权');
        }
        $cache = cache('api_token_'.$token);
        // 验证是否失效
        if(empty($cache)) {
           return  $this->restError('TOKEN失效');
        }
        // 验证身份
        if($cache[uid] !== $uid) {
            return $this->restError('身份不符！');
        }
    }

}
