<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/6
 * Time: 15:04
 */

namespace App\Http\Controllers\Mobile\V1_0;

use App\Common\MyRedis;
use App\Common\OrderNumber;
use App\Http\Controllers\MobileApiController;
use App\Models\AgencyOrder\AgencyOrder;
use App\Models\AgencyOrder\ConfirmGoodsOrder;
use App\Models\Goods\GoodsCollection;
use App\Models\Logs\AbNormalLog;
use App\Models\Logs\OperationLog;
use App\Models\OrderInfo\DispenseOrder;
use App\Models\OrderInfo\OrderBox;
use App\Models\Permission\Role;
use App\User;
use Illuminate\Filesystem\Cache;
use Illuminate\Support\Facades\DB;

class AgencyOrderController extends MobileApiController
{
    //验货信息redis的时效时间
    const INSPECTION_INFO_REDIS_TIME_OUT =20 * 60 ;
    //单条数据的redis时效时间
    const SINGLE_REDIS_TIME_OUT = 30 * 24 * 60 * 60 ;
    /**
     * 获取代理商出库数据
     */
    public function getAgencyInfo(AgencyOrder $agency)
    {
        $list = $agency->getAgencyInfo($this->input);
        $list = object_to_array($list);

        $flag_status = [];
        foreach ($list as $k => $item) {
            if ($item['scan_status'] == 0) {
                $flag_status[$item['sm_order']] = 1;
            }
        }

        $agency_info = [];
        $box_exist = [];
        // 处理数据
        foreach ($list as $k => $item) {
            $scan_time = $item['scan_time'];
            if (empty($item['scan_time']) || $item['scan_time'] == '0000-00-00 00:00:00' || $item['scan_time'] == '0000-01-01 00:00:00') {
                $scan_time = '';
            }
            if (in_array($item['box_number'], $box_exist)) {
                continue;
            }
            $agency_info[$item['agency_main_id']][] = [
                'sm_order' => $item['sm_order'],
                'order_number' => $item['order_number'],
                'box_number' => $item['box_number'],
                'collection_code' => $item['collection_code'],
                'scan_status' => $item['scan_status'],
                'scan_time' => $scan_time,
                'scan_type' => isset($flag_status[$item['sm_order']]) ? $flag_status[$item['sm_order']] : 0
            ];
            $box_exist[] = $item['box_number'];
        }

        $agency_list = [];
        foreach ($agency_info as $key => $order_info) {
            $agency_list[] = [
                'agency_main_id' => $key,
                'order_number' => $order_info[0]['order_number'],
                'collection_code' => $order_info[0]['collection_code'],
                'order_list' => $order_info
            ];
        }

        return hSucceed('', $agency_list);
    }

    // 获取所有供应商
    public function getSupplierList(AgencyOrder $agency)
    {
        $supplierList = $agency->getSupplierList();
        return hSucceed('', $supplierList);
    }

    // 扫码入库（代理商出库）
    public function verifyAgentOrder(AgencyOrder $model)
    {
        $params = $this->input;
        $box_number = $this->getInput('box_number')->isString()->value();
        $supplier = $this->getInput('supplier')->isString()->value();
        checkLogic(substr($box_number, 0, 4) == 'RBAP', '该箱号属于锐锢发货，请移步到分仓收货重新进行扫描!');
        $result = $model->verifyAgentOrder($params);
        if (is_array($result)) {
            return hSucceed('success', $result);
        }
        return hError($result);
    }

    // 省代退回
    public function rollbackAgencyOrder(AgencyOrder $agency)
    {
        $agency_main_id = $this->getInput('agency_main_id')->isString()->value();
        // $result = $agency->markOrderReject($agency_main_id, $this->user_id);
        $result = $agency->rollbackAgencyOrder($agency_main_id, $this->user_id);
        if (is_numeric($result)) {
            return hSucceed('已退回'.$result);
        }
        return hError($result);
    }

