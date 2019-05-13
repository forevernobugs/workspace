<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tangtnglove <dai_hang_love@126.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

namespace app\api\validate;

use think\Validate;
use think\Lang;

class Login extends Validate
{
    protected $rule =   [
        'username'  => 'require',
        'password'  => 'require',  
    ];

    protected $message  =   [];

    // 加载语言包
    public function loadLang(){
    	$login_not_null = Lang::get('用户名或密码不能为空');
       
        $this->message = [
	        'username.require' => $login_not_null,
	        'password.require' => $login_not_null,
           
        ];
    }
    
   
}