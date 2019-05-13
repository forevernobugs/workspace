<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\LoginRequireController;
use App\Models\AgencyOrder\AgencyOrder;
use App\Models\OrderInfo\ConsignOrder;
use App\User;
use App\Models\Logs\ApiRequestLog;
use Illuminate\Support\Facades\Redis;
use App\Common\MyRedis;
use App\Http\Controllers\Mobile\V1_0\AgencyOrderController;
use App\Models\AgencyOrder\ConfirmGoodsOrder;
use App\Jobs\JobHelper;
use App\Jobs\OrderJobs\SaveAgencyConsign;
use App\Libs\XLSXWriter\XLSXWriterContract;
use App\Libs\XLSXWriter\XLSXWriter;

/**
 * Created by PhpStorm.
 * User: zhangdahao
 * Date: 2019/01/22
 * Time: 03:02
 */
class AgencyController extends LoginRequireController
{
	//省代单状态
	const AGENCY_STATUS = [
		1 => '新建',
		2 => '收货中',
		3 => '已收货',
		4 => '已退回',
		5 => '已推送出库'
	];

	//分仓出库状态
	const OUT_STATUS = [
		1	=>	'新建',
		2	=>	'已出库',
		3	=>	'已到达'
	];

	/**
	 * 省代出单列表
	 *@return result
	 */
	public function getAgencyList(AgencyOrder $agency){
		$list = $agency->getAgencyList($this->input);

		foreach ($list['list'] as $key => $item) {
			$list['list'][$key]['status'] = isset(self::AGENCY_STATUS[$item['status']]) ? self::AGENCY_STATUS[$item['status']] : '';
		}
		$list['title'] = [
			'order_number' => '出库单单号',
			'agency_main_id' => '批次号',
			'status' => '状态',
			'supplier' => '代理商',
			'out_ware_day' => '要求发货日',
			'out_time' => '出库时间'
		]; 

		$list['supplier_list'] = $agency->getSupplierList();

		$list['status'] = self::AGENCY_STATUS;

		return $this->returnList('加载成功', $list, $list['title'], '代理商出库单');
	}

	/**
	 * 修改发货日
	 * @return json
	 */
	public function saveOutTimeDay(AgencyOrder $agency){
		$agency_main_id = $this->getInput('agency_main_id')->isString()->value();
		$new_out = $this->getInput('new_out')->isString()->value();
		$user_id = $this->getInput('user_id')->isString()->value();
		$result = $agency->saveOutTimeDay($agency_main_id, $new_out, $user_id);
		if ($result === true) {
			return hSucceed('修改成功');
		}
		return hError($result);
	}

	/**
	 * 省代分仓出库确认单列表
	 */
	public function getAgencyConfirmOutList(ConsignOrder $consign, AgencyOrder $agency){

		$list = $consign->getAgencyConfirmOutList($this->input);

		//setTilte
		$list['title'] = [
			'created_at'	=>	'验车时间',
			'supplier'	=>	'代理商',
			'related_no'	=>	'关联单号',
			'status'	=>	'状态',
			'sku'		=>	'商品sku',
			'plan_num'		=>	'申请数量',
			'real_num'		=>	'到货数量',
			'handover_num'		=>	'交接数量',
			'handover_time'		=>	'交接时间',
			'admin'		=>	'仓管',
			'handover_user'		=>	'提货人',
			'handover_mobile'		=>	'提货人手机'
		];

		//获取所有代理商
		$list['supplier_list'] = $agency->getSupplierList();
		$list['out_status'] = self::OUT_STATUS;

		foreach ($list['list'] as $k => $item) {
			$list['list'][$k]['status'] = isset(self::OUT_STATUS[$item['status']]) ? self::OUT_STATUS[$item['status']] : '未知';
		}
		return $this->returnList('加载成功', $list, $list['title'], '分仓出库单');

	}

	/**
	 * 修改分仓出库信息
	 */
	public function saveOutAgencyInfo(ConsignOrder $consign){

		$handover_mobile = $this->getInput('handover_mobile')->isString()->value();
		$handover_user = $this->getInput('handover_user')->isString()->value();
		$handover_num = $this->getInput('handover_num')->isString()->value();
		$related_no = $this->getInput('after_no')->isString()->value();
		$check_out = $consign->getConsignCheckOut($related_no);

		if (is_null($check_out)) {
			return hError('未找到出库信息”');
		}

		if ($check_out->status != 1) {
			return hSucceed('只允许修改“新建状态下的出库信息”');
		}
		$param = $this->input;
		$result = $consign->saveOutAgencyDataByRelatedNo($param, $related_no);

		if ($result === true) {
			return hSucceed('修改成功');
		}
		return hSucceed('修改失败'.$result);
	}

	/**
	 * 分仓确认出库
	 */
	public function confirmAgencyOut(ConsignOrder $consign){
		$related_no = $this->getInput('related_no')->isString()->value();
		$result = $consign->saveConsignCheckOut($this->user_id, $related_no);
		if ($result === true) {
			return hSucceed('');
		}

		return hSucceed('失败'.$result);
	}

