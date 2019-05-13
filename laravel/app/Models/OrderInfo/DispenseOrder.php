<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/6
 * Time: 15:06
 */

namespace App\Models\OrderInfo;

use App\Models\BaseModel;
use App\Models\BasicInfo\Warehouse;
use App\Models\BasicInfo\WarehouseParameter;
use App\Models\Logs\AbNormalLog;
use App\Models\Logs\OperationLog;
use App\Models\Logs\StockRemain;
use App\Models\Permission\Organization;
use App\Models\Permission\Role;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Common\OrderNumber;
use App\Models\OrderInfo\OrderBox;
use App\Models\CenterWarehouse\CollectionArea;
use App\Models\AgencyOrder\AgencyOrder;

class DispenseOrder extends BaseModel {
	protected $table = 't_dispense_order';
	protected $table_agency_order = 't_agency_order';
    protected $table_agency_order_detail= 't_agency_order_detail';

	public $timestamps = false;

	public function getDispenseList($params) {
		$userId = $params['user_id'];
		$type = $params['type'];
		$orgNode = Organization::getOrgPath($userId);
		$warehouseCodes = Warehouse::getWarehouseByOrgId($orgNode);

		//集货单列表
		$model = DB::table('t_dispense_order as dis')
			->join(
				't_warehouse_info as from_ware',
				'dis.warehouse_code',
				'=',
				'from_ware.warehouse_code'
			)->join(
			't_warehouse_info as to_ware',
			'dis.destination_code',
			'=',
			'to_ware.warehouse_code'
		)
			->leftJoin(
				't_dispense_order_detail as dis_it',
				'dis.order_number',
				'=',
				'dis_it.order_number'
			)
			->select(
				'dis.id',
				'dis.order_number',
				'from_ware.warehouse_name as from_warehouse_name',
				'to_ware.warehouse_name as to_warehouse_name',
				'dis.create_time',
				'dis.plate_number',
				'dis.order_status',
				DB::raw('COUNT(dis_it.order_number) as goods_num')
			)
			->groupBy(
				'dis.id',
				'dis.order_number',
				'from_ware.warehouse_name',
				'to_ware.warehouse_name',
				'dis.create_time',
				'dis.plate_number',
				'dis.order_status'
			)
			->orderBy('dis.create_time', 'desc');

		if ($type == '0') {
			$model->whereIn('dis.warehouse_code', $warehouseCodes);
		} else {
			$model->whereIn('dis.destination_code', $warehouseCodes);
		}

		$this->setWhereLike($model, $params, 'dis.order_number');
		$this->setWhereEq($model, $params, 'collect_status');

		//验证是否分页
		if (empty($params['pagesize']) && !isset($params['pagesize'])) {
			return $model->get();
		}
		//分页
		$pagesize = $this->tryGetPageSize($params, parent::PAGESIZE);
		$result = json_decode($model->orderBy('id')->paginate($pagesize)->toJson(), true);
		$data = $result['data'];
		unset($result['data']);
		return $data;
	}

	//获取出库单详情
	public function getDispenseDetail($order_number = null) {
		if (empty($order_number)) {
			return [];
		}

		//获取集货单
		$one = DB::table('t_dispense_order as dis')
			->leftJoin(
				't_dispense_order_detail as dis_it',
				'dis.order_number',
				'=',
				'dis_it.order_number'
			)
			->select(
				'dis.id',
				'dis.order_number',
				'dis.depart_time',
				'dis.arrive_date',
				'dis.warehouse_code',
				'dis.destination_code',
				'dis.update_time',
				'dis.plate_number',
				'dis.contact_name',
				'dis.contact_tel',
				'dis.seal_number',
				'dis.order_status',
				'dis.transport_cropname',
				'dis.transport_crop',
				'dis.confirm_type',
				'dis.car_id',
				'dis.remark'
			)
			->where('dis.order_number', $order_number)
			->groupBy('dis.order_number')
			->orderBy('dis.create_time', 'desc')
			->first();

		// 获取详情
		checkLogic(!empty($one), '出库单未找到');

		$warehouse = Warehouse::getNameOrgByCode($one->warehouse_code);
		$departWarehouse = Warehouse::getNameOrgByCode($one->destination_code);

		$one->warehouse_name = isset($warehouse->warehouse_name) ? $warehouse->warehouse_name : '';
		$one->warehouse_username = isset($warehouse->username) ? $warehouse->username : '';

		$one->destination_name = isset($departWarehouse->warehouse_name) ? $departWarehouse->warehouse_name : '';
		$one->destination_username = isset($departWarehouse->username) ? $departWarehouse->username : '';

		$detail = $this->getCollectOrderByOrderNumber($order_number);
		foreach ($detail as $k => $v) {
			$detail[$k]->box_finish_num = $this->getFinishBoxNum($order_number, $v->collection_no);
			$detail[$k]->type = 1;
		}

		//获取异常
		$ab_normal = AbNormalLog::getAbNormalLog(['related_order' => $order_number]);
		//异常单为空 则不记录异常
		if (count($ab_normal) != 0) {
			$detail[] = [
				'collection_no' => '异常单',
				'line_code' => '',
				'box_num' => count($ab_normal),
				'box_finish_num' => 0,
				'type' => 0,
			];
		}

		array_multisort(array_column($detail, 'type'), SORT_ASC, $detail);
		$one->detail = $detail;
		return $one;
	}

