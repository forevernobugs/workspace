<?php

namespace App\Http\Controllers\Mobile\V1_0;

use App\Http\Controllers\MobileApiController;
use App\Models\BasicInfo\TransportCrop;

/**
 * Created by Sublime3.
 * User: Zhangdahao
 * Date: 2018/5/14
 * Time: 19:00
 */
class TransportController extends MobileApiController
{
    public function getTransportAll(TransportCrop $model)
    {
        $list = $model->getTransportAll();
        return hSucceed('', $list);
    }
}
