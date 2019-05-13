<?php
namespace App\Http\Controllers\CenterWarehouse;

use App\Models\OrderInfo\DispenseOrder as OrderDispense;
use App\Models\CenterWarehouse\DispenseOrder;
use App\Http\Controllers\LoginRequireController;

class DispenseOrderController extends LoginRequireController
{
    /**
     * 获取装车单列表
     */
    public function DispenseOrderList(DispenseOrder $dispense)
    {
        $list = $dispense->getDispenseList($this->input);

        $list['title'] = [
            'order_number' => '出库单号',
            'seal_number' => '封车码',
            'from_warehouse_name' => '主仓库',
            'to_warehouse_name' => '目的仓库',
            'plate_number' => '车牌',
            'contact_name' => '司机',
            'contact_tel' => '司机联系电话',
            'goods_num' => '箱数',
            'create_time' => '创建时间'
        ];
        return $this->returnList('加载成功', $list, $list['title'], '集货单列表');
    }

    /**
     * 获取装车单详情
     */
    public function DispenseOrderDetailList(OrderDispense $model)
    {
        $order_number = $this->getInput('order_number')->isString()->value();
        
        $detail = $model->getDispenseDetail($order_number);

        checkLogic(!empty($detail), '装车单未找到,请核实装车单');
        $detail->order_status = $this->getStatus($detail->order_status);
        $detail->list = object_to_array($detail->detail);
        unset($detail->detail);

        // 只统计正常集货的箱数
        foreach ($detail->list as $key => $c_val) {
            if (substr($c_val['collection_no'], 0, 1) != 'C') {
                unset($detail->list[$key]);
            }
        }

        $detail->title = [
            'collection_no' => '集货单号',
            'line_code' => '线路编号',
            'box_num' => '集货箱数'
        ];

        return $this->returnList('加载成功', $detail, $detail->title, '装车单详情');        
    }

    protected function getStatus($ostatus)
    {
        switch ($ostatus) {
            case '0':
                $status = '新建';
                break;
            case '1':
                $status = '装车中';
                break;
            case '2':
                $status = '已装车';
                break;
            case '3':
                $status = '已发车';
                break;
            case '4':
                $status = '已到达';
                break;
            case '5':
                $status = '收货中';
                break;
            case '6':
                $status = '已收货';
                break;
            case '-1':
                $status = '取消';
                break;
            default:
                $status = '未知状态' . $ostatus;
                break;
        }
        return $status;
    }
}