	/**
	 * 扫码收货
	 * @param null $params
	 * @return string
	 * @throws \App\Exceptions\KnownLogicException
	 */
	public function verifyDispenseOrder($params = null) {
		checkLogic(Role::checkUserRole($params['user_id'], 'DISTRIBUTION_MANAGER'), '当前用户不是仓管用户');
		// 获取当前登录用户的详细信息
		$userInfo = User::getUserDetail($params['user_id']);
		$dealer_id = $userInfo->dealer_id;
		checkLogic(!empty($dealer_id), '找不到当前用户所属分仓');
		//拥有的组织节点
		$orgNode = $userInfo->org_node;

		//拥有的仓库权限
		$checkWare = Warehouse::getWarehouseByOrgId($orgNode);

		$orderNumber = $params['order_number'];
		$boxNumber = $params['box_number'];

		$checkBox = substr($boxNumber, 0, 2);

		checkLogic($checkBox == 'RB', '箱号扫描不正确,请核实是否是RB*********');

		$dispenseInfo = DB::table('t_dispense_order')->where('order_number', $orderNumber)->first();
		checkLogic(!is_null($dispenseInfo), '未找到出库单:' . $orderNumber);

		//判断是否有操作目标仓库的权限
		checkLogic(in_array($dispenseInfo->destination_code, $checkWare), $boxNumber . '您对该出库单没凑操作权限，或者此出库单不是到达该仓库，请核实！');

		$boxInfo = DB::table('t_dispense_order_detail as dod')
			->where('dod.goods_no', $boxNumber)
			->select('dod.id', 'dod.order_number', 'dod.goods_no', 'dod.scan_status', 'doo.revice_time', 'doo.create_time', 'destination_code')
			->leftJoin(
				't_dispense_order as doo',
				'doo.order_number',
				'=',
				'dod.order_number'
			)
			->first();

		$lineCode = DB::table('t_order_box as ob')
					->join('t_order_line_info as oli', 'ob.sm_order', '=', 'oli.sm_order')
					->where('ob.box_number', $boxNumber)
					->first(['oli.line_code', 'oli.dealer_id', 'ob.is_arrive', 'oli.order_number', 'oli.bind_number', 'oli.is_single']);
		checkLogic($lineCode != null, '系统不存在此箱号');

		if ($boxInfo != null) {
			if ($boxInfo->scan_status == 0) {
				// 设置箱号到达分仓
				$res = OrderBox::arriveDranch($boxNumber);
				checkLogic($res === true, '箱号标记到达分仓时异常!');
				$result = OrderBox::getArriveDelivery($boxNumber);
				if ($result !== false) {
					//请求MP允许排车
					$mpres = curl_post_erpapi('/consign_order/set_delivery_arrive', ['delivery_no'=>$result]);
					checkLogic($mpres['code'] == 200, '通知MP失败:'.$res['msg']);
				}

				//判断是否有操作目标仓库的权限
				checkLogic(in_array($boxInfo->destination_code, $checkWare), $boxNumber . '您对该出库单没凑操作权限，或者此出库单不是到达该仓库，请核实！');

				$waiting['remark'] = '';
				//开始收货时间与装车单创建时间 小于24H正常 大于24H 为补扫
				$diff_time = time() - (strtotime($boxInfo->create_time) + 24 * 3600);
				if ($diff_time > 0) {
					// 补扫
					$waiting['remark'] = '补扫';
				}

				//启动事务
				DB::beginTransaction();
				//修改
				try {
					//当所有箱子都完成 运货单状态 修改为已收货
					$waiting['scan_status'] = 1;
					$waiting['scan_time'] = hDate();
					$waiting['scan_user'] = $userInfo->login_name;
					DB::table('t_dispense_order_detail')
						->where('id', $boxInfo->id)
						->update($waiting);
					$unScanCount = DB::table('t_dispense_order_detail')
						->where(['order_number' => $boxInfo->order_number, 'scan_status' => 0])
						->count();

					//收货时间以第一个被收货的箱子的那一刻为准
					if ($boxInfo->revice_time == '0000-00-00 00:00:00' || $boxInfo->revice_time == '1000-01-01 00:00:00') {
					    $dispense['revice_time'] = hDate();
					}
					$dispense['update_time'] = hDate();
					$dispense['order_status'] = $unScanCount == 0 ? 6 : 5;
					DB::table('t_dispense_order')
						->where('order_number', $boxInfo->order_number)
						->update($dispense);
					#region 保存在途信息
					$roadData = [$boxNumber => $boxInfo->order_number];
					StockRemain::roadRecord(
						'SUB_RECEIVE',
						$dispenseInfo->destination_code,
						$userInfo->username,
						$roadData
					);
					#endregion

					OperationLog::saveLog(
						$userInfo->org_id,
						$userInfo->login_name,
						'update',
						'扫码收货' . $dispenseInfo->order_number . ' ' . $boxNumber,
						2
					);
					DB::commit();

				} catch (\Exception $e) {
					DB::rollBack();
					throw $e;
				}
			}
		} else {
			//启动事务
			DB::beginTransaction();
			//新增
			try {
				// $lineCode = DB::table('t_order_box as ob')
				// 	->join('t_order_line_info as oli', 'ob.sm_order', '=', 'oli.sm_order')
				// 	->where('ob.box_number', $boxNumber)
				// 	->first(['oli.line_code', 'oli.dealer_id', 'ob.is_arrive']);
				// checkLogic($lineCode != null, '系统不存在此箱号');
				$dealerId = Warehouse::getWarehouseInfo($dispenseInfo->destination_code, 'dealer_id');

				if ($lineCode->dealer_id == $dealerId) {
					if ($lineCode->is_arrive == 0) {
						$res = OrderBox::arriveDranch($boxNumber);
						checkLogic($res === true, '箱号标记到达分仓时异常!');
					}
					$result = OrderBox::getArriveDelivery($boxNumber);
					if ($result !== false) {
						//请求MP允许排车
						$mpres = curl_post_erpapi('/consign_order/set_delivery_arrive', ['delivery_no'=>$result]);
						checkLogic($mpres['code'] == 200, '通知MP失败:'.$res['msg']);
					}
					//查找今日漏扫单
					$toDay = date('Y-m-d');
					$normal = DB::table('t_dispense_order')->where('destination_code', $userInfo->warehouse_code)->where('order_type', 1)->whereDate('create_time', $toDay)->first();
					// var_dump($normal);die;
					if (is_null($normal)) {
						$normalOrderNumber = OrderNumber::getNextNumber('T');
						$insertingData = [
							'order_number' => $normalOrderNumber,
							'org_id' => $userInfo->org_id,
							'warehouse_code' => 'RG01',
							'destination_code' => $userInfo->warehouse_code,
							'create_time' => hdate(),
							'depart_time' => hdate(),
							'car_id' => 0,
							'dirver_id' => 0,
							'order_status' => 6,
							'receiver_tel' => '',
							'revice_time' => hdate(),
							'update_time' => hdate(),
							'operator' => $userInfo->login_name,
							'remark' => '总仓未集货，分仓收货',
							'order_type' => 1,
						];
						DB::table('t_dispense_order')->insert($insertingData);
					} else {
						$normalOrderNumber = $normal->order_number;
					}

					$detail_info = DB::table('t_dispense_order_detail')->where([['order_number', $normalOrderNumber], ['goods_no', $boxNumber]])->first();
					if (is_null($detail_info)) {
						$data = [
							'order_number' => $normalOrderNumber,
							'create_time' => hdate(),
							'goods_no' => $boxNumber,
							'collection_no' => '',
							'goods_type' => '2',
							'goods_typename' => '箱',
							'scan_status' => 1,
							'scan_user' => $userInfo->login_name,
							'scan_time' => hdate(),
							'line_code' => substr($lineCode->line_code, -3),
							'remark' => '总仓未集货，分仓收货',
						];

						DB::table('t_dispense_order_detail')->insert($data);	
					}

				} else {
					//可能串货
					$a = AbNormalLog::saveLog(
						$boxNumber,
						$dispenseInfo->destination_code,
						$orderNumber,
						$userInfo->login_name,
						2
					);
				}

				$roadData = [$boxNumber => $orderNumber];
				StockRemain::roadRecord(
					'SUB_RECEIVE',
					$dispenseInfo->destination_code,
					$userInfo->username,
					$roadData
				);

				DB::commit();

				$warehouse_name = Warehouse::getWarehouseInfoDealerId($lineCode->dealer_id, 'warehouse_name');
				checkLogic($lineCode->dealer_id == $dealerId, $boxNumber . '串货,应属于【' . $warehouse_name . '】');

			} catch (\Exception $e) {
				DB::rollBack();
				throw $e;
			}
		}

		#region 返回扫码收货信息
		$consignInfo = DB::table('t_consign_order_consign_box')
			->where('box_number', $boxNumber)
			->select('waybill_no', 'delivery_number')
			->orderBy('id', 'desc')
			->first();

		if ($consignInfo == null) {
			$mpres = curl_post_erpapi('/consign_order/get_order_info_by_order', ['order'=>$lineCode->order_number]);
			checkLogic($mpres['code'] == 200, 'mp返回:'.$mpres['msg']);
			$order_info = $mpres['data'];
			checkLogic(!empty($order_info), '订单信息为空');
			$collection_model = new CollectionArea();
			$collection_code = $collection_model->getCollectionArea($order_info['location_provinceCode'], $order_info['location_regionCode'], $order_info['location_cityCode'], $dealer_id);
			$waybillData = [
				'box_number' => $boxNumber,
				'order_number' => $lineCode->order_number,
				'collection_code' => $collection_code,
				'is_urgent' => 0,
				'is_single' => $lineCode->is_single,
				'is_line' => 0
			];

			//未排车 记录异常
			AbNormalLog::saveLog(
				$boxNumber,
				$dispenseInfo->destination_code,
				$orderNumber,
				$userInfo->login_name,
				4
			);

		} else {
			$deliverInfo = DB::table('t_consign_order as con')
				->join('t_consign_order_item as item', function ($join) use($consignInfo){
			        $join->on('item.waybill_no', '=', 'con.waybill_no')
			             ->where('item.delivery_number', '=', $consignInfo->delivery_number);
			    })
				->leftJoin('t_car as car', 'car.id', '=', 'con.car_id')
				->select(
					'con.waybill_no',
					'waybill_index',
					'car.driver_name',
					'plate_number',
					'item.is_urgent'
				)
				->where('con.waybill_no', $consignInfo->waybill_no)
				->first();
			if ($deliverInfo == null) {
				$mpres = curl_post_erpapi('/consign_order/get_order_info_by_order', ['order'=>$lineCode->order_number]);
				checkLogic($mpres['code'] == 200, 'mp返回:'.$mpres['msg']);
				$order_info = $mpres['data'];
				checkLogic(!empty($order_info), '订单信息为空');
				$collection_model = new CollectionArea();
				$collection_code = $collection_model->getCollectionArea($order_info['location_provinceCode'], $order_info['location_regionCode'], $order_info['location_cityCode'], $dealer_id);
				$waybillData = [
					'box_number' => $boxNumber,
					'order_number' => $lineCode->order_number,
					'collection_code' => $collection_code,
					'is_urgent' => 0,
					'is_single' => $lineCode->is_single,
					'is_line' => 0
				];

				//未排车 记录异常
				AbNormalLog::saveLog(
					$boxNumber,
					$dispenseInfo->destination_code,
					$orderNumber,
					$userInfo->login_name,
					4
				);

			} else {
				$waybillData = [
					'box_number' => $boxNumber,
					'waybill_no' => $deliverInfo->waybill_no,
					'sort' => $deliverInfo->waybill_index,
					'time' => substr($deliverInfo->waybill_no, -9, 4),
					'car_index' => substr($deliverInfo->waybill_no, -5),
					'is_urgent' => $deliverInfo->is_urgent,
					'is_single' => $lineCode->is_single,
					'is_line' => 1
				];
			}
		}

		#endregion

		return hSucceed('', $waybillData);
	}

