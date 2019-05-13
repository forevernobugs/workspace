<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/18
 * Time: 17:11
 */

namespace App\Http\Controllers\SendExpress;


use App\Http\Controllers\LoginRequireController;

use App\Models\BasicInfo\WarehouseParameter;

use App\Models\BasicInfo\SendExpress;
class SendExpressController extends LoginRequireController
{
    const PRINT_SEND_STATUS = ['0' => '否', '1' => '是'];//打印，寄出状态

    /**
     * 快递单号信息列表页
     */
    public function getSendExpressList(SendExpress $express,WarehouseParameter $model)
    {
        $parameterList = $express->getSendExpressList($this->input);

        if (!empty($parameterList)) {
            foreach ($parameterList['list'] as $key => &$v) {
                $v['status'] = hMapValue(self::PRINT_SEND_STATUS, $v['status']);//打印状态
                $v['is_send_off'] = hMapValue(self::PRINT_SEND_STATUS, $v['is_send_off']);//寄出状态
            }
        }
        $parameterList['title'] = [
            'related_no' => '关联单号',
            'parent_no' => 'tms单号',
            'child_no' => '快递单号',
            'box_number' => '箱号',
            'status' => '是否打印',
            'print_time'=> '打印时间',
            'is_send_off'=> '是否寄出',
        ];

        return $this->returnList('加载成功', $parameterList, $parameterList['title'], '快递信息列表');
    }
}