<?php

namespace App\Http\Controllers;

use App\Models\Logs\ApiRequestLog;
use App\Models\OrderInfo\ConsignOrder;
use App\Models\OrderInfo\OrderBox;
use Illuminate\Support\Facades\DB;

/**
 * Created by Sublime3
 * User: zhangdahao
 * Date: 2018/10/15
 * Time: 15:10
 */
class MpApiController extends SuperController
{
    /**
     * 请求ERP接口  扫描箱号获取改送日期
     * @param string $box_number
     * @return array
     */
    public function verifyBoxBack($box_number = ''){
        $result = curl_post_erpapi('/delivery/verify_box_back',['box_number' => $box_number]);
        return $result;
    }

    /**
     * 获取装车单列表
     * @param int $user_id
     * @param array $params
     * @return array
     */
    public function consignOrderList($user_id = 0,$params = []){
        $consignOrder = new ConsignOrder();
        $where = [];
        $param['type'] = isset($params['type']) ? $params['type'] : 1;
        $param['created_start'] = isset($params['created_start']) ? $params['created_start'] : '';
        $param['created_end'] = isset($params['created_end']) ? $params['created_end'] : '';
        $param['check_status'] = isset($params['check_status']) ? $params['check_status'] : '';
        $param['waybill_status'] = isset($params['waybill_status']) ? $params['waybill_status'] : '';
        if($param['type'] == 1){
            $type = 1;
        }else{
            $type = 2;
        }
        if($param['created_start']){
            $where[] = ['co.created','>=',$param['created_start']];
        }
        if($param['created_end']){
            $where[] = ['co.created','<=',date('Y-m-d 23:59:59',strtotime($param['created_end']))];
        }
        if(!$param['created_start'] && !$param['created_end']){
            $where[] = ['co.created','>=',date('Y-m-d',strtotime('-7 days'))];
        }
        if($param['waybill_status']){
            $where[] = ['co.waybill_status','=',$param['waybill_status']];
        }
        $waybill_data = $consignOrder->getWaybillList($user_id,$where,$type);
        if(empty($waybill_data)){
            return hApiError('没有数据');
        }else{
            return hApiSucceed('查询成功',object_to_array($waybill_data));
        }
    }
    /**
     * 装车单详情
     * @param string $waybill_no
     * @param string $check_type
     * @return array
     * @throws \App\Exceptions\KnownLogicException
     */
    public function consignInfo($waybill_no = '',$check_type = 'AGAIN'){
        checkLogic($waybill_no,'运货单号不能为空');
        $consignOrder = new ConsignOrder();
        $consign_count = DB::table('t_consign_order_check')
            ->where('waybill_no','=',$waybill_no)
            ->count();
        // $this->getCheckConsignWayillInfo($waybill_no);

        $consign_info = $consignOrder->getConsignInfo($waybill_no,$this->user_id ? $this->user_id : 1);
        if($consign_info){
            $check_data = $consignOrder->getCheckList($waybill_no,$check_type,$this->user_id ? $this->user_id : 1);
            $check_list = [];
            if ($check_data){
                $check_data = object_to_array($check_data);
                foreach ($check_data as $v){
                    switch ($check_type){
                        case 'AGAIN':
                        case 'ALL_REJECT': $check_number = $v['box_number']; break;
                        case 'PART_REJECT': $check_number = $v['split_sku'] ? $v['split_sku'] : $v['box_number']; break;
                        case 'AFTER': $check_number = $v['related_no']; break;
                        default: $check_number = '';
                    }
                    $check_list[] = [
                        'delivery_no' => $v['delivery_no'],
                        'check_number' => $check_number,
                        'plan_num' => $v['plan_num'],
                        'real_num' => $v['real_num'],
                        'is_again_time' => $v['is_again_time'],
                        'is_check' => $v['is_check'],
                    ];
                }
            }
            $consign_info->check_type = $check_type;
            $consign_info->check_data = $check_list;
            $consign_info = object_to_array($consign_info);
            return hApiSucceed('success',$consign_info);
        }else{
            return hApiError('没有数据');
        }
    }
    /**
     * 查看装车单商品信息
     * @param string $check_type
     * @param string $delivery_no
     * @param string $search_code
     * @return array
     * @throws \App\Exceptions\KnownLogicException
     */
    public function productInfo($check_type = 'AFTER',$delivery_no = '',$search_code = ''){
        checkLogic($delivery_no,'运货单号不能为空');
        $consignOrder = new ConsignOrder();
        switch ($check_type){
            case 'ALL_REJECT':
                $product_info = $consignOrder->getProductByBox($delivery_no,$search_code);
                if($product_info){
                    $price_info = curl_post_erpapi('/delivery/get_price',['product_info' => $product_info])['data'];
                    foreach ($product_info as &$v){
                        $v['price'] = isset($price_info[$v['product_code']]['price']) ? $price_info[$v['product_code']]['price'] : '';
                        $v['picture'] = isset($price_info[$v['product_code']]['picture']) ? $price_info[$v['product_code']]['picture'] : '';
                        $v['product_name'] = isset($price_info[$v['product_code']]['product_name']) ? $price_info[$v['product_code']]['product_name'] : '';
                        $v['unit_name'] = isset($price_info[$v['product_code']]['unit_name']) ? $price_info[$v['product_code']]['unit_name'] : '';
                    }
                }
                break;
            case 'PART_REJECT':
                if(strpos($search_code,'RB') !== false){
                    $product_info = $consignOrder->getProductByBox($delivery_no,$search_code);
                }else{
                    $product_info = $consignOrder->getProductInfo($delivery_no,$search_code);
                }
                if($product_info){
                    $price_info = curl_post_erpapi('/delivery/get_price',['product_info' => $product_info])['data'];
                    foreach ($product_info as &$v){
                        $v['price'] = isset($price_info[$v['product_code']]['price']) ? $price_info[$v['product_code']]['price'] : '';
                        $v['picture'] = isset($price_info[$v['product_code']]['picture']) ? $price_info[$v['product_code']]['picture'] : '';
                        $v['product_name'] = isset($price_info[$v['product_code']]['product_name']) ? $price_info[$v['product_code']]['product_name'] : '';
                        $v['unit_name'] = isset($price_info[$v['product_code']]['unit_name']) ? $price_info[$v['product_code']]['unit_name'] : '';
                    }
                }
                break;
            case 'AFTER':
                $product_info = curl_post_erpapi('/aftersales/get_product_after',['aftersales_no' => $search_code])['data'];
                $check_info = $consignOrder->getCheckInfo($delivery_no,$search_code);
                foreach ($product_info as &$v){
                    $v['price'] = '';
                    $v['is_check'] = object_get($check_info,'is_check',0);
                    $v['check_user'] = object_get($check_info,'check_user','');
                    $v['check_time'] = object_get($check_info,'check_time','');
                }
                break;
            default:
                $product_info = [];
        }
        if($product_info){
            return hApiSucceed('success',$product_info);
        }else{
            return hApiError('没有数据');
        }
    }

