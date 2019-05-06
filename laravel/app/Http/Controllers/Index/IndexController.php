<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 17:09
 */

namespace App\Http\Controllers\Index;


use App\Http\Controllers\LoginRequireController;
use App\Models\Permission\Menu;

class IndexController extends LoginRequireController
{
    public function index()
    {
        $menuTree = Menu::getUseMenuTree($this->input['user_id']);
        return hSucceed('', ['tree'=>$menuTree]);
    }

    public function getUserSetting()
    {


    }

    public function saveUserSetting()
    {

    }



}