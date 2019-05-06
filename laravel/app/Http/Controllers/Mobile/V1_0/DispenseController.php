<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/6
 * Time: 15:04
 */

namespace App\Http\Controllers\Mobile\V1_0;

use App\Common\OrderNumber;
use App\Http\Controllers\MobileApiController;
use App\Models\BasicInfo\Warehouse;
use App\Models\Goods\GoodsCollection;
use App\Models\Logs\AbNormalLog;
use App\Models\Logs\OperationLog;
use App\Models\OrderInfo\DispenseOrder;
use App\Models\OrderInfo\OrderBox;
use App\Models\Permission\Role;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Models\AgencyOrder\AgencyOrder;

class DispenseController extends MobileApiController {
	//类型值
	const SCAN_VALUE = [
		'COLLECT_LEAKAGE' => '总仓漏扫',
		'TRANSREGIONAL' => '串货',
	];

	public function getDispenseOrderList(DispenseOrder $model) {
		$this->getInput('type')->isNumeric()->inArray([0, 1])->check();
		$data = $model->getDispenseList($this->input);
		checkLogic(!empty($data), '没有更多数据');
		return hSucceed('', $data);
	}

	public function saveDispenseOrder(DispenseOrder $model) {
		$params = $this->input;
		checkLogic(Role::checkUserRole($params['user_id'], 'WAREHOUSE_MANAGER'), '当前用户不是仓管用户');
		$userInfo = User::getUserDetail($params['user_id']);
		$orderId = isset($params['id']) ? $params['id'] : 0;
		$fromWare = $userInfo->warehouse_code;
		$toWare = $this->getInput('to_ware', '请输入到达仓库')->isString()->value();
		$plateNumber = $this->getInput('plate_number', '请输入车牌')->isString()->value();
		$driverName = $this->getInput('driver_name', '请输入司机名称')->isString()->value();
		$contactTel = $this->getInput('contact_tel', '请输入联系方式')->isString()->value();
		$remark = isset($params['remark']) ? $params['remark'] : 0;
		$transportCrop = $this->getInput('transport_crop')->isNumeric()->value(0);
		$detail = $this->getInput('detail')->isString()->value();

		$detail = json_decode($detail, 1);

		$departDate = $arriveDate = date('Y-m-d');

		$hour = date('H');
		if ($hour >= 18 || $hour == 00) {
			$arriveDate = date('Y-m-d', strtotime('+1 day'));
		}

		if ($transportCrop == 0) {
			$transportCropName = '其他';
		} else {
			$transportCropName = DB::table('t_transport_provider')
				->where('id', $transportCrop)
				->value('provider_name');
			checkLogic($transportCropName != null, 'ID为' . $transportCrop . '的物流未找到');
		}

		//判断仓库是否存在
		$checkWare = DB::table('t_warehouse_info')
			->whereIn('warehouse_code', [$fromWare, $toWare])
			->pluck('warehouse_code')
			->toArray();
		checkLogic(in_array($fromWare, $checkWare), '发出仓库未找到');
		checkLogic(in_array($toWare, $checkWare), '到达仓库未找到');

		$collectionNos = hCollapseArray($detail, 'collection_code');

		$collectionOrders = DB::table('t_goods_collection')
			->whereIn('collection_code', $collectionNos)
			->where('collect_status', 3)
			->get()->toArray();

		checkLogic(!empty($collectionOrders), '没有可出库的集货单！');

		$toDealer = Warehouse::getDealerId($toWare);
		$checkDealer = [];
		foreach ($collectionOrders as $ck) {
			$currentDealer = Warehouse::parseCollectionCode($ck->collection_code)['dealer_id'];
			if ($currentDealer != $toDealer) {
				$checkDealer[] = $ck->order_no;
			}
		}

		checkLogic(empty($checkDealer), '以下集货单目的地仓库与当前出库单目标仓库不一致' . implode($checkDealer));

		if ($orderId == 0) {
			$orderNumber = OrderNumber::getNextNumber('T');
		} else {
			$orderNumber = $this->getInput('order_number')->isString()->value();
		}
		DB::beginTransaction();
		try {
			if ($orderId == 0) {
				$insertingData = [
					'order_number' => $orderNumber,
					'org_id' => $userInfo->org_id,
					'warehouse_code' => $fromWare,
					'destination_code' => $toWare,
					'create_time' => hdate(),
					'depart_time' => hdate(),
					'car_id' => 0,
					'dirver_id' => 0,
					'contact_name' => $driverName,
					'contact_tel' => $contactTel,
					'order_status' => 1,
					'plate_number' => $plateNumber,
					'receiver_contact' => '',
					'receiver_tel' => '',
					'update_time' => hdate(),
					'operator' => $userInfo->login_name,
					'depart_date' => $departDate,
					'arrive_date' => $arriveDate,
					'confirm_type' => 0,
					'seal_number' => '',
					'remark' => $remark,
					'transport_cropname' => $transportCropName,
					'transport_crop' => $transportCrop,
				];
				$orderId = DB::table('t_dispense_order')->insertGetId($insertingData);
				//添加日志
				OperationLog::saveLog($userInfo->org_id, $userInfo->login_name, 'insert', '新建出库单' . $orderNumber, 2);
			} else {
				$dispenseOrderInfo = DispenseOrder::find($orderId);
				checkLogic($dispenseOrderInfo != null, '未找到发车单');
				checkLogic($dispenseOrderInfo->order_number == $orderNumber, '发车单号不一致');
				$updateData = [
					'order_number' => $orderNumber,
					'org_id' => $userInfo->org_id,
					'warehouse_code' => $fromWare,
					'destination_code' => $toWare,
					'create_time' => hdate(),
					'depart_time' => hdate(),
					'car_id' => 0,
					'dirver_id' => 0,
					'contact_name' => $driverName,
					'contact_tel' => $contactTel,
					'order_status' => 1,
					'plate_number' => $plateNumber,
					'receiver_contact' => '',
					'receiver_tel' => '',
					'update_time' => hdate(),
					'operator' => '',
					'depart_date' => $departDate,
					'arrive_date' => $arriveDate,
					'confirm_type' => 0,
					'seal_number' => '',
					'remark' => $remark,
					'transport_cropname' => $transportCropName,
					'transport_crop' => $transportCrop,
				];
				DB::table('t_dispense_order')->where('id', $orderId)->update($updateData);
				//添加日志
				OperationLog::saveLog($userInfo->org_id, $userInfo->login_name, 'update', '修改出库单' . $orderNumber, 2);
			}
			$itemInfo = DB::table('t_goods_collection as order')
				->join('t_goods_collection_detail as det', 'det.order_no', '=', 'order.order_no')
				->whereIn('order.collection_code', $collectionNos)
				->where('collect_status', 3)
				->select(
					'order.line_code',
					'det.goods_code',
					'det.goods_type',
					'det.goods_typename',
					'order.collection_code',
					'order.order_no'
				)
				->get()
				->toArray();

			checkLogic(!empty($itemInfo), '该集货单下的,箱号为空,请认真核实!');

			$dispenseDetail = [];
			$collection_no = $model->getCollectionNoByOrderNumber($orderNumber);

			foreach ($itemInfo as $item) {
				if (!in_array($item->collection_code, $collection_no)) {
					$dispenseDetail[] = [
						'order_number' => $orderNumber,
						'create_time' => hdate(),
						'goods_no' => $item->goods_code,
						'collection_no' => $item->order_no,
						'goods_type' => $item->goods_type,
						'goods_typename' => $item->goods_typename,
						'line_code' => $item->line_code,
					];
				}

				//修改集货状态
				GoodsCollection::saveCollectionStatus($item->order_no, 5);
			}

			DB::table('t_dispense_order_detail')->insert($dispenseDetail);

			DB::commit();
			return hSucceed('保存成功', ['id' => $orderId, 'order_number' => $orderNumber]);
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}
	}

