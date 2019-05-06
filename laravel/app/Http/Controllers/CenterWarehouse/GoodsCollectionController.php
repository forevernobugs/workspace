<?php
namespace App\Http\Controllers\CenterWarehouse;

use App\Models\Goods\GoodsCollection;
use App\Http\Controllers\LoginRequireController;
use App\Models\OrderInfo\OrderLineInfo;
use App\Models\BasicInfo\Warehouse;

class GoodsCollectionController extends LoginRequireController
{
    /**
     * 获取集货单列表页
     */
    public function GoodsCollectionList(GoodsCollection $goods)
    {
        $list = $goods->getCollectionList($this->input);
        $warehouse = Warehouse::getWarehouseCodeName();
        
        foreach ($list['list'] as &$collection) {
            $collection['collect_status'] = $this->getStatus($collection['collect_status']);
            $arr = explode('-', $collection['collection_code']);
            // $collection['line_code'] = OrderLineInfo::getLineCode($arr['2'], $arr['1']);
            $collection['warehouse_name'] = isset($warehouse[$collection['warehouse_code']]) ? $warehouse[$collection['warehouse_code']] : '';
            $collection['aim_warehouse_name'] = isset($warehouse[$collection['destination_code']]) ? $warehouse[$collection['destination_code']] : '';
            $collection['line_code'] = substr($collection['aim_warehouse_name'], 0, 6).$arr['2'];
        }

        $list['title'] = [
            'id' => '集货单ID',
            'order_no' => '集货单编号',
            'warehouse_name' => '主仓库',
            'aim_warehouse_name' => '目的仓库',
            'line_code' => '线路',
            'goods_num' => '箱数',
            'collect_status' => '集货状态',
            'operator' => '操作人',
            'create_user' => '创建人',
            'create_time' => '创建时间',
            'remark' => '备注'
        ];

        $list['warehouse'] = $warehouse;
        return $this->returnList('加载成功', $list, $list['title'], '集货单列表');
    }

    /**
     * 获取集货单详情页
     */
    public function GoodsCollectionDetailList(GoodsCollection $goods)
    {
        $order_no = $this->getInput('order_no')->isString()->value();
        $detail = $goods->getCollectionOneByOrderNo($order_no);
        checkLogic(!empty($detail), '集货单未找到,请核实集货单');
        $detail->collect_status = $this->getStatus($detail->collect_status);
        $detail->list = $detail->detail;
        unset($detail->detail);

        $detail->title = [
            'order_no' => '集货单编号',
            'goods_code' => '集货商品码',
            'create_user' => '创建人',
            'create_time' => '创建时间'
        ];

        return $this->returnList('加载成功', $detail, $detail->title, '集货单详情');        
    }

    protected function getStatus($ostatus)
    {
        switch ($ostatus) {
            case '2':
                $status = '集货中';
                break;
            case '3':
                $status = '集货完成';
                break;
            case '4':
                $status = '装车中';
                break;
            case '5':
                $status = '装车完成';
                break;
            case '6':
                $status = '已发车';
                break;
            default:
                $status = '未知状态' . $ostatus;
                break;
        }
        return $status;
    }
}