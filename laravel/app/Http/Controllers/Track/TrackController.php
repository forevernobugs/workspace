<?php
namespace App\Http\Controllers\Track;

use App\Models\Logs\StockRemain;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\LoginRequireController;
use App\Models\BasicInfo\Warehouse;

class TrackController extends LoginRequireController
{
    /**
     * 获取箱号追溯
     */
	public function boxTrack(StockRemain $track)
    {
        $list = $track->getBoxTrack($this->input);
        $arr = [];
        foreach ($list['list'] as $box_number => $logs) {
            foreach ($logs as $log) {
                $arr[$box_number]['box_number'] = $box_number;
                $arr[$box_number][$log->remain_type] = $log->action_time;
            }
        }

        $list['list'] = $arr;
        $list['title'] = [
            'box_number' => '箱号',
            'WARE_COLLECT' => '总仓集货',
            'WARE_SENT' => '总仓出库',
            'SUB_RECEIVE' => '分仓收货',
            'SUB_SENT' => '分仓出库',
            'USER_SIGN' => '客户签收'
        ];
        
        return $this->returnList('加载成功', $list, $list['title'], '箱号追溯');
    }

    //订单追溯
    public function productTrack(StockRemain $track)
    {
        //获取所有仓库code name   code=>name
        $warehouse = Warehouse::getWarehouseCodeName();

        $list = $track->getOrderBoxTrack($this->input);
        $arr = [];
        foreach ($list['list'] as $doubleKey => $logs) {
            foreach ($logs as $log) {
                $arr[$doubleKey]['order_number'] = $log->order_number;
                $arr[$doubleKey]['out_order'] = $log->out_order;
                $arr[$doubleKey]['box_number'] = $log->box_number;
                $arr[$doubleKey]['out_time'] = $log->out_time;
                $arr[$doubleKey]['send_time'] = $log->send_time;
                $arr[$doubleKey]['warehouse_code'] = '上虞总仓';
                $arr[$doubleKey]['destination_code'] = isset($warehouse[$log->destination_code]) ? $warehouse[$log->destination_code] : '未知';
                $arr[$doubleKey][$log->remain_type] = $log->action_time;
            }
        }
        
        $list['list'] = $arr;
        $list['warehouse'] = $warehouse;
        $list['title'] = [
            'order_number' => '订单号',
            'out_order' => '出库单号',
            'box_number' => '箱号',
            'out_time' => '出库时间',
            'send_time' => '排车时间',
            'warehouse_code' => '始发仓',
            'destination_code' => '目的仓',
            'WARE_COLLECT' => '总仓集货',
            'WARE_SENT' => '总仓出库',
            'SUB_RECEIVE' => '分仓收货',
            'SUB_SENT' => '分仓出库',
            'USER_SIGN' => '客户签收'
        ];
        
        return $this->returnList('加载成功', $list, $list['title'], '订单追溯');
    }

    // 查看箱子详情
    public function boxDetail(StockRemain $track)
    {
        $box_number = $this->getInput('box_number')->isString()->value();
        $list['list'] = $track->getBoxDetail($box_number, $this->input);
        $list['title'] = [
            'order_number' => '订单号',
            'out_order' => '出库单号',
            'box_number' => '箱号',
            'product_code'  =>  '产品号',
            'quantity' => '数量'
        ];

        return $this->returnList('加载成功', $list, $list['title'], '箱子明细');
    }
    // 查看箱子扫描明细
    public function productBoxDetail(StockRemain $track)
    {
        if(!isset($this->input['box_number']) || empty($this->input['box_number']) ){
            return hError('箱号不能为空');
        }
        $box_number = $this->input['box_number'];
        $list = $track->getOrderBoxTrack($this->input);
        $track::REMAIN_TYPE_MAP;
        $arr = [];
        foreach ($list['list'] as $doubleKey => $logs) {
            foreach ($logs as $k=>$log) {
                $arr[$k]['type'] = hMapValue($track::REMAIN_TYPE_MAP, $log->remain_type);
                $arr[$k]['user'] = $log->action_user;
                $arr[$k]['time'] = $log->action_time;
            }
        }
        $ret = DB::table('t_box_abnormal')->where('box_number',$box_number) ->orderBy('scan_time', 'desc')->get();
        foreach ($ret as $k=>$v){
            $arr[] = [
                'type' => hMapValue($track::SCAN_VALUE, $v->scan_type),
                'user' => $v->scan_user,
                'time' => $v->scan_time,
            ];
        }

        $list['box_number'] = $box_number;
        $list['list'] = $arr;
        $list['title'] = [
            'type' => '类型',
            'user' => '扫描人',
            'time' => '扫描时间',
        ];

        return $this->returnList('加载成功', $list, $list['title']);
    }
}