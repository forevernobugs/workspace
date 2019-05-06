<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * 基本控制器,需要被验证权限
 */
class PermissionRequiredController extends LoginRequireController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->middleware('permission');
        //$this->middleware('menubadge');
    }
}