    /**
     * 获取验货信息
     * @param string $waybill_no
     * @param string $verify_code
     * @return array
     * @throws \App\Exceptions\KnownLogicException
     */
    public function verifyBack($waybill_no = '',$verify_code = '', $verify_num = 0){
        checkLogic($waybill_no,'装车单号为空');
        checkLogic($verify_code,'编码为空');

        $consignOrder = new ConsignOrder();
        $verify_info = [];
        if(strpos($verify_code,'RB') !== false){    //箱号
            $product_info = $consignOrder->getVerifyProduct($waybill_no,'box_number',$verify_code,$this->user_id);
            if(empty($product_info)){
                return hApiError('该货码不存在，或已验货');
            }
            $verify_info['check_type'] = hMapValue($consignOrder::CHECK_TYPE,$product_info['check_type']);
            $verify_info['delivery_no'] = $product_info['delivery_no'];
            $verify_info['order_number'] = $product_info['related_no'];

            $verify_info['verify_code'] = $verify_code.'(1箱'.$product_info['plan_num'].'件)';
            $verify_info['verify_name'] = '';

            $reserve_time = date('Y-m-d',strtotime($product_info['is_again_time']));
            $day = (strtotime($reserve_time) - strtotime(date('Y-m-d')))/86400;
            if($product_info['check_type'] == 'AGAIN'){
                $verify_info['again_time'] = $reserve_time."(今天后第".$day."天)";
            }else{
                $verify_info['again_time'] = '';
            }
        }elseif(strpos($verify_code,'AS') !== false){    //提货单号
            $product_info = $consignOrder->getVerifyProduct($waybill_no,'related_no',$verify_code,$this->user_id, $verify_num);
            if(empty($product_info)){
                return hApiError('该货码不存在，或已验货');
            }
            $verify_info['check_type'] = hMapValue($consignOrder::CHECK_TYPE,$product_info['check_type']);
            $verify_info['delivery_no'] = $product_info['delivery_no'];
            $verify_info['order_number'] = $product_info['related_no'];
            $verify_info['verify_name'] = '';
            if (strpos($verify_code,'ASAP') !== false) {
                $url = env('APS_API_URL','').'aftersales/internal/order/getShopByOrders?as_numbers='.$verify_code;
                $res = file_get_contents($url);
                //api日志
                ApiRequestLog::saveLog(
                    1,
                    'getSupplierInfo',
                    env('APS_API_URL','').'aftersales/internal/order/getShopByOrders',
                    $verify_code,
                    json_encode($res)
                );
                $result = json_decode($res, 1);

                if ($result['code'] == 200) {
                    if (!empty($result['data'])) {
                        $verify_info['verify_name'] = $result['data'][$verify_code]['name'];
                    }
                }
            }
            $verify_info['verify_code'] = $product_info['split_sku'].'(1件)';
            $verify_info['again_time'] = '';
        }else{  //sku
            $product_info = $consignOrder->getVerifyProduct($waybill_no,'split_sku',$verify_code,$this->user_id, $verify_num);
            if(empty($product_info)){
                return hApiError('该货码不存在，或已验货');
            }
            $verify_info['check_type'] = hMapValue($consignOrder::CHECK_TYPE,$product_info['check_type']);
            $verify_info['delivery_no'] = $product_info['delivery_no'];
            $verify_info['order_number'] = $product_info['related_no'];
            $verify_info['verify_name'] = '';

            $verify_info['verify_code'] = $product_info['split_sku'].'(1件)';
            $verify_info['again_time'] = '';
        }
        $verify_data = $consignOrder->getVerifyList($waybill_no);

        $verify_info['verify_count'] = count($verify_data);
        if (empty($verify_data)){
            $verify_info['verify_count'] = 0;
            $verify_info['verify_data'] = [];
        }else{
            $verify_info['verify_count'] = count($verify_data);
            foreach ($verify_data as $k=>$v){
                if($v['check_type'] == 'AGAIN' || $v['check_type'] == 'ALL_REJECT'){
                    $verify_info['verify_data'][] = [
                        'verify_type' => hMapValue($consignOrder::CHECK_TYPE,$v['check_type']),
                        'verify_code' => $v['box_number'],
                        'verify_num' => '1箱'.$v['real_num'].'件',
                    ];
                }elseif($v['check_type'] == 'AFTER'){
                    $verify_info['verify_data'][] = [
                        'verify_type' => hMapValue($consignOrder::CHECK_TYPE,$v['check_type']),
                        'verify_code' => $v['related_no'],
                        'verify_num' => '1箱'.$v['real_num'].'件',
                    ];
                }else{
                    $is_sku = ($v['real_num'] != $v['plan_num']) ? 1 : 0;
                    $verify_info['verify_data'][] = [
                        'verify_type' => hMapValue($consignOrder::CHECK_TYPE,$v['check_type']),
                        'verify_code' => $is_sku == 1 ? $v['split_sku'] : $v['box_number'],
                        'verify_num' => $is_sku == 1 ? $v['real_num'].'件' : '1箱'.$v['plan_num'].'件',
                    ];
                }
            }
        }

        if($verify_info){
            return hApiSucceed('success',$verify_info);
        }else{
            return hApiError('没有数据');
        }
    }


