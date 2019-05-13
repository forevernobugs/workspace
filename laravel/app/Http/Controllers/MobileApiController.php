<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 21:45
 */

namespace App\Http\Controllers;

use App\Http\Controllers\SuperController;
use Illuminate\Http\Request;

class MobileApiController extends SuperController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->middleware('mobileapi');
        $this->middleware('crossdomain');
    }
}