	//打印确认出库单
	public function printConfirmOut(ConsignOrder $consign){
		
		$supplier = $this->getInput('supplier')->isString()->value();
		$related_nos = $this->getInput('related_nos')->isString()->value();
		$this->input['related_nos'] = explode(',', $related_nos);
		
		//请求APS获取代理商信息
    	$url = env('APS_API_URL','').'manage/shop/shop/getByName?name='.$supplier;
    	$res = file_get_contents($url);
    	$res = json_decode($res, 1);
    	//api日志
        ApiRequestLog::saveLog(
            1,
            'getSupplierName',
            env('APS_API_URL','').'manage/shop/shop/getByName',
            $supplier,
            json_encode($res)
        );
    	checkLogic($res['code'] == 200, '未找到代理商信息');
		$info = $consign->getAgencyConfirmOutList($this->input);
		$sku = array_fu(array_column($info['list'], 'sku'));

		$sku_name = [];
		if (!empty($sku)) {
	    	$result = curl_post_erpapi('consign_order/get_product_info_by_sku', ['sku' => json_encode($sku)]);

	    	if ($result['code'] != 200) {
	            return hError('MP返回'.$result['msg']);
	        }
	        if (is_string($result['data'])) {
	             return hError('MP返回:'.$result['data']);
	        }
	        if (!empty($result['data'])) {
	        	foreach ($result['data'] as $k => $product) {
	        		$sku_name[$product['bianma']] = $product['product_name'];
	        	}
	        }
		}

		foreach ($info['list'] as $key => $item) {
			$info['list'][$key]['product_name'] = isset($sku_name[$item['sku']]) ? $sku_name[$item['sku']] : '';
		}

		$info['agency_address'] = $res['data']['address'];
		$info['agency_contact'] = $res['data']['name'];
		$info['agency_tel'] = $res['data']['tel'];
		$info['print_date'] = hDate();
		$info['warehouse_name'] = User::getUserDetail($this->user_id)->warehouse_name;
		
		return hSucceed('加载成功', $info);
	}

	//分仓售后直发确认列表
	public function saveAfterExpressInfo(){
		$params = $this->input;

		$order_key = isset($params['order_key']) ? $params['order_key'] : '';
		$type = isset($params['type']) ? $params['type'] : 1;

		$list['list'] = [];
		$list['title'] = [
        	'express_platform'	=>	'承运商平台',
        	'carrier_number'	=>	'快递单号',
        	'related_no'	=>	'关联单号',
        	'sku'	=>	'商品编码',
        	'goods_name'	=>	'商品名称',
        	'goods_picture'	=>	'标准商品图片',
        	'sale_goods_picture'	=>	'售后商品图片',
        	'apply_number'	=>	'计划取件数',
        	'real_number'	=>	'实际取件数',
        	'do_goods_time'	=>	'确认收货时间',
        	'receiver_contact'	=>	'收货人',
        	'is_normal'	=>	'是否异常',
        	'provider_name'	=>	'三方店铺',
        	'different_reason'	=>	'差异原因',
            // 'status'=>	'状态',
        ];

        $after_type = 'normal';
        if ($type == 1) {
        	$after_type = 'ALL_SET';
        }elseif($type == 3){
        	$after_type = 'abnormal';
        }else{
        	$after_type = 'normal';
        }

        if ($order_key !== '') {
        	$data = ConfirmGoodsOrder::getOneOrder($after_type, $order_key);
        }else{
        	$data = ConfirmGoodsOrder::getSetOrders($after_type);
        }

        if ($data === false || !is_array($data)) {
			return $this->returnList('加载成功', $list, $list['title'], '分仓售后直发确认列表');
        }

        $redis = new MyRedis();
        $info = [];
        foreach ($data as $i=>$datum){
            if($datum['is_database'] == 1){
                continue;
            }
        	$after_info = [];
        	$aftersales_no = $redis->createKey($datum['aftersales_no']);
        	if($redis->exists($aftersales_no)){
                $after_info = json_decode($redis->get($aftersales_no), 1);
            }
            $reason = isset($after_info['reason']) ? $after_info['reason'] : '';
            $status = isset($after_info['status']) ? $after_info['status'] : '新建';
            $receiver = isset($after_info['receiver']) ? $after_info['receiver'] : '';
            $receive_time = isset($after_info['receive_time']) ? $after_info['receive_time'] : '';
            $real_number = isset($after_info['real_number']) ? $after_info['real_number'] : 0;

        	foreach ($datum['shop'] as $shop) {
        		$img = '';
        		foreach ($shop['imgs'] as $afterImg) {
        			$img .= "<img src='{$shop["i_picture"]}'></img>";
        		}
        		$info[] = [
        			'express_platform'	=>	'',
        			'carrier_number'	=>	$datum['transport_no'],
        			'provider_name'	=>	$datum['name'],
        			'related_no'	=>	$datum['aftersales_no'],
        			'sale_after_status'	=>	'',
        			'service_check_time'	=>	'',
        			'sku'	=>	$shop['bianma'],
        			'goods_name'	=>	$shop['product_name'],
        			'goods_picture'	=>  "<img src='{$shop['i_picture']}'></img>",
        			'sale_goods_picture'	=>	$img,
        			'apply_number'	=>	$shop['count'],
        			'real_number'	=>	$real_number,
        			'do_goods_time'	=>	$receive_time,
        			'receiver_contact'	=>	$receiver,
        			'is_normal'	=>	'正常',
        			'different_reason'	=>	$reason,
                    'status'	=>	$status
        		];
        	}
        }

        $list['list'] = $info;
		return $this->returnList('加载成功', $list, $list['title'], '分仓售后直发确认列表');
	}