    //省代集货号更改
    public function saveBindNumber(AgencyOrder $agency)
    {
        $bind_number = $this->getInput('bind_number')->isString()->value();
        $order_number = $this->getInput('order_number')->isString()->value();

        $bind = explode('-', $bind_number);
        if (count($bind) != 2) {
            return hError('分仓集货号的格式必须是*******-*');
        }

        if (!is_numeric($bind[1])) {
            return hError('分仓集货号的格式尾数必须是数字');
        }

        $res = $agency->saveBindNumber($order_number, $bind[0], $bind[1]);
        if ($res) {
            //返回集货号
            return hSucceed('success', ['box_number' => $bind_number]);
        }

        return hSucceed('error');
    }

    // 省代收货结束
    public function saveAgencyOver(AgencyOrder $agency)
    {
        $order_stying = $this->getInput('agency_main_id')->isString()->value();
        $agency_main_id = explode(',', $order_stying);
        $agency_main_id = array_fu($agency_main_id);
        $result = $agency->saveAgencyOver($agency_main_id, $this->user_id);
        return hSucceed($result);
    }

    //分仓快递箱号扫描
    public function sacnExpressBox(AgencyOrder $agency)
    {
        $box_number = $this->getInput('box_number')->isString()->value();
        $res = $agency->sacnExpressBox($box_number);

        $res['collection_code'] = '物流区';
        return hSucceed('', $res);
    }


    /**
     * 获取要发快递的订单
     */
    public function getWantSendExpress(AgencyOrder $agency)
    {
        $list = $agency->getWantSendExpress($this->user_id);
        $list = object_to_array($list);

        $agency_info = [];
        $box_exist = [];
        // 处理数据
        foreach ($list as $k => $item) {
            $print_time = $item['print_time'];
            if (empty($item['print_time']) || $item['print_time'] == '0000-00-00 00:00:00' || $item['print_time'] == '0000-01-01 00:00:00') {
                $print_time = '';
            }

            $agency_info[$item['agency_main_id']][] = [
                'sm_order' => $item['sm_order'],
                'order_number' => $item['order_number'],
                'box_number' => $item['box_number'],
                'status' => empty($item['status']) ? 0 : $item['status'],
                'print_time' => $print_time,
                'child_no' => empty($item['child_no']) ? '' : $item['child_no']
            ];
        }

        $agency_list = [];
        foreach ($agency_info as $key => $order_info) {
            $agency_list[] = [
                'agency_main_id' => $key,
                'related_no' => $order_info[0]['order_number'],
                'order_list' => $order_info
            ];
        }
        return hSucceed('', $agency_list);
    }


    //扫码打印快递面单
    public function printExpressDocuments(AgencyOrder $agency)
    {

        $box_number = $this->getInput('box_number')->isString()->value();
        $result = $agency->printExpressDocuments($box_number, $this->user_id);

        if (is_string($result)) {
            return hError($result);
        }
        return hSucceed('', $result);
    }

    //打印结束
    public function overExpressPrint(AgencyOrder $agency)
    {
        $res = $agency->overExpressPrint($this->user_id);
        return hSucceed($res);
    }

    //获取出库交接的的快递单
    public function getExpressHandOut(AgencyOrder $agency)
    {
        $list = $agency->getExpressHandOut($this->user_id);

        $info = [];
        foreach ($list as $key => $item) {
            $info[$item['express_no']]['express_no'] = $item['express_no'];
            $info[$item['express_no']]['express_info'][$item['agency_main_id']]['order_list'][] = $item;
            $info[$item['express_no']]['express_info'][$item['agency_main_id']]['related_no'] = $item['related_no'];
        }

        $express_info = [];
        foreach ($info as $key => $express) {
            $flag_status = 1;
            $agency_list = [];
            foreach ($express['express_info'] as $k => $item) {
                foreach ($item['order_list'] as $ke => $order) {
                    if ($order['is_send_off'] != 1) {
                        $flag_status = 0;
                    }
                }
                $agency_list[] = $item;
            }
            $express_info[] = [
                'express_no' => $key,
                'flag_status' => $flag_status,
                'express_info' => $agency_list
            ];
        }

        return hSucceed('', $express_info);
    }


