<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tangtanglove <dai_hang_love@126.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\Controller;
use think\Db;

/**
 * 系统中心控制器
 * @author  完美°ぜ界丶
 */
class Index extends Common
{
    public function index()
    {

        $user_number = Db::name('Users')->field(true)->select();
        $post_number = Db::name('Posts')->field(true)->select();
        $post_num    = count($post_number);
        $user_num    = count($user_number);
        $version     = MINISHOP_VERSION;
        //新版本检测
        $new_ver     = $this->getVersion();
        
        $this->assign('version',$version);
        $this->assign('new_ver',$new_ver);

        $this->assign('user_num',$user_num);
        $this->assign('post_num',$post_num);
        return $this->fetch('index');
    }

    /**
     * @author vaey
     * 系统更新检测
     */
    private function getVersion()
    {
        //暂无
        return null;
    }

}