    /**
     * 获取装车单验车明细(CRM调用只获取获取数据不写入)
     * @DateTime  2018-10-24
     * @param $waybill_no  装车单号
     * @return array
     */
    public function getCheckWayillInfo($waybill_no){
        // 请求ERP接口 获取验货信息
        $result = curl_post_erpapi('consign_order/to_tms_check_waybill_info', ['waybill_no' => $waybill_no]);
        if ($result['code'] != 200) {
            return hApiError($result['msg']);
        }
        if (is_string($result['data'])) {
             return hApiError($result['data']);
        }

        $consignOrder = new ConsignOrder();
        $order_arr = [];        //需要获取箱号的订单号（改日送,整单拒收）
        $part_reject_arr = [];  //需要获取箱号的订单号（部分拒收）
        $order_info = [];
        $is_again_list = [];
        $is_all_reject_list = [];
        $is_part_reject_order = [];
        $is_after_order = [];

        $is_again = [
            'order_count'   =>  0,
            'box_count'   =>  0,
            'quantity'   =>  0
        ];
        $is_all_reject = [
            'order_count'   =>  0,
            'box_count'   =>  0,
            'quantity'   =>  0
        ];
        $is_part_reject = [
            'order_count'   =>  0,
            'quantity'   =>  0
        ];
        $is_after = [
            'order_count'   =>  0,
            'quantity'   =>  0
        ];
        $back_consign = [];

        //装车单验货详情
        $waybill_info = [
            'waybill_no'    =>  $result['data']['waybill_no'],
            'car_name'    =>  '',
            'driver_name'    =>  '',
            'real_money'    =>  $result['data']['real_money'],
            'cash_money'    =>  $result['data']['cash_money'],
            'againCount'    =>  '',
            'againCheckCount'    =>  '',
            'againNum'    =>  '',
            'againCheckNum'    =>  '',
            'allRejectCount'    =>  '',
            'allRejectCheckCount'    =>  '',
            'allRejectNum'    =>  '',
            'allRejectCheckNum'    =>  '',
            'partRejectCount'    =>  '',
            'partRejectCheckCount'    =>  '',
            'partRejectNum'    =>  '',
            'partRejectCheckNum'    =>  '',
            'afterCount'    =>  '',
            'afterCheckCount'    =>  '',
            'afterNum'    =>  '',
            'afterCheckNum'    =>  '',
            'remark'    =>  '',
            'executed_count'    =>  $result['data']['executed_count']
        ];

        //处理数据
        if (!empty($result['data']['item'])) {
            $data = $result['data'];
            foreach ($data['item'] as $key => $back_data) {
                $order_info[$back_data['delivery_no']] = [
                     'waybill_no'       =>      $data['waybill_no'],
                     'delivery_no'      =>      $back_data['delivery_no'],
                     'type'             =>      $back_data['type'],
                     'is_again_time'    =>      $back_data['is_again_time']
                ];
                if ($back_data['type'] == 'AGAIN' || $back_data['type'] == 'ALL_REJECT') {
                    $order_arr[] = $back_data['delivery_no'];
                } else if($back_data['type'] == 'PART_REJECT'){ //部分拒收
                    $part_reject_arr[] = $back_data['split_sku'];
                }
            }
            $box_info = [];
            // 根据订单号获取所有箱号
            if(!empty($order_arr)){
                $box_info = OrderBox::getBoxQuantityByDelivery($order_arr);
            }
            if(!empty($part_reject_arr)){
                $product_info = OrderBox::getBoxByProduct($part_reject_arr);
            }

            if (!empty($box_info)) {
                foreach ($box_info as $key => $box_val) {
                    $back_consign[] = [
                        'waybill_no'        =>      $order_info[$box_val->delivery_no]['waybill_no'],
                        'delivery_no'       =>      $order_info[$box_val->delivery_no]['delivery_no'],
                        'related_no'        =>      $box_val->delivery_no,
                        'check_type'        =>      $order_info[$box_val->delivery_no]['type'],
                        'plan_num'          =>      $box_val->quantity,
                        'plan_num_min'      =>      $box_val->quantity,
                        'is_again_time'     =>      $order_info[$box_val->delivery_no]['is_again_time'],
                        'box_number'        =>      $box_val->box_number,
                        'split_sku'         =>      '',
                        'unit_price'        =>      '--'
                    ];
                    if ($back_data['type'] == 'AGAIN') {
                        $is_again_list[] = [
                            'order_number'    =>  $box_val->delivery_no,
                            'box_number'    =>  $box_val->box_number,
                            'delivery_no'    =>  $order_info[$box_val->delivery_no]['delivery_no'],
                            'plan_num'    =>  $box_val->quantity,
                            'is_again_time'    =>  $order_info[$box_val->delivery_no]['is_again_time'],
                            'split_sku'         =>      '',
                        ];
                    } else {
                         $is_all_reject_list[] = [
                            'order_number'    =>  $box_val->delivery_no,
                            'box_number'    =>  $box_val->box_number,
                            'plan_num'    =>  $box_val->quantity
                        ];
                    }
                }
            }
            foreach ($data['item'] as $key => $back_data) {
                if ($back_data['type'] == 'AGAIN' || $back_data['type'] == 'ALL_REJECT') {
                } else {
                    if ($back_data['type'] == 'PART_REJECT') {
                        if (!isset($product_info[$back_data['split_sku']])) {
                            return hApiError('找不到sku为'.$back_data['split_sku'].'对应的箱号!');
                        }
                        $back_consign[] = [
                            'waybill_no'        =>      $data['waybill_no'],
                            'delivery_no'       =>      $back_data['delivery_no'],
                            'related_no'        =>      $back_data['related_no'],
                            'check_type'        =>      $back_data['type'],
                            'plan_num'          =>      $back_data['plan_num'],
                            'plan_num_min'      =>      $back_data['plan_num_min'],
                            'is_again_time'     =>      $back_data['is_again_time'],
                            'box_number'        =>      $product_info[$back_data['split_sku']],
                            'split_sku'         =>      $back_data['split_sku'],
                            'unit_price'        =>      $back_data['unit_price'],
                        ];
                        $is_part_reject_order = [$back_data['related_no']];
                        $waybill_info['partRejectNum']++;
                    } else {
                        $back_consign[] = [
                            'waybill_no'        =>      $data['waybill_no'],
                            'delivery_no'       =>      $back_data['delivery_no'],
                            'related_no'        =>      $back_data['related_no'],
                            'check_type'        =>      $back_data['type'],
                            'plan_num'          =>      $back_data['plan_num'],
                            'plan_num_min'      =>      $back_data['plan_num_min'],
                            'is_again_time'     =>      $back_data['is_again_time'],
                            'split_sku'         =>      $back_data['split_sku'],
                            'unit_price'        =>      $back_data['unit_price'],
                            'box_number'        =>      ''
                        ];
                        $is_after_order = [$back_data['related_no']];
                        $waybill_info['afterNum']++;
                    }
                }
            }
        }

        //同步数据
        $waybill_tms = [
            'waybill_no'      =>  $waybill_info['waybill_no'],
            'real_money'      =>  $waybill_info['real_money'],
            'cash_money'      =>  $waybill_info['cash_money'],
            'executed_count'  =>  $waybill_info['executed_count'],
            'driver_name'     =>  '',
            'plate_number'    =>  '',
            'back_consign'    =>  $back_consign
        ];
        
        $waybill_car = $consignOrder->getCarInfoByWaybill($waybill_tms['waybill_no']);
        if (!is_null($waybill_car)) {
            $waybill_tms['plate_number'] = $waybill_car->plate_number;
            $waybill_tms['driver_name'] = $waybill_car->car_name;
        }
    
        return hApiSucceed('success', $waybill_tms);
    }

