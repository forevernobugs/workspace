<?php

namespace App\Models\Logs;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

/**
 * Created by sublime3.
 * Index: zhangdahao
 * Date: 2018/5/16
 * Time: 15:31
 */
class StockRemain extends BaseModel
{
    protected $table = 't_stock_remain';
    public $timestamps = false;

    const REMAIN_TYPE_MAP = [
        'WARE_COLLECT'=>'总仓集货',
        'WARE_SENT'=>'总仓出库',
        'SUB_RECEIVE'=>'分仓收货',
        'SUB_SENT'=>'分仓发货',
        'USER_SIGN'=>'用户签收',
        'USER_RETURN'=>'用户退回',
        'SUB_RT_RECEIVE'=>'退回分仓收货',
        'SUB_RT_SENT'=>'退回分仓收货',
        'WARE_RECEIVE'=>'退回总仓收货',
        'SEND_EXPRESS'  =>  '分仓发快递'
    ];
    //类型值
    const SCAN_VALUE = [
        'COLLECT_LEAKAGE' => '总仓漏扫',
        'TRANSREGIONAL' => '串货',
        'REPEAT_OUTBOUND' => '重复出库',
        'NOT_LINE' => '未排车',
    ];

    /**
     * 保存在途日志
     * @param string $remainType 在途方式
     * @param string $toRemainingTarget 目标地点
     * @param string $userName 操作人
     * @param array $remainData 箱号和订单号关联 ['箱号'=>'订单号', '箱号2'=>'订单号']
     * @param null $actionTime
     */
    public static function roadRecord($remainType, $toRemainingTarget, $userName, $remainData, $actionTime = null)
    {
        checkLogic(key_exists($remainType, self::REMAIN_TYPE_MAP), '保存在途信息失败，非法在途类型');

        $remark = self::REMAIN_TYPE_MAP[$remainType];

        $insertingLog = [];
        $boxNumbers = [];
        $time = hdate();
        $actionTime = is_null($actionTime) ? $time : $actionTime;
        foreach ($remainData as $boxNumber => $deliveryNo) {
            $boxNumbers[] = $boxNumber;
            $insertingLog[] = [
                'box_number'=>$boxNumber,
                'remaining_target'=>$toRemainingTarget,
                'remain_type'=>$remainType,
                'related_order'=>$deliveryNo,
                'action_user'=>$userName,
                'action_time'=>$time,
                'create_time'=>$actionTime,
                'remark'=>$remark
            ];
        }
        DB::table('t_stock_remain_log')->insert($insertingLog);

        // 获取当想的在途情况
        $currentBoxRemain = DB::table('t_stock_remain')
            ->whereIn('box_number', $boxNumbers)
            ->pluck('box_number')
            ->toArray();

        //获取现有在途的箱号
        $newBoxRemain = array_diff($boxNumbers, $currentBoxRemain);
        //本次传入的箱号中有，但是原有箱号没有，则表明需要新增在途
        if (!empty($newBoxRemain)) {
            $toRemain = [];
            foreach ($newBoxRemain as $box) {
                $toRemain[] = [
                    'box_number' => $box,
                    'remaining_target' => $toRemainingTarget,
                    'remain_type' => $remainType
                ];
            }
            DB::table('t_stock_remain')->insert($toRemain);
        }

        //如果原有在途的箱号存在，则需要更新原有箱号的在途未知
        if (!empty($currentBoxRemain)) {
            DB::table('t_stock_remain')
                ->whereIn('box_number', $currentBoxRemain)
                ->update([
                    'remaining_target' => $toRemainingTarget,
                    'remain_type' => $remainType
                ]);
        }
    }

    public function getBoxTrack($params = [])
    {
        $condition = $this->getBoxCondition($params);
        if (empty($condition)) {
            return ['list' => [], 'page' => []];
        }
        //获取所有箱号并分页 
        if (isset($params['request_file'])) {
            $params['pagesize'] = 99999;
        }
        $boxs = DB::table('t_stock_remain as sr')
            ->join(
                't_order_box as ob',
                'sr.box_number',
                '=',
                'ob.box_number'
            )
            ->select('sr.box_number')
            ->where($condition)
            ->orderBy('ob.create_time', 'desc')
            ->paginate($params['pagesize'])
            ->toArray();
        $boxArr = [];
        foreach ($boxs['data'] as $box) {
            array_push($boxArr, $box->box_number);
        }

        //根据箱号查询log
        $logs = DB::table('t_stock_remain_log as srl')
            ->select(
                'srl.remain_type',
                'srl.box_number',
                'srl.action_time'
            )
            ->whereIn('srl.box_number', $boxArr)
            ->orderBy('srl.create_time', 'desc')
            ->get();
        $logArr = [];
        foreach ($logs as $log) {
            $logArr[$log->box_number][] = $log;
        }

        unset($boxs['data']);
        return ['list' => $logArr, 'page' => $boxs];
    }

