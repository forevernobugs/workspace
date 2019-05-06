<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 01/09/2017
 * Time: 10:04
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DirectVerifyController extends SuperController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->middleware('direct');
        $this->middleware('actionlog');
    }
}