    /**
     * 获取装车单验车明细
     * @DateTime  2018-10-15
     * @param $waybill_no  装车单号
     * @return array
     */
    public function getCheckConsignWayillInfo($waybill_no)
    {
        // 请求ERP接口 获取验货信息
        $result = curl_post_erpapi('consign_order/to_tms_check_waybill_info', ['waybill_no' => $waybill_no]);
        if ($result['code'] != 200) {
            return hApiError($result['msg']);
        }
        if (is_string($result['data'])) {
             return hApiError($result['data']);
        }

        $consignOrder = new ConsignOrder();
        $order_arr = [];        //需要获取箱号的订单号（改日送,整单拒收）
        $part_reject_arr = [];  //需要获取箱号的订单号（部分拒收）
        $order_info = [];
        $is_again_list = [];
        $is_all_reject_list = [];
        $is_part_reject_order = [];
        $is_after_order = [];

        $is_again = [
            'order_count'   =>  0,
            'box_count'   =>  0,
            'quantity'   =>  0
        ];
        $is_all_reject = [
            'order_count'   =>  0,
            'box_count'   =>  0,
            'quantity'   =>  0
        ];
        $is_part_reject = [
            'order_count'   =>  0,
            'quantity'   =>  0
        ];
        $is_after = [
            'order_count'   =>  0,
            'quantity'   =>  0
        ];
        $back_consign = [];

        //装车单验货详情
        $waybill_info = [
            'waybill_no'    =>  $result['data']['waybill_no'],
            'car_name'    =>  '',
            'driver_name'    =>  '',
            'real_money'    =>  $result['data']['real_money'],
            'cash_money'    =>  $result['data']['cash_money'],
            'againCount'    =>  '',
            'againCheckCount'    =>  '',
            'againNum'    =>  '',
            'againCheckNum'    =>  '',
            'allRejectCount'    =>  '',
            'allRejectCheckCount'    =>  '',
            'allRejectNum'    =>  '',
            'allRejectCheckNum'    =>  '',
            'partRejectCount'    =>  '',
            'partRejectCheckCount'    =>  '',
            'partRejectNum'    =>  '',
            'partRejectCheckNum'    =>  '',
            'afterCount'    =>  '',
            'afterCheckCount'    =>  '',
            'afterNum'    =>  '',
            'afterCheckNum'    =>  '',
            'remark'    =>  '',
            'executed_count'    =>  $result['data']['executed_count']
        ];

        //处理数据
        if (!empty($result['data']['item'])) {
            $data = $result['data'];
            foreach ($data['item'] as $key => $back_data) {
                $order_info[$back_data['delivery_no']] = [
                     'waybill_no'       =>      $data['waybill_no'],
                     'related_no'       =>      $back_data['related_no'],
                     'delivery_no'       =>      $back_data['delivery_no'],
                     'type'             =>      $back_data['type'],
                     'is_again_time'    =>      $back_data['is_again_time']
                ];
                if ($back_data['type'] == 'AGAIN' || $back_data['type'] == 'ALL_REJECT') {
                    $order_arr[] = $back_data['delivery_no'];
                } else if($back_data['type'] == 'PART_REJECT'){ //部分拒收
                    $part_reject_arr[] = $back_data['split_sku'];
                }
            }
            $box_info = [];
            // 根据订单号获取所有箱号
            if(!empty($order_arr)){
                $box_info = OrderBox::getBoxQuantityByDelivery($order_arr);
            }
            if(!empty($part_reject_arr)){
                $product_info = OrderBox::getBoxByProduct($part_reject_arr);
            }

            if (!empty($box_info)) {
                foreach ($box_info as $key => $box_val) {
                    $back_consign[] = [
                        'waybill_no'        =>      $order_info[$box_val->delivery_no]['waybill_no'],
                        'delivery_no'       =>      $order_info[$box_val->delivery_no]['delivery_no'],
                        'related_no'        =>      $order_info[$box_val->delivery_no]['related_no'],
                        'check_type'        =>      $order_info[$box_val->delivery_no]['type'],
                        'plan_num'          =>      $box_val->quantity,
                        'plan_num_min'      =>      $box_val->quantity,
                        'is_again_time'     =>      $order_info[$box_val->delivery_no]['is_again_time'],
                        'box_number'        =>      $box_val->box_number,
                        'split_sku'         =>      '',
                        'unit_price'        =>      '--'
                    ];
                    if ($back_data['type'] == 'AGAIN') {
                        $is_again_list[] = [
                            'order_number'    =>  $order_info[$box_val->delivery_no]['related_no'],
                            'box_number'    =>  $box_val->box_number,
                            'delivery_no'    =>  $order_info[$box_val->delivery_no]['delivery_no'],
                            'plan_num'    =>  $box_val->quantity,
                            'is_again_time'    =>  $order_info[$box_val->delivery_no]['is_again_time'],
                            'split_sku'         =>      '',
                        ];
                    } else {
                         $is_all_reject_list[] = [
                            'order_number'    =>  $order_info[$box_val->delivery_no]['related_no'],
                            'box_number'    =>  $box_val->box_number,
                            'plan_num'    =>  $box_val->quantity
                        ];
                    }
                }
            }
            foreach ($data['item'] as $key => $back_data) {
                if ($back_data['type'] == 'AGAIN' || $back_data['type'] == 'ALL_REJECT') {
                } else {
                    if ($back_data['type'] == 'PART_REJECT') {
                        if (!isset($product_info[$back_data['split_sku']])) {
                            return hApiError('找不到sku为'.$back_data['split_sku'].'对应的箱号!');
                        }
                        $back_consign[] = [
                            'waybill_no'        =>      $data['waybill_no'],
                            'delivery_no'       =>      $back_data['delivery_no'],
                            'related_no'        =>      $back_data['related_no'],
                            'check_type'        =>      $back_data['type'],
                            'plan_num'          =>      $back_data['plan_num'],
                            'plan_num_min'      =>      $back_data['plan_num_min'],
                            'is_again_time'     =>      $back_data['is_again_time'],
                            'box_number'        =>      $product_info[$back_data['split_sku']],
                            'split_sku'         =>      $back_data['split_sku'],
                            'unit_price'        =>      $back_data['unit_price'],
                        ];
                        $is_part_reject_order = [$back_data['related_no']];
                        $waybill_info['partRejectNum']++;
                    } else {
                        $back_consign[] = [
                            'waybill_no'        =>      $data['waybill_no'],
                            'delivery_no'       =>      $back_data['delivery_no'],
                            'related_no'        =>      $back_data['related_no'],
                            'check_type'        =>      $back_data['type'],
                            'plan_num'          =>      $back_data['plan_num'],
                            'plan_num_min'      =>      $back_data['plan_num_min'],
                            'is_again_time'     =>      $back_data['is_again_time'],
                            'split_sku'         =>      $back_data['split_sku'],
                            'unit_price'        =>      $back_data['unit_price'],
                            'box_number'        =>      ''
                        ];
                        $is_after_order = [$back_data['related_no']];
                        $waybill_info['afterNum']++;
                    }
                }
            }
        }

        //同步数据
        $waybill_tms = [
            'waybill_no'      =>  $waybill_info['waybill_no'],
            'real_money'      =>  $waybill_info['real_money'],
            'cash_money'      =>  $waybill_info['cash_money'],
            // 'executed_count'  =>  $waybill_info['executed_count'],
            'back_consign'    =>  $back_consign
        ];

        //同步TMS
        $res = $consignOrder->saveConsignCheck($waybill_tms);
        if ($res !== true) {
            return hApiError($res);
        }

        $waybill_car = $consignOrder->getCarInfoByWaybill($waybill_tms['waybill_no']);
        $waybill_tms['plate_number'] = '';
        $waybill_tms['driver_name'] = '';
        if (!is_null($waybill_car)) {
            $waybill_tms['plate_number'] = $waybill_car->plate_number;
            $waybill_tms['driver_name'] = $waybill_car->car_name;
        }
        

        // if (!empty($is_again_list)) {
        //     $waybill_info['againCount'] = count(array_unique(array_column($is_again_list, 'order_number')));
        //     $waybill_info['againNum'] = count(array_unique(array_column($is_again_list, 'box_number')));
        // }

        // if (!empty($is_all_reject_list)) {
        //     $waybill_info['allRejectCount'] = count(array_unique(array_column($is_all_reject_list, 'order_number')));
        //     $waybill_info['allRejectNum'] = count(array_unique(array_column($is_all_reject_list, 'box_number')));
        // }

        // // 获取司机车牌号
        // $waybill_info['partRejectCount']   =  count($is_part_reject_order);
        // $waybill_info['afterCount']   =  count($is_after_order);

        // $carInfo = $consignOrder->getCarInfoByWaybill($waybill_info['waybill_no']);
        // if (!empty($carInfo)) {
        //     $waybill_info['car_name']   =  $carInfo->car_name;
        //     $waybill_info['driver_name']   =  $carInfo->plate_number;
        //     $waybill_info['remark']   =  $carInfo->remark;
        // }

        // $waybill_info['is_again_list'] = $is_again_list;
        return hApiSucceed('success', $waybill_tms);
    }


    /**
     * @查看商品详情
     * @DateTime  2018-11-22
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function getProductInfoDetail($check_type, $delivery_no, $search_code){
        $consignOrder = new ConsignOrder();
        $product_info = $consignOrder->getProductInfoDetail($check_type, $delivery_no, $search_code);
        
        if ($product_info === false) {
            return hApiError('无数据!');
        }

        return hApiSucceed('success', $product_info);
    }
}
?>