	/**
	 * 扫码收货(代理商平台)
	 * @param null $params
	 * @return string
	 * @throws \App\Exceptions\KnownLogicException
	 */
	public function verifyAgentPlatformOrder($params){
		checkLogic(Role::checkUserRole($params['user_id'], 'DISTRIBUTION_MANAGER'), '当前用户不是仓管用户');
		// 获取当前登录用户的详细信息
		$userInfo = User::getUserDetail($params['user_id']);
		$dealer_id = $userInfo->dealer_id;
		checkLogic(!empty($dealer_id), '找不到当前用户所属分仓');
		//拥有的组织节点
		$orgNode = $userInfo->org_node;
		//拥有的仓库权限
		$checkWare = Warehouse::getWarehouseByOrgId($orgNode);

		$boxNumber = $params['box_number'];
		//请求第三方标记已经到达分仓
		$box_info = DB::table('t_order_box as ob')
					->leftJoin('t_order_line_info as oli', 'ob.sm_order', '=', 'oli.sm_order')
					->where('ob.box_number', $boxNumber)
					->first(['oli.line_code', 'oli.dealer_id', 'ob.destination_code', 'oli.order_number', 'oli.bind_number', 'ob.is_arrive', 'oli.is_single']);

		checkLogic($box_info != null, '系统不存在此箱号');

		//判断是否有操作目标仓库的权限
		checkLogic(in_array($box_info->destination_code, $checkWare), '您对该箱号'.$boxNumber.'没有操作权限,请核实!');

		$dealerId = Warehouse::getWarehouseInfo($box_info->destination_code, 'dealer_id');

		$orderNumber = $box_info->order_number;

		if ($box_info->dealer_id != $dealerId) {
			//可能串货
			AbNormalLog::saveLog(
				$boxNumber,
				$box_info->destination_code,
				$orderNumber,
				$userInfo->login_name,
				2
			);

			$roadData = [$boxNumber => $orderNumber];
			StockRemain::roadRecord(
				'SUB_RECEIVE',
				$box_info->destination_code,
				$userInfo->username,
				$roadData
			);

			$warehouse_name = Warehouse::getWarehouseInfoDealerId($box_info->dealer_id, 'warehouse_name');
			checkLogic($box_info->dealer_id == $dealerId, $boxNumber . '串货,应属于【' . $warehouse_name . '】');
		}else{
			// 是否拒收
		 	$is_reject_box = $this->checkBoxReject($boxNumber);

		 	checkLogic($is_reject_box !== 0, '此箱号:'.$boxNumber.'，被拒收了！');

			// 标记箱号到达分仓
			$consignInfo = DB::table('t_consign_order_consign_box')
				->where('box_number', $boxNumber)
				->select('waybill_no', 'delivery_number')
				->orderBy('id', 'desc')
				->first();
			if ($consignInfo == null) {
				$mpres = curl_post_erpapi('/consign_order/get_order_info_by_order', ['order'=>$orderNumber]);
				checkLogic($mpres['code'] == 200, 'mp返回:'.$mpres['msg']);
				$order_info = $mpres['data'];
				checkLogic(!empty($order_info), '订单信息为空');
				$collection_model = new CollectionArea();
				$collection_code = $collection_model->getCollectionArea($order_info['location_provinceCode'], $order_info['location_regionCode'], $order_info['location_cityCode'], $dealer_id);
				$waybillData = [
					'box_number' => $boxNumber,
					'order_number' => $orderNumber,
					'collection_code' => $collection_code,
					'is_urgent' => 0,
					'is_line' => 0,
					'is_single' => $box_info->is_single
				];
			} else {
				$deliverInfo = DB::table('t_consign_order as con')
					->join('t_consign_order_item as item', function ($join) use($consignInfo){
				        $join->on('item.waybill_no', '=', 'con.waybill_no')
				             ->where('item.delivery_number', '=', $consignInfo->delivery_number);
				    })
					->leftJoin('t_car as car', 'car.id', '=', 'con.car_id')
					->select(
						'con.waybill_no',
						'waybill_index',
						'car.driver_name',
						'plate_number',
						'item.is_urgent'
					)
					->where('con.waybill_no', $consignInfo->waybill_no)
					->first();

				if ($deliverInfo == null) {
					$mpres = curl_post_erpapi('/consign_order/get_order_info_by_order', ['order'=>$orderNumber]);
					checkLogic($mpres['code'] == 200, 'mp返回:'.$mpres['msg']);
					$order_info = $mpres['data'];
					checkLogic(!empty($order_info), '订单信息为空');
					$collection_model = new CollectionArea();
					$collection_code = $collection_model->getCollectionArea($order_info['location_provinceCode'], $order_info['location_regionCode'], $order_info['location_cityCode'], $dealer_id);
					$waybillData = [
						'box_number' => $boxNumber,
						'order_number' => $orderNumber,
						'collection_code' => $collection_code,
						'is_urgent' => 0,
						'is_line' => 0,
						'is_single' => $box_info->is_single
					];

				} else {
					$waybillData = [
						'box_number' => $boxNumber,
						'waybill_no' => $deliverInfo->waybill_no,
						'sort' => $deliverInfo->waybill_index,
						'time' => substr($deliverInfo->waybill_no, -9, 4),
						'car_index' => substr($deliverInfo->waybill_no, -5),
						'is_urgent' => $deliverInfo->is_urgent,
						'is_line' => 1,
						'is_single' => $box_info->is_single
					];
				}
			}
			
			return $waybillData;
		}

	}


