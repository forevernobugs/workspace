<?php

namespace App\Http\Controllers\Mobile\V1_0;

use App\Http\Controllers\MobileApiController;
use App\Models\BasicInfo\Warehouse;

/**
 * Created by Sublime3.
 * User: Zhangdahao
 * Date: 2018/5/14
 * Time: 19:00
 */
class WarehouseController extends MobileApiController
{
    public function getWarehouseAll(Warehouse $model)
    {
        $list = $model->getWarehouseAll();
        return hSucceed('', $list);
    }

    public function getTransportInfo(Warehouse $model)
    {
        $this->getInput('warehouse_code')->isString()->check();
        $returnData = $model->getTransportInfo($this->input);
        checkLogic(!empty($returnData), '未找到此分仓的干线配送配置');
        return hSucceed('', $returnData);
    }
}