	// 获取出库单详情
	public function getDispenseDetail(DispenseOrder $model) {
		$order_number = $this->getInput('order_number')->isString()->value();
		$result = $model->getDispenseDetail($order_number);
		checkLogic(!empty($result), '集货单未找到,请核实集货单');
		return hSucceed('', $result);
	}

	//完成出库
	public function dispenseFinish(DispenseOrder $model) {
		$this->getInput('order_number')->isString()->check();
		$this->getInput('type')->isNumeric()->inArray([1, 2])->check();
		if ($this->input['type'] == 2) {
			$this->getInput('seal_number')->isString()->check();
		}
		$model->dispenseFinish($this->input);
		return hSucceed('出库完成');
	}

	// 扫码收货
	public function verifyDispenseOrder(DispenseOrder $model) {
		$params = $this->input;
		$this->getInput('order_number')->isString()->check();
		$this->getInput('box_number')->isString()->check();
		$box_number = $params['box_number'];
		checkLogic(substr($box_number, 0, 4) != 'RBAP', '该箱号属于第三方发货，请移步到(三方)收货重新进行扫描!');
		return $model->verifyDispenseOrder($params);
	}

	// 扫码收货（代理商）
	public function verifyAgentPlatformOrder(DispenseOrder $model, AgencyOrder $agency){
		$params = $this->input;
		$box_number = $this->getInput('box_number')->isString()->value();

		//校验是否是售后类型的省代单
		$agencyInfo = $agency->getAgencyDetailByBoxNumber($box_number);
		if (!empty($agencyInfo) && substr($agencyInfo->order_number, 0, 4) == 'ASAP') {

			$result = $model->getAgentAfterLine($box_number);

			return hSucceed('省代售后', $result);
		}

		$result = $model->verifyAgentPlatformOrder($params);
		return hSucceed('', $result);
	}