	/**
	 * 获取省代售后线路
	 */
	public function getAgentAfterLine($box_number){
		$agency = new AgencyOrder();
		$agencyOrder = $agency->getAgencyDetailByBoxNumber($box_number);
		checkLogic(!is_null($agencyOrder), '系统不存在此箱号（售后）');

		// 标记箱号到达分仓
		$consignInfo = DB::table('t_consign_order_consign_box')
			->where('box_number', $box_number)
			->select('waybill_no', 'delivery_number')
			->orderBy('id', 'desc')
			->first();
		if ($consignInfo == null) {

			$waybillData = [
				'box_number' => $box_number,
				'order_number' => $agencyOrder->order_number,
				'collection_code' => '(省代售后)',
				'is_urgent' => 0,
				'is_line' => 0,
				'is_single' => 0
			];
		} else {
			$deliverInfo = DB::table('t_consign_order as con')
				->join('t_consign_order_item as item', function ($join) use($consignInfo){
			        $join->on('item.waybill_no', '=', 'con.waybill_no')
			             ->where('item.delivery_number', '=', $consignInfo->delivery_number);
			    })
				->leftJoin('t_car as car', 'car.id', '=', 'con.car_id')
				->select(
					'con.waybill_no',
					'waybill_index',
					'car.driver_name',
					'plate_number',
					'item.is_urgent'
				)
				->where('con.waybill_no', $consignInfo->waybill_no)
				->first();

			if ($deliverInfo == null) {

				$waybillData = [
					'box_number' => $box_number,
					'order_number' => $agencyOrder->order_number,
					'collection_code' => '(省代售后)',
					'is_urgent' => 0,
					'is_line' => 0,
					'is_single' => 0
				];

			} else {
				$waybillData = [
					'box_number' => $box_number,
					'waybill_no' => $deliverInfo->waybill_no,
					'sort' => $deliverInfo->waybill_index,
					'time' => substr($deliverInfo->waybill_no, -9, 4),
					'car_index' => substr($deliverInfo->waybill_no, -5),
					'is_urgent' => $deliverInfo->is_urgent,
					'is_line' => 1,
					'is_single' => 0
				];
			}
		}
		
		return $waybillData;
	}