	//修改差异原因
	public function saveDifferenceReasion(){
		$after_no = $this->getInput('after_no')->isString()->value();
		$difference_reasion = $this->getInput('difference_reasion')->isString()->value();
		$redis = new MyRedis();
		$after_no = $redis->createKey($after_no);

		checkLogic($redis->exists($after_no), 'PDA尚未验货，请先打印售后标签');

		$data = json_decode($redis->get($after_no), 1);

		$data['reason'] = $difference_reasion;
        $data['is_normal'] = 2;
		$redis->set($after_no,json_encode($data));
		//同时修改集合中数据
        ConfirmGoodsOrder::setOrder('abnormal',$data);

		return hSucceed('修改成功');
	}

	//省代分仓直发售后确认收货
	public function saveReceiveExpressAfter(ConfirmGoodsOrder $confirmGoodsOrder){
		$related_nos = $this->getInput('related_nos')->isString()->value();
		$related_no_arr = explode(',', $related_nos);

		$result = $confirmGoodsOrder->confirmGoods($related_no_arr);
		$result = object_to_array($result);
		
		$redis = new MyRedis();
		//请求ts标记入库
		if(!empty($result['success'])){
			foreach ($result['success'] as $related_no => $shop) {
				
				$related_no = $redis->createKey($related_no);
				if ($redis->exists($related_no)) {
					$data = json_decode($redis->get($related_no), 1);
					$data['receiver'] = $this->getUserName();
					$data['receive_time'] = hDate();
                    $data['is_database'] = 1;
					$redis->set($related_no,json_encode($data));
					//同时修改集合中的数据
                    if((int)$data['shop'][0]['count'] == (int)$data['real_number']){
                        ConfirmGoodsOrder::setOrder('normal',$data);
                    }else{
                        ConfirmGoodsOrder::setOrder('abnormal',$data);
                    }
				}
				$related_no  = str_replace('tms_api:', '', $related_no);
				JobHelper::dispatchJob(
					SaveAgencyConsign::class, ['agency_after_no' => $related_no]
				);
			}
		}

		return hSucceed('成功入库'.count($result['success']).',失败了'.count($result['error']), $result);
	}

	//导出失败错误单
	public function saveReceiveExpressAfterErrorExport(){
		$error_data = $this->getInput('error_data')->isString()->value();
		$error = explode('<@>', $error_data);

		$error_info = [];
		$data[] = [
			'失败单据',
			'失败原因'
		];
		foreach ($error as $error_msg) {
			$error_one = explode('->', $error_msg);
			$data[] = [
				$error_one[0],
				$error_one[1]
			];
		}

		$xLSXWriter = new XLSXWriter();

		$xLSXWriter->export($data, '分仓直发失败单据-' . date('Y-m-d H:i:s'));
	}
    //修改redis中的状态
	public function changeStatus($param)
    {
        $redis = new MyRedis();
        $key = $param;
        if(substr($param,0,4)=="ASAP"){
            $key = "sale".$param;
        }
        $redisKey = $redis->createKey($key);
        $data = $redis->get($redisKey);
        $result = json_decode($data,true);
        $result['status'] = "已确认";
        $redis->set($redisKey,json_encode($result));
        $redis->exprieAt($redisKey,time()+30*24*60*60);
        return $result;
    }
    /**
     * 确认收货列表
     * @param ConfirmGoodsOrder $confirmGoodsOrder
     * @return array|string
     */
    public function confirmGoodsList(ConfirmGoodsOrder $confirmGoodsOrder)
    {
        $list = $confirmGoodsOrder->getConfirmGoodOrders($this->input);
        $list['title'] = [
            'express_platform'	=>	'承运商平台',
            'carrier_number'	=>	'快递单号',
            'related_no'	=>	'关联单号',
            'sku'	=>	'商品编码',
            'goods_name'	=>	'商品名称',
            'goods_picture'	=>	'标准商品图片',
            'sale_goods_picture'	=>	'售后商品图片',
            'apply_number'	=>	'计划取件数',
            'real_number'	=>	'实际取件数',
            'do_goods_time'	=>	'确认收货时间',
            'receiver_contact'	=>	'收货人',
            'is_normal'	=>	'收货状态',
            'provider_name'	=>	'三方店铺',
            'different_reason'	=>	'差异原因',
            'service_check_time'	=>	'服务审核时间',
        ];
        return $this->returnList('加载成功', $list, $list['title']);
    }
}