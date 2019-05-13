<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tangtanglove <dai_hang_love@126.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

use think\Db;

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

/**
 * 执行自动加载方法
 * @author tangtanglove <dai_hang_love@126.com>
 */
autoload_function(ROOT_PATH.'application/common/function');

/**
 * 自动加载方法
 * @author tangtanglove <dai_hang_love@126.com>
 */
function autoload_function($path)
{
    $dir  = array();
    $file = array();
    recursion_dir($path,$dir,$file);
    foreach ($file as $key => $value) {
        if (file_exists($value)) {
            require_once($value);
        }
    }
    if(is_file(ROOT_PATH . 'data/install.lock')){
        // 加载主题里的方法
        $where['collection'] = 'indextheme';
        $theme_path = Db::name('KeyValue')->where($where)->value('value');
        if (file_exists(ROOT_PATH.'themes/'.$theme_path.'/functions.php')) {
            require_once(ROOT_PATH.'themes/'.$theme_path.'/functions.php');
        }
    }
}

/*
* 获取文件&文件夹列表(支持文件夹层级)
* path : 文件夹 $dir ——返回的文件夹array files ——返回的文件array 
* $deepest 是否完整递归；$deep 递归层级
*/
function recursion_dir($path,&$dir,&$file,$deepest=-1,$deep=0){
    $path = rtrim($path,'/').'/';
    if (!is_array($file)) $file=array();
    if (!is_array($dir)) $dir=array();
    if (!$dh = opendir($path)) return false;
    while(($val=readdir($dh)) !== false){
        if ($val=='.' || $val=='..') continue;
        $value = strval($path.$val);
        if (is_file($value)){
            $file[] = $value;
        }else if(is_dir($value)){
            $dir[]=$value;
            if ($deepest==-1 || $deep<$deepest){
                recursion_dir($value."/",$dir,$file,$deepest,$deep+1);
            }
        }
    }
    closedir($dh);
    return true;
}