    //快递出库扫描
    public function verifyExpressOut(AgencyOrder $agency)
    {
        $box_number = $this->getInput('box_number')->isString()->value();
        $res = $agency->verifyExpressOut($box_number, $this->user_id);
        return hSucceed('', $res);
    }

    public function finishExpressOut(AgencyOrder $agency)
    {
        $res = $agency->finishExpressOut($this->user_id);
        return hSucceed($res);
    }


    //345
    public function handPullSmOrder(AgencyOrder $agency)
    {

        $sm_order = $this->getInput('sm_order')->isString()->value();

        $sm_order_arr = explode(',', $sm_order);

        foreach ($sm_order_arr as $sm_order) {
            $res = $agency->handPullSmOrder($sm_order);

            echo $res . PHP_EOL;
        }

    }


    //临时方案请求TS 立即通知三方
    public function distributeNow()
    {
        $transport_no = $this->getInput('transport_no')->isString()->value();
        $res = curl_post_tsapi('order/transport/distributeNow', ['transport_no' => $transport_no]);

        if ($res['code'] != 200) {
            echo 'TS获立即推送三方任务失败，返回' . $res['msg'] . $transport_no;
        } else {
            echo 'Success';
        }
    }

    //根据快递单号。客户手机号 服务单号 获取验货信息
    public function getAftersInfoCheckByNumber()
    {

        $params = $this->input;
        $transport_no = isset($params['transport_no']) ? $params['transport_no'] : '';
        $aftersales_no = isset($params['aftersales_no']) ? $params['aftersales_no'] : '';
        $contact_mobile = isset($params['contact_mobile']) ? $params['contact_mobile'] : '';

        //参数验证
        if (empty($transport_no) && empty($aftersales_no) && empty($contact_mobile)) {
            return hError('快递单号，手机号，服务单号，至少传一个！');
        }
        $redis = new MyRedis();
        //确定KEY.
        $stagKey = '';
        if (!empty($transport_no)) {
            $stagKey = $transport_no;
        }
        if (!empty($aftersales_no)) {
            //避免与下方键冲突
            $stagKey = 'sale' . $aftersales_no;
        }
        if (!empty($contact_mobile)) {
            $stagKey = $contact_mobile;
        }
        $initKey = $redis->createKey($stagKey);
        $data = [
            'transport_no' => $transport_no,
            'aftersales_no' => $aftersales_no,
            'contact_mobile' => $contact_mobile
        ];
        if($redis->exists($initKey)){
            $res = $redis->get($initKey);
            $data = json_decode($res,1);
        }else{
            $res = curl_post_omsapi('aftersales/get_aftersales_by_transport_no', $data);
            checkLogic($res['code'] === 200, 'OMS：' . $res['msg']);
            $data = $res['data'];
        }
        $tempArray = [];
        $tempArr = [];
        $tempCurrent = [];
        //服务单数
        $serviceNumber = 0;
        //快递单数
        $expressNumber = 0;
        foreach ($data as $item => $value) {
            if ($item == 'current' && !empty($value)) {
                $value[0]['real_number'] = '0';
                $value[0]['status'] = '新建';
                $value[0]['warehouse_name'] = '上海地推仓';
                $value[0]['RGSF'] = 'RGSF';
                $value[0]['is_normal'] = 0;
                $value[0]['is_database'] = 0;
                //类型转换
                if (isset($value[0]['shop']) && !empty($value[0]['shop'])) {
                    foreach ($value[0]['shop'] as $shop_key => $shop) {
                        $value[0]['shop'][$shop_key]['count'] = (string)(int)$value[0]['shop'][$shop_key]['count'];
                    }
                }
                $key = $redis->createKey($value[0]['aftersales_no']);
                if($redis->exists($key)){
                    $tempStr = $redis->get($key);
                    $strCurrent[] = json_decode($tempStr,1);
                }else{
                    $strCurrent = $value;
                }
                $tempCurrent=$strCurrent;
                if ($value[0]['transport_no'] != '' && isset($value[0]['transport_no']) && !empty($value[0]['transport_no'])) {
                    $expressNumber++;
                }
                if ($value[0]['aftersales_no'] != '' && isset($value[0]['aftersales_no']) && !empty($value[0]['aftersales_no'])) {
                    $serviceNumber++;
                }

            } elseif($item == 'orther' && !empty($value)){
                foreach ($value as $i => $k) {     
                    $key = $redis->createKey($k['aftersales_no']);
                    if($redis->exists($key)){
                        $tempOther = $redis->get($key);
                        $other = json_decode($tempOther,1);
                    }else{
                        $k['real_number'] = '0';
                        $k['status'] = '新建';
                        $k['warehouse_name'] = '上海地推仓';
                        $k['RGSF'] = 'RGSF';
                        $k['is_normal'] = 0;
                        $k['is_database'] = 0;
                    
                        //类型转换
                        if (isset($k['shop']) && !empty($k['shop'])) {
                            foreach ($k['shop'] as $shop_key => $shop) {
                                $k['shop'][$shop_key]['count'] = (string)(int)$k['shop'][$shop_key]['count'];
                            }
                        }
                        $other = $k;
                    }
                    //检查是否入库,过滤到已经入库信息
                    if($other['is_database'] != 1){
                        array_push($tempArr, $other);
                        if ($other['transport_no'] != '' && isset($other['transport_no']) && !empty($other['transport_no'])) {
                            $expressNumber++;
                        }
                        if ($other['aftersales_no'] != '' && isset($other['aftersales_no']) && !empty($other['aftersales_no'])) {
                            $serviceNumber++;
                        }
                    }
                }
            }
        }
        $tempArray['current'] = $tempCurrent;
        $tempArray['orther'] = $tempArr;
        $tempArray['serviceNumber'] = $serviceNumber;
        $tempArray['expressNumber'] = $expressNumber;
        if ($tempArray['serviceNumber'] == 0 && $tempArray['expressNumber'] == 0) {
            return hSucceed('未查到数据');
        }
        $redis->set($initKey, json_encode($tempArray));
        $now =time();
        $redis->exprieAt($initKey,$now + self::INSPECTION_INFO_REDIS_TIME_OUT);
        //查看是否有更新数据
        return hSucceed('', $tempArray);

    }
    /**
     * 跟新redis中的数据
     * @return string
     * @throws \App\Exceptions\ApiParaException
     */
    public function updateAftersInfo()
    {
        $params = $this->input;
        //参数验证
        $this->getInput('aftersales_no')->isString()->value();
        $this->getInput('searchNumber')->isString()->value();
        $this->getInput('real_number')->isString()->value();
        $redis = new MyRedis();
        $key = $redis->createKey($params['aftersales_no']);
        if($redis->exists($key)){
            $order = $redis->get($key);
            $order = json_decode($order,1);
        }else{
            $order = ConfirmGoodsOrder::getAllOrder($params['aftersales_no'],$params['searchNumber']);
            if(!is_array($order) || empty($order)){
                return hError('未找到指定数据');
            }
        }
        $order['is_normal'] = isset($params['is_normal']) ? $params['is_normal'] : 1;
        $order['reason'] = isset($params['reason']) ? $params['reason'] :'';
        $order['real_number'] =(string)((int)$params['real_number'] + (int)$order['real_number']);
        if((int)$order['real_number'] > (int)$order['shop'][0]['count']){
            return hError('实际收货数量不能大于计划收货数量');
        }
        if((int)$order['real_number'] < 0){
            return hError('实际数量不能为负!');
        }
        $redis->set($key,json_encode($order));
        $redis->exprieAt($key,time() + self::SINGLE_REDIS_TIME_OUT);
        //存入状态集合中
        if((int)$order['real_number'] == (int)$order['shop'][0]['count']){
            //更新正常集合中的数据
            ConfirmGoodsOrder::setOrder('normal',$order);
            //同时删除异常集合中的数据
            ConfirmGoodsOrder::delSetRedisSet('abnormal',$order);
        }else{
            ConfirmGoodsOrder::setOrder('abnormal',$order);
        };
        return hSucceed('成功', $order);
    }
}