    //订单追溯
    public function getOrderBoxTrack($params = [])
    {

        $condition = $this->getBoxCondition($params);

        //获取所有箱号并分页 
        if (isset($params['request_file'])) {
            $params['pagesize'] = 99999;
        }

        // 处理出库单条件
        if (isset($params['out_order']) && !empty($params['out_order'])) {
            $condition[] = ['ob.sm_order', 'like', "%{$params['out_order']}%"];
        }

        if (empty($condition)) {
            if(!isset($params['out_create_time_s']) && !isset($params['out_create_time_e']) && !isset($params['create_time_s']) && !isset($params['create_time_e'])){
                return ['list' => [], 'page' => []];
            }
        }

        $boxs_model = DB::table('t_stock_remain as sr')
            ->leftJoin(
                't_order_box as ob',
                'sr.box_number',
                '=',
                'ob.box_number'
            )->leftJoin(
                't_consign_order_consign_box as cocb',
                'sr.box_number',
                '=',
                'cocb.box_number'
            )
            ->select('sr.box_number')
            ->where($condition);

        $this->setWhereBetween($boxs_model, $params, 'ob.create_time', 'out_create_time_s', 'out_create_time_e');
        $this->setWhereBetween($boxs_model, $params, 'cocb.create_time', 'create_time_s', 'create_time_e');

        $boxs = $boxs_model->orderBy('ob.create_time', 'desc')
            ->paginate($params['pagesize'])
            ->toArray();
        $boxArr = [];
        foreach ($boxs['data'] as $box) {
            array_push($boxArr, $box->box_number);
        }

        //根据箱号查询log
        $logs = DB::table('t_stock_remain_log as srl')
            ->select(
                'ob.order_number',
                'ob.sm_order as out_order',
                'srl.remain_type',
                'srl.box_number',
                'srl.action_user',
                'srl.action_time',
                'ob.create_time as out_time',
                'cocb.create_time as send_time',
                'ob.destination_code as destination_code'
            )
            ->leftJoin('t_order_box as ob', 'ob.box_number', '=', 'srl.box_number')
            ->leftJoin('t_consign_order_consign_box as cocb', 'cocb.box_number', '=', 'srl.box_number')
            ->whereIn('srl.box_number', $boxArr)
            ->orderBy('srl.create_time', 'desc')
            ->get();
        $logArr = [];
        foreach ($logs as $log) {
            $logArr[$log->box_number][] = $log;
        }

        unset($boxs['data']);
        return ['list' => $logArr, 'page' => $boxs];
    }


    public function getProductTrack($params = [])
    {
        $condition = $this->getProductCondition($params);
        //获取所有箱号并分页
        if (isset($params['request_file'])) {
            $params['pagesize'] = 99999;
        }
        $boxs = DB::table('t_stock_remain as sr')
            ->join(
                't_order_box_detail as obd',
                'sr.box_number',
                '=',
                'obd.box_number'
            )->join(
                't_order_box as ob',
                'ob.box_number',
                '=',
                'obd.box_number'
            )
            ->select('sr.box_number')
            ->groupBy('sr.box_number')
            ->where($condition)
            ->orderBy('ob.box_number', 'desc')
            ->paginate($params['pagesize'])
            ->toArray();
        
        $boxArr = [];
        foreach ($boxs['data'] as $box) {
            array_push($boxArr, $box->box_number);
        }
        //根据箱号查询log
        $logs = DB::table('t_order_box_detail as obd')
            ->leftJoin(
                't_stock_remain_log as srl',
                'srl.box_number',
                '=',
                'obd.box_number'
            )
            ->select(
                'obd.product_code',
                'obd.box_number',
                'obd.out_order',
                'obd.order_number',
                'srl.remain_type',
                'srl.action_time'
            )
            ->whereIn('obd.box_number', $boxArr)
            ->orderBy('srl.create_time', 'desc')
            ->groupBy('obd.box_number')
            ->get();

        $logArr = [];
        foreach ($logs as $log) {
            $logArr[$log->product_code . '-' . $log->box_number][] = $log;
        }

        unset($boxs['data']);
        return ['list' => $logArr, 'page' => $boxs];
    }

    protected function getBoxCondition($params = [])
    {
        $condition[] = $this->buildPara($params, 'sr.box_number', 'like');
        $condition[] = $this->buildPara($params, 'ob.sm_order', 'like');
        $condition[] = $this->buildPara($params, 'ob.order_number', 'like');
        $condition[] = $this->buildPara($params, 'ob.destination_code', '=');

        return $this->unsetCondition($condition);
    }

    protected function getProductCondition($params = [])
    {
        $condition[] = $this->buildPara($params, 'sr.box_number', 'like');
        $condition[] = $this->buildPara($params, 'obd.out_order', 'like');
        $condition[] = $this->buildPara($params, 'obd.order_number', 'like');
        $condition[] = $this->buildPara($params, 'obd.product_code', 'like');
        return $this->unsetCondition($condition);
    }

    protected function unsetCondition($condition)
    {
        foreach ($condition as $key => $con) {
            if(!isset($con[0])){
                unset($condition[$key]);
            }
        }
        return $condition;
    }

    //查看箱子明细
    public function getBoxDetail($box_number, $params){
        $models = DB::table('t_order_box_detail')
            ->where('box_number', $box_number);
        // setWhere
        $this->setWhereLike($models, $params, 'product_code');
        return $models->get()->toArray();
    }
}