	//核实出库箱号
	public function inCheckBox(DispenseOrder $model) {
		$order_number = $this->getInput('order_number')->isString()->value();
		$goods_no = $this->getInput('goods_no')->isString()->value();
		$info = $model->getDetailByOrderGoodsNo($order_number, $goods_no);

		if (empty($info)) {
			return hError($goods_no);
		}
		return hSucceed('');
	}

	//根据装车单集货号获取箱子明细
	public function getBoxDetail(DispenseOrder $model) {
		$order_number = $this->getInput('order_number')->isString()->value();
		$collection_no = $this->getInput('collection_no')->isString()->value();
		$type = $this->getInput('type')->isNumeric()->value(1);
		if ($type == 0) {
			//获取异常单
			$abNormalLog = AbNormalLog::getAbNormalLog(['related_order' => $order_number]);
			$info = [];
			if (!empty($abNormalLog)) {
				foreach ($abNormalLog as $key => $normal) {
					$info[] = [
						'goods_no' => $normal['box_number'],
						'scan_time' => $normal['scan_time'],
						'scan_status' => 0,
						'scan_type' => $normal['scan_type'],
						'scan_value' => isset(self::SCAN_VALUE[$normal['scan_type']]) ? self::SCAN_VALUE[$normal['scan_type']] : 'UNKNOW_TYPE',
					];
				}
			}
			return hSucceed('', $info);
		}
		$list = $model->getBoxDetail($order_number, $collection_no);
		checkLogic(!empty($list), '信息拉取失败，请核实单号！');
		return hSucceed('', $list);
	}

	public function carArrive(DispenseOrder $model) {
		$this->getInput('order_number')->isString()->check();
		$this->getInput('seal_number')->isString(false)->check();
		$this->getInput('type')->isNumeric()->inArray([1, 2])->check();

		$model->carArrive($this->input);
		return hSucceed('扫码成功');
	}

	//干线报表
	public function getDrylineList(DispenseOrder $model){
		$info = $model->getDrylineList($this->input);
		if (empty($info)) {
			return hError('没有更多数据');
		}
		return hSucceed('', $info);
	}

	//分仓报表
	public function getWarehouseList(DispenseOrder $model) {
		$info = $model->getWarehouseList($this->input);
		if (empty($info)) {
			return hError('没有更多数据');
		}
		return hSucceed('', $info);
	}

	/**
     * 根据订单号获取用户信息
     */
    public function getOrderAddressByOrder(DispenseOrder $model, AgencyOrder $agency){

        $box_number = $this->getInput('box_number')->isString()->value();

        checkLogic(substr($box_number, 0, 4) == 'RBAP', '箱号传递不正确,请检查是否是RBAP*******-***');
        $agencyInfo = $agency->getAgencyDetailByBoxNumber($box_number);
        checkLogic(!is_null($agencyInfo), '找不到箱号为'.$box_number.'的订单信息');

        $related_no = $agencyInfo->order_number;
        
        if (substr($related_no, 0, 2) == 'AS') {//售后类型
        	$result = curl_post_omsapi('aftersales/aftersales_info', ['aftersales_no' => $related_no]);
        	if ($result['code'] != 200) {
	            return hError('OMS返回'.$result['msg']);
	        }
	        if (is_string($result['data'])) {
	             return hError('OMS返回:'.$result['data']);
	        }
        	
        }else{
        	$result = curl_post_erpapi('consign_order/get_order_address_by_order', ['order_number' => $related_no]);
        	if ($result['code'] != 200) {
	            return hError('MP返回'.$result['msg']);
	        }
	        if (is_string($result['data'])) {
	             return hError('MP返回:'.$result['data']);
	        }
        }
        
        $result['data']['box_number'] = $box_number;
        $result['data']['sm_order'] = $agencyInfo->sm_order;
        $result['data']['related_no'] = $related_no;

        return hSucceed('', $result['data']);
    }
}