	//根据出库单所有箱号
	public function getBoxDetailByOrderNumber($order_number = null, $status = null) {
		if (empty($order_number)) {
			return [];
		}

		$model = DB::table('t_dispense_order_detail')
			->select('order_number', 'goods_no', 'line_code', 'scan_status');

		if (!is_null($status)) {
			$model->where('scan_status', '=', $status);
		}

		$detail = $model->where('order_number', $order_number)->get()->toArray();

		return $detail;
	}

	//完成出库
	public function dispenseFinish($params) {
		checkLogic(Role::checkUserRole($params['user_id'], 'WAREHOUSE_MANAGER'), '当前用户不是仓管用户');
		$userInfo = User::getUserInfo($params['user_id']);
		$order_number = $params['order_number'];
		$info = $this->getDispenseDetail($order_number);
		checkLogic($info->order_status == 1, '该出库单已经出库，请勿重复操作!');

		$updateData = [
			'order_status' => 3,
			'update_time' => hdate(),
		];
		if ($params['type'] == 2) {
			checkLogic(
				isset($params['seal_number']) && !empty($params['seal_number']),
				'选择扫描封车码完成出库，但是未传入封车码'
			);
			$sealNo = $params['seal_number'];
			$checkCount = DB::table('t_dispense_order')
				->where('seal_number', $sealNo)
				->where('order_status', '>', '-1')
				->count();
			checkLogic($checkCount == 0, '封车码' . $sealNo . '已被使用');

			$updateData['seal_number'] = $sealNo;
			$updateData['confirm_type'] = 2;
		} else {
			$updateData['confirm_type'] = 1;
		}
		try {
			$goods_info = DB::table('t_dispense_order_detail')->where('order_number', $order_number)->pluck('goods_no')->toArray();
			$onRoadData = [];
			foreach ($goods_info as $value) {
				$onRoadData[$value] = $order_number;
			}
			StockRemain::roadRecord('WARE_SENT', $info->plate_number, $userInfo->username, $onRoadData);

			DB::table('t_dispense_order')->where('order_number', $order_number)->update($updateData);
			//添加日志
			OperationLog::saveLog($userInfo->org_id, $userInfo->login_name, 'update', '修改出库单' . $order_number, 2);
			DB::commit();
			$info->order_status = 3;
			return $info;
		} catch (\Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	//根据出库单号获取已经添加的集货单单号
	public function getCollectionNoByOrderNumber($order_number) {
		return DB::table('t_dispense_order_detail')
			->groupBy('collection_no')
			->where('order_number', $order_number)
			->pluck('collection_no')
			->toArray();
	}

	//根据出库单号获取已经收完货集货单号
	public function getCollectionNoFinishByOrderNumber($order_number) {
		return DB::table('t_dispense_order_detail')
			->groupBy('collection_no')
			->where([['order_number', $order_number], ['scan_status', 1]])
			->pluck('collection_no')->toArray();
	}

	//根据出库单号集货单
	public function getCollectOrderByOrderNumber($order_number) {
		return DB::table('t_dispense_order_detail')
			->select(
				'collection_no',
				'line_code',
				DB::raw('COUNT(id) as box_num')
			)
			->groupBy('collection_no')
			->where('order_number', $order_number)
			->where('collection_no', '<>', '')
			->get()
			->toArray();
	}

	//获取集货单下已经收完的箱数
	public function getFinishBoxNum($order_number, $collection_no) {
		return DB::table('t_dispense_order_detail')
			->where([['order_number', $order_number], ['collection_no', $collection_no], ['scan_status', 1]])
			->count();
	}

	//根据出库单和箱号获取一个出库详情
	public function getDetailByOrderGoodsNo($order_number, $goods_no) {
		return DB::table('t_dispense_order_detail')
			->where([['order_number', $order_number], ['goods_no', $goods_no]])
			->first();
	}

	//获取集货单下已经收完的箱字明细
	public function getBoxDetail($order_number, $collection_no) {
		return DB::table('t_dispense_order_detail')
			->select('goods_no', 'scan_time', 'scan_status')
			->where([['order_number', $order_number], ['collection_no', $collection_no]])
			->get()->toArray();
	}

	/**
	 * 车辆到达分仓时执行 type:1人工,2封车码
	 * @param $params
	 * @throws \Exception
	 */
	public function carArrive($params) {
		checkLogic(Role::checkUserRole($params['user_id'], 'DISTRIBUTION_MANAGER'), '当前用户不是仓管用户');
		// 获取当前登录用户的详细信息
		$userInfo = User::getUserDetail($params['user_id']);

		$orderNumber = $params['order_number'];
		$type = $params['type'];
		$sealNumber = $params['seal_number'];
		//获取出库信息
		$dispenseInfo = DB::table('t_dispense_order')->where('order_number', $orderNumber)->first();
		checkLogic(!is_null($dispenseInfo), '出库单' . $orderNumber . '未找到');

		$orgNode = $userInfo->org_node;
		//拥有的仓库权限
		$checkWare = Warehouse::getWarehouseByOrgId($orgNode);

		checkLogic($dispenseInfo->order_status == 3, $dispenseInfo->order_number . '仅在出库单为已发车状态允许此操作');
		//判断是否有操作目标仓库的权限
		checkLogic(in_array($dispenseInfo->destination_code, $checkWare), '您对该出库单没凑操作权限，或者此出库单不是到达该仓库，请核实！');

		if ($type == 1) {
			checkLogic($dispenseInfo->confirm_type == 1, '原出库单使用封车码出库，请扫描封车码确认');
		} else {
			checkLogic($dispenseInfo->confirm_type == 2, '本出库单人工确认出库，仅支持人工确认');
			checkLogic($dispenseInfo->seal_number == $sealNumber, '您扫描的封车码和原封车码不一致');
		}

		//返分仓收货模式 1到达立即收货，2必须扫码收货
		// $receiveType = Warehouse::getWarehouseInfo($dispenseInfo->destination_code, 'receive_type');  //禁用
		$receiveType = WarehouseParameter::getWarehouseConfig('receive_type', $userInfo->warehouse_code);

		DB::beginTransaction();
		try {
			if ($receiveType != 1) {
				//必须扫码收货
				DB::table('t_dispense_order')
					->where('order_number', $orderNumber)
					->update([
						'revice_time' => hdate(),
						'order_status' => 4,
						'update_time' => hdate(),
					]);

				OperationLog::saveLog(
					$userInfo->org_id,
					$userInfo->login_name,
					'update',
					'扫描封车码确认车辆到达' . $dispenseInfo->order_number,
					2
				);
			} else {
				//到达时直接收货
				DB::table('t_dispense_order')
					->where('order_number', $orderNumber)
					->update([
						'revice_time' => hdate(),
						'order_status' => 6,
						'update_time' => hdate(),
					]);
				//当所有箱子都完成 运货单状态 修改为已收货
				$waiting['scan_status'] = 1;
				$waiting['scan_time'] = hDate();
				$waiting['scan_user'] = $userInfo->login_name;
				DB::table('t_dispense_order_detail')
					->where('order_number', $orderNumber)
					->update($waiting);

				#region 保存在途信息
				$roadData = [];
				$receiveData = DB::table('t_dispense_order_detail')
					->where('order_number', $orderNumber)
					->where('scan_status', 0)
					->get(['goods_no', 'order_number']);
				foreach ($receiveData as $r) {
					$roadData[$r->goods_no] = $r->order_number;
				}
				StockRemain::roadRecord(
					'SUB_RECEIVE',
					$dispenseInfo->destination_code,
					$userInfo->username,
					$roadData
				);
				#endregion

				OperationLog::saveLog(
					$userInfo->org_id,
					$userInfo->login_name,
					'update',
					'扫描封车码确认车辆到达，并且完成入库' . $dispenseInfo->order_number,
					2
				);
			}
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollBack();
			throw $ex;
		}
	}

	//干线报表
	public function getDrylineList($params)
	{
		$userInfo = User::getUserDetail($params['user_id']);

		//非仓管不可看
		checkLogic(in_array('WAREHOUSE_MANAGER', $userInfo->role_info['role_code']), '您不是仓管人员,不具备查看此操作!');

		//统计一周的数据
		$whereDate = date('Y-m-d', strtotime("-30 day"));

		$model = DB::table('t_dispense_order as dor')
			->select(
				'dor.order_number',
				DB::raw('LEFT(dor.create_time, 10) as create_time'),
				'dor.plate_number',
				'from.warehouse_name as from_warehouse_name',
				'to.warehouse_name as to_warehouse_name',
				DB::raw('COUNT(dod.goods_no) AS out_box'),
				DB::raw('COUNT(CASE WHEN dod.scan_status=1 THEN dod.goods_no ELSE NULL END) AS into_box')
			)
			->leftJoin('t_dispense_order_detail as dod', 'dod.order_number', '=', 'dor.order_number')
			->leftJoin('t_warehouse_info as from', 'from.warehouse_code', '=', 'dor.warehouse_code')
			->leftJoin('t_warehouse_info as to', 'to.warehouse_code', '=', 'dor.destination_code')
			->where('dor.create_time', '>', $whereDate)
			->whereIn('dor.org_id', $userInfo->org_node)
			->groupBy('dor.order_number')
			->orderBy('dor.id', 'desc');

		$drylineList = $model->get()->toArray();

		foreach ($drylineList as $ds_key => $des_val) {
			if ($des_val->out_box - $des_val->into_box == 0) {
				$drylineList[$ds_key]->status = '完成';
			} else if($des_val->out_box - $des_val->into_box > 0) {
				$drylineList[$ds_key]->status = '未完';
			} else {
				$drylineList[$ds_key]->status = '异常';
			}
		}

		return $drylineList;
	}

	//分仓报表
	public function getWarehouseList($params)
	{
		$whereDate = date('Y-m-d', strtotime("-30 day"));

		$userInfo = User::getUserDetail($params['user_id']);
		$warehouse_code = $userInfo->warehouse_code;

		if (isset($params['warehouse_code']) && !empty($params['warehouse_code'])) {
			$warehouse_code = $params['warehouse_code'];
		}

		hOpenDBLog();
		//连接发货数数据
		$receiveInfo = DB::table('t_consign_order as co')
				->select(
					'warehouse_code',
					DB::raw('LEFT(load_time, 10) as dates'),
					DB::raw('"out" as type'),
					DB::raw('sum(cocb.box_status) as box_num')
				)->join('t_consign_order_consign_box as cocb', function ($join) {
			        $join->on(
			        	'cocb.waybill_no',
			        	'=',
			        	'co.waybill_no'
			        )->where('cocb.box_status', 1);
			    })->where(
			    	'co.load_time',
			    	'<>',
			    	'0000-00-00 00:00:00'
			    )->groupBy(
			    	'co.warehouse_code',
					DB::raw('LEFT(co.load_time, 10)')
			    );

		//获取收货数据
		$sendInfo = DB::table('t_dispense_order as dd')
				->select(
					'dd.destination_code as warehouse_code',
					DB::raw('LEFT (dd.revice_time, 10) as dates'),
					DB::raw('"in" as type'),
					DB::raw('SUM(dod.scan_status) as box_num')
				)->join('t_dispense_order_detail as dod', function ($join) {
			        $join->on(
			        	'dod.order_number',
			        	'=',
			        	'dd.order_number'
			        )->where('dod.scan_status', 1);
			    })->where(
			    	'dd.revice_time',
			    	'<>',
			    	'0000-00-00 00:00:00'
			    )->groupBy(
			    	'dd.destination_code',
					DB::raw('LEFT(dd.revice_time, 10)')
			    );

		// 连接异常收货数据
		$query = DB::table('t_box_abnormal as ab')
				->select(
					'ab.warehouse_code',
					DB::raw('LEFT (ab.scan_time, 10) as dates'),
					DB::raw('"in" as type'),
					DB::raw('count(ab.id) as box_num')
				)->where(
					'ab.scan_type',
					'=',
					'TRANSREGIONAL'
				)->groupBy(
					DB::raw('LEFT(ab.scan_time, 10)')
				)
				->unionAll($receiveInfo)
				->unionAll($sendInfo);

		$query->get();
		$querySql = hGetDBLogStr();

		$warehouseList = DB::table(DB::raw("($querySql) as a"))
			// ->mergeBindings($querySql)
			->select(
				'wi.warehouse_code',
				'wi.warehouse_name',
				'dates',
				DB::raw("IFNULL(sum(IF(type = 'in', box_num, 0)), 0) intos"),
				DB::raw("sum(IF(type = 'out', box_num, 0)) as outs")
			)->leftJoin(
				't_warehouse_info as wi',
				'wi.warehouse_code',
				'=',
				'a.warehouse_code'
			)->where(
				'dates',
				'>',
				$whereDate
			)->groupBy(
				'dates',
				'wi.warehouse_code'
			)
			->where('wi.warehouse_code', $warehouse_code)
			->orderBy('dates', 'desc')
			->get()
			->toArray();

		foreach ($warehouseList as $ware_key => $ware_val) {
			$warehouseList[$ware_key]->stock = $ware_val->intos - $ware_val->outs;
		}

		return $warehouseList;
	}


	/**
	 * 校验箱子是否拒收 0 未拒收
	 * @param $box_number
	 * @return int
	 */
	public function checkBoxReject($box_number){
		$reject_box = DB::table($this->table_agency_order_detail.' as aod')
				->leftJoin($this->table_agency_order.' as ao', 'aod.agency_id', '=', 'ao.id')
				->where('ao.status', '!=', 4)
				->where('box_number', $box_number)
				->count();
		return $reject_box;
	}
}