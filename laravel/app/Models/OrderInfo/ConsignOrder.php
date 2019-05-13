<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/17
 * Time: 15:16
 */

namespace App\Models\OrderInfo;

use App\Models\BaseModel;
use App\Models\BasicInfo\Warehouse;
use App\Models\Logs\AbNormalLog;
use App\Models\Logs\OperationLog;
use App\Models\Logs\StockRemain;
use App\Models\Permission\Organization;
use App\Models\Permission\Role;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Jobs\JobHelper;
use App\Jobs\OrderJobs\SaveAgencyConsign;
use App\Jobs\ErpJobs\CreateReturnOrder;
use App\Jobs\AgencyJobs\SaveAgencyConsignOut;
use App\Jobs\AgencyJobs\saveAgencyAfterOutCustomer;
use App\Models\Logs\ApiRequestLog;

class ConsignOrder extends BaseModel {
	protected $table = 't_consign_order';
	protected $t_consign_order_check_out = 't_consign_order_check_out';
	public $timestamps = false;

	//异常记录类型
	const SCAN_TYPE = [
		1 => 'COLLECT_LEAKAGE', //总仓漏扫
		2 => 'TRANSREGIONAL', //串货
		3 => 'REPEAT_OUTBOUND', //重复出库
		4 => 'NOT_LINE' //没有排车信息
	];
	//类型值
	const SCAN_VALUE = [
		'COLLECT_LEAKAGE' => '总仓漏扫',
		'TRANSREGIONAL' => '串货',
		'REPEAT_OUTBOUND' => '重复出库',
		'NOT_LINE' => '无排车信息'
	];

	//类型值
	const SCAN_STATUS = [
		'0' => '未扫',
		'1' => '已扫',
	];
    //验货类型值
    const CHECK_TYPE = [
        'AGAIN' => '改日送',
        'ALL_REJECT' => '整单拒收',
        'PART_REJECT' => '部分拒收',
        'AFTER' => '售后提货',
    ];

	public function listConsignOrder($params) {
		//七天前
		$time = date('Y-m-d H:i:s', strtotime(date('Y-m-d', time() - 24 * 3600 * 7)));
		$org_node = Organization::getOrgPath($params['user_id']);
		$warehouseCodes = Warehouse::getWarehouseByOrgId($org_node);
		$model = DB::table('t_consign_order as cons')
			->leftjoin('t_car as car', 'car.id', '=', 'cons.car_id')
			->join(
				't_consign_order_consign_box as box_info',
				'box_info.waybill_no',
				'=',
				'cons.waybill_no'
			)->whereIn('warehouse_code', $warehouseCodes)
			->where('cons.created', '>', $time)
			->where('cons.waybill_status', '<>', -1)
		//不允许直接在外面附加任何where语句，只能在下面的callback里面增加，因为warehouse_code
			->where(function ($query) use ($params) {
				if (isset($params['search'])) {
					$tempPara = $params;
					$tempPara['remark'] = $params['search'];
					$tempPara['waybill_no'] = $params['search'];
					$tempPara['box_number'] = $params['search'];
					$tempPara['plate_number'] = $params['search'];
					$tempPara['delivery_number'] = $params['search'];
					$this->setWhereLike($query, $tempPara, 'cons.remark', true);
					$this->setWhereLike($query, $tempPara, 'cons.waybill_no', true);
					$this->setWhereLike($query, $tempPara, 'box_number', true);
					$this->setWhereLike($query, $tempPara, 'car.plate_number', true);
					$this->setWhereLike($query, $tempPara, 'cons.delivery_number', true);
				}
			})
			->select(
				'cons.waybill_no',
				//'weight',
				//'volume',
				'waybill_status',
				'cons.created',
				'load_time',
				'cons.remark',
				//'cons.modified',
				'warehouse_code',
				'out_time',
				//'plan_arrive',
				'box_count',
				'cons.driver_name',
				DB::raw("IFNULL(car.plate_number, '') as plate_number"),
				DB::raw("count(*) as total_box"),
				DB::raw("CAST(SUM(box_status) as SIGNED) as scan_box")
			)
			->groupBy(
				'cons.waybill_no',
				//'weight',
				//'volume',
				'waybill_status',
				'cons.created',
				'load_time',
				'cons.remark',
				//'cons.modified',
				'warehouse_code',
				'out_time',
				//'plan_arrive',
				'box_count',
				'cons.driver_name',
				'car.plate_number'
			);
		$this->setWhereBetween($model, $params, 'cons.created', 'create_s', 'created_e');

		$data = $model->orderBy('cons.id', 'desc')->get();
		return $data;
	}

	public function getConsignOrderDetail($waybillNo) {
		$data = DB::table('t_consign_order as c')
			->join('t_warehouse_info as w', 'w.warehouse_code', '=', 'c.warehouse_code')
			->leftjoin('t_car as car', 'car.id', '=', 'c.car_id')
			->where('c.waybill_no', $waybillNo)
			->select(
				'c.id',
				'c.waybill_no',
				'c.car_id',
				'c.weight',
				'c.volume',
				'c.waybill_status',
				'c.created',
				'load_time',
				'c.remark',
				'c.modified',
				'c.createdBy',
				'w.warehouse_name',
				'c.warehouse_code',
				'c.out_time',
				'c.plan_arrive',
				'c.contact',
				'c.box_count',
				'c.driver_name',
				DB::raw("IFNULL(car.plate_number, '') as plate_number")
			)
			->first();

		checkLogic($data != null, '未找到出库装车单');

		$item = DB::table('t_consign_order_consign_box as cocb')
			->leftjoin('t_consign_order_item as coi', 'coi.delivery_number', '=', 'cocb.delivery_number')
			->where('cocb.waybill_no', $waybillNo)
			->select('cocb.box_number', 'cocb.box_status', 'cocb.scan_user', 'cocb.scan_time', 'cocb.create_time', 'coi.is_urgent')
			->get();

		$data->item = $item;

		return $data;
	}

	/**
	 * 分仓发货扫码装车
	 * @param $userId
	 * @param $waybillNo
	 * @param $boxNumbers
	 * @return string
	 * @throws \Exception
	 */
	public function verifyWaybillBox($userId, $waybillNo, $boxNumbers) {
		if (Role::checkUserRole($userId, 'DISTRIBUTION_MANAGER') == false) {
			return '当前用户不是仓管用户';
		}
		$orgNode = Organization::getOrgPath($userId);
		$warehouseCodes = Warehouse::getWarehouseByOrgId($orgNode);
		$userInfo = User::getUserInfo($userId);
		$waybillInfo = DB::table('t_consign_order')
			->where('waybill_no', $waybillNo)
			->select('waybill_status', 'warehouse_code', 'car_id')
			->first();
		$boxNumbers = json_decode($boxNumbers, 1);
		if (empty($boxNumbers)) {
			return '未传入箱码';
		}

		if (is_null($waybillInfo)) {
			return '装车单未找到';
		}

		if (!in_array($waybillInfo->warehouse_code, $warehouseCodes)) {
			return '您无权操作此仓库的装车单';
		}
		checkLogic(in_array($waybillInfo->waybill_status, [1, 2]), '装车单状态不允许');

		$boxInfo = DB::table('t_consign_order_consign_box')
			->where('waybill_no', $waybillNo)
			->whereIn('box_number', $boxNumbers)
			->select('id', 'box_number', 'box_status')
			->get();
		$remark = '';
		$updatingId = [];
		$updatingBoxNumbers = [];
		foreach ($boxNumbers as $number) {
			$box = $boxInfo->first(function ($value, $key) use ($number) {
				return $value->box_number == $number;
			});
			if (is_null($box)) {
				return '装车单' . $waybillNo . '没有找到箱号为' . $number . '的箱子, 请确认是否扫描错误';
			}
			if ($box->box_status != 0) {
				$remark .= $number . ' ';
			} else {
				$updatingBoxNumbers[] = $box->box_number;
			}
			$updatingId[] = $box->id;
		}

		$updateData = [
			'scan_user' => $userInfo->login_name,
			'scan_time' => hdate(),
			'box_status' => 1,
		];

		try {
			DB::table('t_consign_order_consign_box')
				->whereIn('id', $updatingId)
				->update($updateData);

			#region 保存在途信息
			$roadData = [];
			foreach ($updatingBoxNumbers as $box) {
				$roadData[$box] = $waybillNo;
			}
			StockRemain::roadRecord(
				'SUB_SENT',
				$waybillInfo->car_id,
				$userInfo->username,
				$roadData
			);
			#endregion

			OperationLog::saveLog(
				$userInfo->org_id,
				$userInfo->login_name,
				'update',
				'扫码装车' . $waybillNo . '箱码' . json_encode($boxNumbers),
				2
			);
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollBack();
			throw $ex;
		}

		$existedRemark = empty($remark) ? '' : ('下列箱号已经扫描:' . $remark);
		return ['msg' => '共验证' . count($boxNumbers) . '箱 ' . $existedRemark];
	}

	/**
	 * 完成
	 * @param $userId
	 * @param $waybillNo
	 * @throws \Exception
	 */
	public function finishLoadConsign($userId, $waybillNo) {

		checkLogic(Role::checkUserRole($userId, 'DISTRIBUTION_MANAGER'), '当前用户不是仓管用户');
		$orgNode = Organization::getOrgPath($userId);
		$warehouseCodes = Warehouse::getWarehouseByOrgId($orgNode);
		$userInfo = User::getUserInfo($userId);
		$waybillInfo = DB::table('t_consign_order')
			->where('waybill_no', $waybillNo)
			->select('waybill_status', 'warehouse_code', 'car_id')
			->first();
		checkLogic($waybillInfo != null, '装车单未找到');
		checkLogic(in_array($waybillInfo->warehouse_code, $warehouseCodes), '您无权操作此仓库的装车单');
		checkLogic(in_array($waybillInfo->waybill_status, [1, 2]), '装车单状态不允许');

		$updateData = [
			'load_time' => hdate(),
			'waybill_status' => 3,
			'modified' => hdate(),
		];

		DB::beginTransaction();
		try {
			DB::table('t_consign_order')
				->where('waybill_no', $waybillNo)
				->update($updateData);

			OperationLog::saveLog(
				$userInfo->org_id,
				$userInfo->login_name,
				'update',
				'完成装车单' . $waybillNo,
				2
			);

			#region 保存在途信息
			// $boxFinish = $this->getFinishBoxNumberBywaybillNo($waybillNo);
			// $roadData = [];
			// foreach ($boxFinish as $box) {
			// 	$roadData[$box] = $waybillNo;
			// }
			// StockRemain::roadRecord(
			// 	'SUB_SENT',
			// 	$waybillInfo->car_id,
			// 	$userInfo->username,
			// 	$roadData
			// );
			#endregion

			DB::commit();
		} catch (\Exception $ex) {
			DB::rollBack();
			throw $ex;
		}

		$order_delivery = DB::table('t_consign_order_consign_box')
			->where('waybill_no', $waybillNo)
			->where('order_number', 'like', 'ASAP%')
			->groupBy('order_number')
			->pluck('order_number', 'delivery_number')
			->toArray();

		if (!empty($order_delivery)) {
			foreach ($order_delivery as $delivery_number => $after_number) {
				//通知OMS省代出库
	     		JobHelper::dispatchJob(
					saveAgencyAfterOutCustomer::class, ['as_number'=> $after_number, 'delivery_number' => $delivery_number]
				);
			}
		}

		return true;
	}

	//获取箱号
	public function boxNumberBywaybillNo($waybill_no = null) {
		if (is_null($waybill_no)) {
			return [];
		}

		return DB::table('t_consign_order_consign_box')->where('waybill_no', $waybill_no)->pluck('box_number')->toArray();
	}

	//获取已经装车的箱号
	public function getFinishBoxNumberBywaybillNo($waybill_no = null) {
		if (is_null($waybill_no)) {
			return [];
		}

		return DB::table('t_consign_order_consign_box')
			->where('waybill_no', $waybill_no)
			->where('box_status', 1)
			->pluck('box_number')->toArray();
	}

	/**
	 * 验证箱子
	 */
	public function consignBoxCheck($waybill_no, $box_number, $user_id) {
		$boxPrefix = substr($box_number, 0, 2);
		if ($boxPrefix != 'RB') {
			return '未识别的箱号:' . $box_number;
		};

		$waybill_info = DB::table('t_consign_order_consign_box as cocb')
			->select('cocb.box_status', 'cocb.waybill_no', 'co.car_id')
			->leftjoin('t_consign_order as co', 'co.waybill_no', '=', 'cocb.waybill_no')
			->where('cocb.box_number', $box_number)
			->orderBy('cocb.id', 'desc')
			->first();

		if (is_null($waybill_info)) {
			return '装车单中找不到' . $box_number . '的箱子,请核实是否已经排车!';
		}

		if ($waybill_info->waybill_no != $waybill_no) {
			$plate_number = DB::table('t_car')->where('id', $waybill_info->car_id)->value('plate_number');
			$error_msg = $box_number . '不属于当前装车单,应属于：'.$waybill_info->waybill_no;
			if (!is_null($plate_number)) {
				$error_msg .= '车牌号：'.$plate_number;
			}
			return $error_msg;
		}

		$waybill_num = DB::table('t_consign_order_consign_box')->where('box_number', $box_number)->count();

		if ($waybill_num >= 2) {
			$userInfo = User::getUserDetail($user_id);
			//记录日志
			try {
				AbNormalLog::saveLog(
					$box_number,
					$userInfo->warehouse_code,
					$waybill_no,
					$userInfo->login_name,
					3
				);
			} catch (\Exception $e) {
				throw $e;
			}
		}

		if ($waybill_info->box_status == 1) {
			return $box_number . '已经出库请勿重复扫描!';
		}

		return true;
	}

	/**
	 * 获取分仓排车信息
	 */
	public function getBoxInfo($params) {
		$userInfo = User::getUserDetail($params['user_id']);
		$orgId = $userInfo->org_node;
		$warehouseCode = Warehouse::getWarehouseByOrgId($orgId);

		$model = DB::table('t_consign_order_consign_box as ocb')
			->select('ocb.box_number', 'wi.warehouse_name', 'co.created', 'oli.line_code')
			->leftjoin('t_consign_order as co', 'ocb.waybill_no', '=', 'co.waybill_no')
			->leftjoin('t_warehouse_info as wi', 'wi.warehouse_code', '=', 'co.warehouse_code')
			->leftjoin('t_order_box as ob', 'ob.box_number', '=', 'ocb.box_number')
			->leftjoin('t_order_line_info as oli', 'oli.sm_order', '=', 'ob.sm_order')
			->whereIn('co.warehouse_code', $warehouseCode)
			->orderBy('ocb.create_time', 'desc')
			->groupBy('ocb.box_number');

		$this->setWhereBetween($model, $params, 'co.created', 'create_time_s', 'create_time_e');
		$condition[] = $this->buildPara($params, 'ocb.box_number', 'like');
		$condition[] = $this->buildPara($params, 'wi.warehouse_code', '=');

		foreach ($condition as $ke => $val) {
			if (empty($val)) {
				unset($condition[$ke]);
			}
		}

		if (empty($condition) && !isset($params['create_time_s']) && !isset($params['create_time_e']) && empty($params['create_time_s']) && empty($params['create_time_e'])) {
			$model->where('ocb.id', -1);
		}

		return $this->getList($model, $condition, $params);
	}

	//获取分仓收货单信息
	public function getDispenseList($params = []) {
		$userInfo = User::getUserDetail($params['user_id']);
		$orgId = $userInfo->org_node;
		$warehouseCode = Warehouse::getWarehouseByOrgId($orgId);

		hOpenDBLog();

		//获取串货
		$ab_normal = DB::table('t_box_abnormal as ab')
			->select(
				'related_order as order_number',
				DB::raw("'' as seal_number"),
				DB::raw("'' as warehouse_name"),
				DB::raw("'' as warehouse_code"),
				DB::raw("'' as plate_number"),
				DB::raw("'' as contact_name"),
				DB::raw('"0" as should_count'),
				DB::raw('"0" AS finish_count'),
				DB::raw("'' as revice_time"),
				DB::raw("COUNT(box_number) as abnormal_count" ),
				DB::raw("'0' as order_type")
			)->where('scan_type', 'TRANSREGIONAL')
			->groupBy('related_order');

		//获取收货数据
		$receiveInfo = DB::table('t_dispense_order as do')
			->select(
				'do.order_number',
				'do.seal_number',
				'wi.warehouse_name',
				'wi.warehouse_code',
				'do.plate_number',
				'do.contact_name',
				DB::raw('COUNT(DISTINCT `dod`.goods_no) AS should_count'),
				DB::raw('COUNT(
					DISTINCT CASE
					WHEN dod.scan_status = 1 THEN
						dod.goods_no
					ELSE
						NULL
					END
				) AS finish_count'),
				DB::raw('LEFT (`do`.revice_time, 10) AS revice_time'),
				DB::raw("'0' as abnormal_count" ),
				'do.order_type'
			)->leftJoin('t_dispense_order_detail as dod', 'dod.order_number', '=', 'do.order_number')
			->leftJoin('t_warehouse_info as wi', 'wi.warehouse_code', '=', 'do.destination_code')
			->groupBy('do.order_number')
			->unionAll($ab_normal)
			->get();
		$querySql = hGetDBLogStr();

		$model = DB::table(DB::raw("($querySql) as a"))
			->select(
				'order_number',
				'seal_number',
				'warehouse_name',
				'plate_number',
				'contact_name',
				DB::raw('SUM(should_count) as should_count'),
				DB::raw('SUM(finish_count) as finish_count'),
				DB::raw('SUM(abnormal_count) as abnormal_count'),
				'revice_time',
				'order_type'
			)->groupBy('order_number')
			->orderBy('revice_time' ,'desc');

		$condition[] = $this->buildPara($params, 'order_number', 'like');
		$condition[] = $this->buildPara($params, 'plate_number', 'like');
		$condition[] = $this->buildPara($params, 'warehouse_code', 'like');
		$this->setWhereBetween($model, $params, 'revice_time', 'create_time_s', 'create_time_e');
		return $this->getList($model, $condition, $params);
	}

	//分仓收货单详情
	public function getDispenseDetail($order_number, $params = []) {

		$order_type = DB::table('t_dispense_order')->where('order_number', $order_number)->value('order_type');

		//总仓漏扫单
		if ($order_type == 1) {
			$model = DB::table('t_dispense_order_detail')
				->select('goods_no', DB::raw('IF(scan_status=1, "已扫", "未扫") as scan_status'), 'scan_time', 'scan_user', DB::raw('IF(1=1, "总仓漏扫", "") as scan_type'))
				->where('order_number', $order_number);

			if (isset($params['box_number']) && $params['box_number'] != '') {
				$model->where('goods_no', 'like', "%{$params['box_number']}%");
			}

			if (isset($params['scan_type']) && $params['scan_type'] != 'COLLECT_LEAKAGE') {
				$model->where('id', '=', -1);
			}
			$this->setWhereEq($model, $params, 'scan_status');

			$box_detail = $model->get()->toArray();
			return object_to_array($box_detail);
		}

		$box_model = DB::table('t_dispense_order_detail')
			->select('goods_no', 'scan_status', 'scan_time', DB::raw("'scan_type'"), 'scan_user')
			->where('order_number', $order_number);

		if (isset($params['box_number']) && $params['box_number'] != '') {
			$box_model->where('goods_no', 'like', "%{$params['box_number']}%");
		}

		if (isset($params['scan_type']) && $params['scan_type'] != '') {
			$box_model->where('id', '=', -1);
		}

		$this->setWhereEq($box_model, $params, 'scan_status');

		$ab_model = DB::table('t_box_abnormal as ab')
			->select(
				'box_number as goods_no',
				DB::raw("'scan_status'"),
				'ab.scan_time',
				'scan_type',
				'scan_user'
			)
			->where('ab.related_order', '=', $order_number)
			->unionAll($box_model)
			->whereIn('ab.scan_type', ['TRANSREGIONAL']);

		if (isset($params['scan_status']) && $params['scan_status'] == '0') {
			$ab_model->where('id', '=', -1);
		}

		$this->setWhereEq($ab_model, $params, 'scan_type');
		$this->setWhereLike($ab_model, $params, 'box_number');
			
		$box_detail = $ab_model->get()->toArray();
		$box_detail = object_to_array($box_detail);

		foreach ($box_detail as $key => $box_val) {
			$box_detail[$key]['scan_type'] = '';
			if ($box_val['scan_status'] == 'scan_status') {
				$box_detail[$key]['scan_status'] = '已扫';
				checkLogic(isset(self::SCAN_VALUE[$box_val['scan_type']]) ,self::SCAN_VALUE[$box_val['scan_type']]. '未知的异常类型');
				$box_detail[$key]['scan_type'] = self::SCAN_VALUE[$box_val['scan_type']];
			} else {
				checkLogic(isset(self::SCAN_STATUS[$box_val['scan_status']]), $box_val['scan_status'] . '未知的状态类型');
				$box_detail[$key]['scan_status'] = self::SCAN_STATUS[$box_val['scan_status']];
			}
		}

		return $box_detail;
	}

	//分仓发货列表
	public function getConsignList($params) {
		$model = DB::Table('t_consign_order as co')
			->select('co.waybill_no', 'oli.line_code', 'wi.warehouse_name', 'c.plate_number', 'co.driver_name', 'co.contact', 'co.box_count', 'co.load_time')
			->leftjoin('t_warehouse_info as wi', 'co.warehouse_code', '=', 'wi.warehouse_code')
			->leftjoin('t_car as c', 'co.car_id', '=', 'c.id')
			->leftjoin('t_consign_order_consign_box as cob', 'cob.waybill_no', '=', 'co.waybill_no')
			->leftjoin('t_order_line_info as oli', 'oli.sm_order', '=', 'cob.out_order_no')
			->orderBy('co.load_time', 'desc')
			->groupBy('co.waybill_no');

		//set where
		$condition[] = $this->buildPara($params, 'oli.line_code', 'like');
		$condition[] = $this->buildPara($params, 'wi.warehouse_code', '=');
		$condition[] = $this->buildPara($params, 'co.driver_name', 'like');
		$condition[] = $this->buildPara($params, 'c.plate_number', 'like');
		$condition[] = $this->buildPara($params, 'co.waybill_no', 'like');
		$this->setWhereBetween($model, $params, 'co.load_time', 'create_time_s', 'create_time_e');

		return $this->getList($model, $condition, $params);
	}

	//分仓发货详情
	public function getConsignDetail($waybill_no, $params) {
		// 获取分仓发货信息
		$waybill_model = DB::table('t_consign_order_consign_box')
			->select('box_number', 'box_status', 'scan_time' ,'scan_user')
			->where('waybill_no', $waybill_no);

		$ab_model = DB::table('t_box_abnormal');

		$this->setWhereLike($waybill_model, $params, 'box_number');
		$this->setWhereEq($waybill_model, $params, 'box_status');

		//搜索异常时 直接返回
		if (isset($params['scan_type']) && !empty($params['scan_type'])) {
			$ab_normal = $ab_model->select('box_number', 'scan_type', 'scan_time')->where([['scan_type', $params['scan_type']], ['related_order', $params['waybill_no']]])->get()->toArray();
			foreach ($ab_normal as $k => $ab_val) {
				checkLogic(isset(self::SCAN_VALUE[$ab_val->scan_type]) . '未知的异常类型');
				$ab_normal[$k]->box_normal = self::SCAN_VALUE[$ab_val->scan_type];
				$ab_normal[$k]->box_status = self::SCAN_STATUS[1];
			}
			return $ab_normal;
		}

		$waybill_info = $waybill_model->get()->toArray();

		checkLogic(!empty($waybill_info), '派车信息为空!');

		//获取异常
		$ab_normal = $ab_model->where('related_order', $waybill_no)->pluck('scan_type', 'box_number')->toArray();

		foreach ($waybill_info as $key => $val) {
			checkLogic(isset(self::SCAN_STATUS[$val->box_status]), $val->box_status . '未知的状态类型');
			$waybill_info[$key]->box_normal = '';
			$waybill_info[$key]->box_status = self::SCAN_STATUS[$val->box_status];
			if (array_key_exists($val->box_number, $ab_normal)) {
				checkLogic(isset(self::SCAN_VALUE[$ab_normal[$val->box_number]]), $ab_normal[$val->box_number] . '未知的异常类型');
				$waybill_info[$key]->box_normal = self::SCAN_VALUE[$ab_normal[$val->box_number]];
			}
		}

		return $waybill_info;
	}

	//分仓运输统计
	public function getStatisticalInfo($params = []) {
		$condition = [];
		if (isset($params['warehouse_name']) && !empty($params['warehouse_name'])) {
			$condition[] = ['wi.warehouse_name', 'like', '%' . $params['warehouse_name'] . '%'];
		}
		if (isset($params['create_time_s']) && !empty($params['create_time_s'])) {
			$condition[] = ['a.dates', '>=', $params['create_time_s']];
		}
		if (isset($params['create_time_e']) && !empty($params['create_time_e'])) {
			$condition[] = ['a.dates', '<=', $params['create_time_e']];
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

		$model = DB::table(DB::raw("($querySql) as a"))
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
			)->groupBy(
				'dates',
				'wi.warehouse_code'
			)
			->orderBy('dates', 'desc');
		return $this->getList($model, $condition, $params);
	}

	//运输统计分仓收货详情
	public function getDispenseTransportDetail($warehouse_code, $date, $params) {
		$model = DB::table('t_dispense_order_detail as dod')
			->leftjoin('t_dispense_order as do', 'do.order_number', '=', 'dod.order_number')
			->select(
				'goods_no',
				'scan_status',
				'do.revice_time',
				'do.order_number',
				'dod.scan_user',
				DB::raw('if(do.order_type=1, "总仓漏扫", "") as box_normal')
			)
			->where('destination_code', $warehouse_code)
			->whereDate('do.revice_time', $date);

		$this->setWhereLike($model, $params, 'dod.goods_no');
		$this->setWhereLike($model, $params, 'dod.scan_status');

		//获取异常
		$ab_model = DB::table('t_box_abnormal')
				->select(
					'box_number as goods_no',
					'scan_time as revice_time',
					'related_order as order_number',
					'scan_user'
				)->where('warehouse_code', $warehouse_code)
				->where('scan_type', 'TRANSREGIONAL')
				->whereDate('scan_time', $date);

		//搜索
		if (isset($params['goods_no']) && $params['goods_no'] != '') {
			$ab_model->where('box_number', $params['goods_no']);
		}

		if (isset($params['scan_type']) && $params['scan_type'] != '') {
			if ($params['scan_type'] == 'COLLECT_LEAKAGE') {
				$ab_model->where('box_number', '!@#$%^&');
				$model->where('order_type', '1');
			} else {
				$model->where('goods_no', '!@#$%%$##');
			}
		}
		


		if (isset($params['scan_status']) && $params['scan_status'] == 0) {
			$ab_model->where('scan_type', '!@#$%^&');
		}

		if (isset($params['goods_no']) && $params['goods_no'] != '') {
			$ab_model->where('box_number', $params['goods_no']);
		}

		$dispense_info = $model->get()->toArray();
		$ab_normal = $ab_model->get()->toArray();

		if (!is_null($dispense_info)) {
			foreach ($dispense_info as $dis_key => &$dis_val) {
				$dis_val->scan_status = $dis_val->scan_status == 1 ? '已扫' : '未扫';
			}
		}

		if (!is_null($ab_normal)) {
			foreach ($ab_normal as $ab_key => $ab_val) {
				$ab_val->box_normal = '串货';
				$ab_val->scan_status = '已扫';
				$dispense_info[] = $ab_val;
			}
		}
		return $dispense_info;
	}

	//运输统计分仓发货
	public function getConsignTransportDetail($warehouse_code, $date, $params) {
		$consign_model = DB::table('t_consign_order_consign_box as cocb')
			->leftjoin('t_consign_order as co', 'co.waybill_no', '=', 'cocb.waybill_no')
			->leftjoin('t_order_line_info as cli', 'cli.sm_order', '=', 'cocb.out_order_no')
			->leftjoin('t_car as c', 'c.id', '=', 'co.car_id')
			->select(
				'cli.line_code',
				'c.plate_number',
				'co.driver_name',
				'cocb.box_number',
				'cocb.box_status',
				'cocb.scan_time',
				'cocb.scan_user',
				'co.waybill_no'
			)
			->where('co.warehouse_code', $warehouse_code)
			->whereDate('co.load_time', $date);
		//setWhere
		$this->setWhereLike($consign_model, $params, 'cli.line_code');
		$this->setWhereLike($consign_model, $params, 'c.plate_number');
		$this->setWhereLike($consign_model, $params, 'co.driver_name');
		$this->setWhereLike($consign_model, $params, 'cocb.box_number');
		$this->setWhereLike($consign_model, $params, 'cocb.box_status');

		$consign_info = $consign_model->get()->toArray();

		$waybill_no_arr = array_column($consign_info, 'waybill_no');

		//获取异常
		$ab_model = DB::table('t_box_abnormal')->whereIn('related_order', $waybill_no_arr);

		//setWhere
		$this->setWhereLike($ab_model, $params, 'scan_type');
		$ab_info = $ab_model->pluck('scan_type', 'box_number')->toArray();

		//异常单
		$ab_normal = [];

		foreach ($consign_info as $k => $con_val) {
			$consign_info[$k]->box_normal = '';
			checkLogic(isset(self::SCAN_STATUS[$con_val->box_status]), $con_val->box_status . '未知的状态类型');
			$consign_info[$k]->box_status = self::SCAN_STATUS[$con_val->box_status];
			if (array_key_exists($con_val->box_number, $ab_info)) {
				checkLogic(isset(self::SCAN_VALUE[$ab_info[$con_val->box_number]]), $ab_info[$con_val->box_number] . '未知的异常类型');
				$consign_info[$k]->box_normal = self::SCAN_VALUE[$ab_info[$con_val->box_number]];
				$ab_normal[] = $consign_info[$k];
			}
		}

		if (isset($params['scan_type']) && !empty($params['scan_type'])) {
			return $ab_normal;
		}

		return $consign_info;
	}

	//分仓司机交接表
	public function consignDirverList($params) {
		$list = DB::table('t_consign_order as co')
			->select(
				'wi.warehouse_name',
				'co.created',
				'co.waybill_no',
				'co.driver_name',
				DB::raw('GROUP_CONCAT(cocb.scan_user) as scan_user'),
				DB::raw('COUNT(DISTINCT cocb.out_order_no) as should_order'),
				DB::raw('COUNT(
					DISTINCT CASE
					WHEN cocb.box_status = 1 THEN
						cocb.out_order_no
					ELSE
						NULL
					END
				) AS real_order'),
				DB::raw('COUNT(DISTINCT cocb.box_number) as should_box'),
				DB::raw('COUNT(
					DISTINCT CASE
					WHEN cocb.box_status = 1 THEN
						cocb.box_number
					ELSE
						NULL
					END
				) AS real_box')
			)
			->leftjoin('t_consign_order_consign_box as cocb', 'co.waybill_no', '=', 'cocb.waybill_no')
			->leftjoin('t_warehouse_info as wi', 'wi.warehouse_code', '=', 'co.warehouse_code')
			->orderBy('co.id', 'desc')
			->groupBy('co.waybill_no');
		//set where
		$condition[] = $this->buildPara($params, 'co.waybill_no', 'like');
		$condition[] = $this->buildPara($params, 'cocd.box_number', 'like');
		$condition[] = $this->buildPara($params, 'wi.warehouse_code', '=');
		return $this->getList($list, $condition, $params);
	}

    /**
     * 根据用户获取最近7天的装车单
     * @param int $user_id
     * @param array $where
     * @param int $type
     * @return array|\Illuminate\Database\Query\Builder
     * @throws \App\Exceptions\KnownLogicException
     */
    public function getWaybillList($user_id = 0,$where = [],$type = 1){
        // 获取当前登录用户的详细信息
        $userInfo = User::getUserDetail($user_id);
        checkLogic($user_id && $userInfo, '找不到该用户');
        checkLogic(in_array('DISTRIBUTION_MANAGER',$userInfo->role_info['role_code']), '当前用户不是分仓仓管');
        $waybill_data = DB::table('t_consign_order AS co')
            ->leftJoin('t_warehouse_info AS wi',function($join){
                $join->on('wi.warehouse_code','=','co.warehouse_code')
                    ->where('wi.in_using','=',1);
            })
            ->leftjoin('t_car as c', 'c.id', '=', 'co.car_id')
            ->select('co.waybill_no','co.driver_name','co.created', 'co.waybill_status as waybill_code', 'co.cash_money', 'c.car_name as driver_name', 'c.plate_number',
                DB::raw("CASE WHEN waybill_status = 1 THEN '新建' WHEN waybill_status = 2 THEN '装车中' 
                    WHEN waybill_status = 3 THEN '装车完成' WHEN waybill_status = 4 THEN '已交货' 
                    WHEN waybill_status = 5 THEN '申请验车' WHEN waybill_status = 6 THEN '验车中' 
                    WHEN waybill_status = 7 THEN '异常处理中' WHEN waybill_status = 8 THEN '异常完成' 
                    WHEN waybill_status = 9 THEN '验车完成' WHEN waybill_status = 10 THEN '已入库' 
                    WHEN waybill_status = 11 THEN '已完成' WHEN waybill_status = 12 THEN '已发车' ELSE '' END AS waybill_status")
                )
            ->whereIn('wi.org_id',$userInfo->org_node)
            ->where($where)
            ->where('co.waybill_status','<>',-1);
        if($type == 1){
            $waybill_data = $waybill_data->whereIn('co.waybill_status',[5,6,7,8,9]);
        }
        $waybill_data = $waybill_data->orderByDesc('co.created')->get()->toArray();
        return $waybill_data;
    }
    /**
     * 获取验货单详情
     * @param string $waybill_no
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     * @throws \App\Exceptions\KnownLogicException
     *
     */
    public function getConsignInfo($waybill_no = '',$user_id = 0){
        // 获取当前登录用户的详细信息
        $userInfo = User::getUserDetail($user_id);
        checkLogic($user_id && $userInfo, '找不到该用户');
        checkLogic($user_id == 1 || in_array('DISTRIBUTION_MANAGER',$userInfo->role_info['role_code']), '当前用户不是分仓仓管');
        //拥有的仓库权限
        $checkWare = Warehouse::getWarehouseByOrgId($userInfo->org_node);
        $consign_info = DB::table('t_consign_order AS tco')
            ->leftJoin('t_consign_order_check AS tcoc','tcoc.waybill_no','=','tco.waybill_no')
            ->select('tco.waybill_no','tco.waybill_status','tco.driver_name','tco.real_money','tco.cash_money','tco.executed_count','tco.remark', 'tco.apply_time',
                DB::raw("(SELECT plate_number FROM t_car WHERE id = tco.car_id limit 1) as plate_number"),
                DB::raw("COUNT(CASE WHEN tcoc.check_type = 'AGAIN' THEN 1 ELSE NULL END) as againCount"),
                DB::raw("COUNT(CASE WHEN tcoc.check_type = 'AGAIN' AND is_check = 1 THEN 1 ELSE NULL END) as againCheckCount"),
                DB::raw("(SELECT count(DISTINCT related_no) FROM t_consign_order_check WHERE waybill_no = '{$waybill_no}' AND check_type='ALL_REJECT') as allRejectCount"),
                DB::raw("COUNT(CASE WHEN tcoc.check_type = 'ALL_REJECT' AND is_check = 1 THEN 1 ELSE NULL END) as allRejectCheckCount"),
                DB::raw("COUNT(CASE WHEN tcoc.check_type = 'PART_REJECT' THEN 1 ELSE NULL END) as partRejectCount"),
                DB::raw("COUNT(CASE WHEN tcoc.check_type = 'PART_REJECT' AND is_check = 1 THEN 1 ELSE NULL END) as partRejectCheckCount"),
                DB::raw("COUNT(CASE WHEN tcoc.check_type = 'AFTER' THEN 1 ELSE NULL END) as afterCount"),
                DB::raw("COUNT(CASE WHEN tcoc.check_type = 'AFTER' AND is_check = 1 THEN 1 ELSE NULL END) as afterCheckCount"),
                DB::raw("SUM(CASE WHEN tcoc.check_type = 'AGAIN' THEN tcoc.plan_num-tcoc.real_num ELSE 0 END) as againNum"),
                DB::raw("SUM(CASE WHEN tcoc.check_type = 'AGAIN' AND is_check = 1 THEN tcoc.plan_num-tcoc.real_num ELSE 0 END) as againCheckNum"),
                DB::raw("(SELECT count(DISTINCT box_number) FROM t_consign_order_check WHERE waybill_no = '{$waybill_no}' AND check_type='ALL_REJECT') as allRejectNum"),
                DB::raw("SUM(CASE WHEN tcoc.check_type = 'ALL_REJECT' AND is_check = 1 THEN tcoc.plan_num-tcoc.real_num ELSE 0 END) as allRejectCheckNum"),
                DB::raw("SUM(CASE WHEN tcoc.check_type = 'PART_REJECT' THEN tcoc.plan_num ELSE 0 END) as partRejectNum"),
                DB::raw("SUM(CASE WHEN tcoc.check_type = 'PART_REJECT' AND is_check = 1 THEN tcoc.plan_num-tcoc.real_num ELSE 0 END) as partRejectCheckNum"),
                DB::raw("SUM(CASE WHEN tcoc.check_type = 'AFTER' THEN tcoc.plan_num ELSE 0 END) as afterNum"),
                DB::raw("SUM(CASE WHEN tcoc.check_type = 'AFTER' AND is_check = 1 THEN tcoc.real_num ELSE 0 END) as afterCheckNum")
            )
            ->where('tco.waybill_no','=',$waybill_no)
            ->whereIn('tco.warehouse_code',$checkWare)
            ->groupBy('tco.waybill_no')
            ->first();
        return $consign_info;
    }

    /**
     * 获取验车信息
     */
    public function getConsignCheck($waybill_no){
    	$consign_info = DB::table('t_consign_order_check')
    			->where('waybill_no', $waybill_no)
    			->get()
    			->toArray();
    	return $consign_info;
    }

    /**
     * 获取装车单验货列表
     * @param string $waybill_no
     * @param string $check_type
     * @param int $user_id
     * @return array
     * @throws \App\Exceptions\KnownLogicException
     */
    public function getCheckList($waybill_no = '',$check_type = 'AGAIN',$user_id = 0){
        // 获取当前登录用户的详细信息
        $userInfo = User::getUserDetail($user_id);
        checkLogic($user_id && $userInfo, '找不到该用户');
        checkLogic($user_id == 1 || in_array('DISTRIBUTION_MANAGER',$userInfo->role_info['role_code']), '当前用户不是分仓仓管');
        //拥有的仓库权限
        $checkWare = Warehouse::getWarehouseByOrgId($userInfo->org_node);
        $data = DB::table('t_consign_order_check AS tcoc')
            ->leftJoin('t_consign_order AS tco','tcoc.waybill_no','=','tco.waybill_no')
            ->select('tcoc.check_type','tcoc.delivery_no','tcoc.related_no','tcoc.box_number','tcoc.split_sku', 'tcoc.check_user', 'tcoc.check_time',
                'tcoc.plan_num','tcoc.real_num','tcoc.is_again_time','tcoc.is_check')
            ->whereIn('tco.warehouse_code',$checkWare)
            ->where('tco.waybill_no','=',$waybill_no)
            ->where('tcoc.check_type','=',$check_type)
            ->get()->toArray();
        return $data;
    }
    /**
     * 查看装车单商品信息
     * @param string $delivery_no
     * @param string $box_number
     * @return array
     */
    public function getProductByBox($delivery_no = '',$box_number = ''){
        $data = DB::table('t_consign_order_check AS tcoc')
            ->leftJoin('t_order_box_detail AS tobd',function ($join){
                $join->on('tobd.order_number','=','tcoc.related_no')
                    ->on('tobd.box_number','=','tcoc.box_number');
            })
            ->where('tcoc.delivery_no','=',$delivery_no)
            ->where('tcoc.box_number','=',$box_number)
            ->select('tcoc.delivery_no','tcoc.box_number','tcoc.is_check','tcoc.check_user','tcoc.check_time','tobd.product_code','tobd.quantity')
            ->groupBy('tobd.product_code')
            ->get()->toArray();
        return object_to_array($data);
    }
    /**
     * 查看装车单商品信息
     * @param string $delivery_no
     * @param string $product_code
     * @return array
     */
    public function getProductInfo($delivery_no = '',$product_code = ''){
        $data = DB::table('t_consign_order_check AS tcoc')
            ->leftJoin('t_order_box_detail AS tobd',function ($join){
                $join->on('tobd.order_number','=','tcoc.related_no')
                    ->on('tobd.box_number','=','tcoc.box_number');
            })
            ->where('tcoc.delivery_no','=',$delivery_no)
            ->where('tobd.product_code','=',$product_code)
            ->select('tcoc.delivery_no','tcoc.box_number','tcoc.is_check','tcoc.check_user','tcoc.check_time','tobd.product_code','tobd.quantity')
            ->groupBy('tobd.product_code')
            ->get()->toArray();
        return object_to_array($data);
    }
    /**
     * 查询商品验货信息
     * @param string $delivery_no
     * @param string $aftersales_no
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function getCheckInfo($delivery_no = '',$aftersales_no = ''){
        $data = DB::table('t_consign_order_check')
            ->where('delivery_no','=',$delivery_no)
            ->where('related_no','=',$aftersales_no)
            ->select('is_check','check_user','check_time')
            ->first();
        return $data;
    }

    // 获取装车单信息 以及 车辆信息
    public function getCarInfoByWaybill($waybill_no){
    	return DB::table('t_consign_order as to')
    		->select(
    			'to.*',
    			'c.car_name',
    			'c.plate_number',
    			DB::raw('IFNULL(SUM(coc.volume), 0) as volume'),
    			DB::raw('IFNULL(SUM(coc.weight), 0) as weight')
    		)
    		->leftJoin('t_car as c', 'c.id', '=', 'to.car_id')
    		->leftJoin('t_consign_order_item as coc', 'coc.waybill_no', '=', 'to.waybill_no')
    		->where('to.waybill_no', $waybill_no)
    		->first();
    }

    //同步排车信息
    public function saveConsignCheck($waybill_info){

    	if (!isset($waybill_info['waybill_no'])) {
    		return '装车单号未传入!';
    	}
    	if (!isset($waybill_info['real_money'])) {
    		return '实际金额未传入!';
    	}
    	if (!isset($waybill_info['cash_money'])) {
    		return '现金收款未传入!';
    	}
    	// if (!isset($waybill_info['executed_count'])) {
    	// 	return '已执行数未传入!';
    	// }
    	if (!isset($waybill_info['back_consign'])) {
    		return '验货详情未传入!';
    	}

    	$result = DB::table('t_consign_order_check')->where('waybill_no', $waybill_info['waybill_no'])->first();
    	if (!is_null($result)) {
    		return true;
    	}

    	$back_consign = $waybill_info['back_consign'];

    	unset($waybill_info['back_consign']);
    	DB::beginTransaction();
		try {
			DB::table('t_consign_order')
				->where('waybill_no', $waybill_info['waybill_no'])
				->update($waybill_info);
			if (!empty($back_consign)) {
				DB::table('t_consign_order_check')->insert($back_consign);
			}

			DB::commit();
			return true;
		} catch (\Exception $ex) {
			DB::rollBack();
			throw $ex;
		}
    }

    /**
     * 快速验车
     * @DateTime  2018-10-22
     * @param     [string]      $waybill_no [装车单号]
     * @param     [string]      $user_id    [用户id]
     * @param     [string]      $user_name  [用户名]
     * @param     [string]      $check_port [触发验车端]
     * @return    [bool]                  [description]
     */
    public function checkBackWaybillAll($waybill_no, $user_name, $check_port = 'TMS'){
    	$waybill_status = DB::table('t_consign_order')->where('waybill_no', $waybill_no)->value('waybill_status');
    	if (!in_array($waybill_status, [5, 6, 7])) {
    		return '装车单状态不允许验车!';
    	}

    	$check_waybill = DB::table('t_consign_order_check')->where('waybill_no', $waybill_no)->where('check_way', 1)->count();
    	if ($check_waybill > 0) {
    		return '装车单正在进行扫描验车，不允许快速验车!';
    	}

    	$waybill_info['check_time'] = hDate();
		$waybill_info['waybill_status'] = 9;
    	if ($check_port == 'TMS') {
    		$waybill_info['waybill_status'] = 6;
    	}

		DB::beginTransaction();
		try {
    		DB::table('t_consign_order')->where('waybill_no', $waybill_no)->update($waybill_info);
    		DB::table('t_consign_order_check')->where('waybill_no', $waybill_no)->update([
    			'real_num' => DB::raw('plan_num'),
    			'is_check'=>1,
    			'check_user'=>$user_name,
    			'check_way'=>2,
    			'check_time'=>hDate(),
    			'updated_at'=>hDate()
    		]);
    		DB::commit();
			return true;
		} catch (\Exception $ex) {
			DB::rollBack();
			throw $ex;
		}
    }

    /**
     * 获取验货扫描信息 扫描验车
     * @param string $waybill_no
     * @param string $verify_field
     * @param string $verify_code
     * @param integer $user_id
     * @return array
     * @throws \App\Exceptions\KnownLogicException
     */
    public function getVerifyProduct($waybill_no = '',$verify_field = '',$verify_code = '',$user_id = 0, $verify_num = 0){
        // 获取当前登录用户的详细信息
        $userInfo = User::getUserDetail($user_id);
        checkLogic($user_id && !empty($userInfo), '找不到该用户');
        checkLogic(in_array('DISTRIBUTION_MANAGER',$userInfo->role_info['role_code']), '当前用户不是分仓仓管');
        //拥有的仓库权限
        $checkWare = Warehouse::getWarehouseByOrgId($userInfo->org_node);

        $model = DB::table('t_consign_order_check')
            ->select('id','waybill_no','check_type','delivery_no','related_no','plan_num','real_num','is_again_time','box_number','split_sku')
            ->where('waybill_no','=', $waybill_no)
            ->where('plan_num','>', DB::raw('real_num'))
            ->where($verify_field, '=', $verify_code);

        //只有拆单拒收才能扫描sku
        if ($verify_field == 'split_sku') {
        	$model->where('check_type', 'PART_REJECT');
        }
       
        $data = $model->first();
        if (empty($data)) {
	        $consign_model = DB::table('t_consign_order_check')
	            ->select('id','waybill_no','check_type','delivery_no','related_no','plan_num','real_num','is_again_time','box_number','split_sku')
	            ->where('waybill_no','=', $waybill_no)
	            ->where('plan_num','=', DB::raw('real_num'))
	            ->where($verify_field, '=', $verify_code);

	        //只有拆单拒收才能扫描sku
	        if ($verify_field == 'split_sku') {
	        	$consign_model->where('check_type', 'PART_REJECT');
	        }
	        $consign_data = $consign_model->first();
	        return object_to_array($consign_data);
        }

        //验证装车单状态
        $waybill_info = DB::table('t_consign_order')
            ->where('waybill_no', $waybill_no)
            ->whereIn('waybill_status',[5,6,7])
            ->whereIn('warehouse_code',$checkWare)
            ->first();

        checkLogic(!empty($data) && !empty($waybill_info), '没有权限操作');

        //校验
        if ($verify_field == 'split_sku' || $verify_field == 'related_no') {
        	checkLogic($verify_num  != 0, '验货数量有误!');
        	$verify_num = $data->real_num + $verify_num;
        	checkLogic($verify_num <= $data->plan_num, '验货数量溢出!');
        }
            $userInfo = User::getUserInfo($user_id);
            //扫码验货
            $update_data = [
                'real_num' => $verify_field == 'box_number' ? $data->plan_num : $verify_num,   //验货数量
                'is_check' => 1,                        //已验车
                'check_user' => $userInfo->login_name,  //验车人
                'check_way' => 1,                       //扫描验车
                'check_time' => date('Y-m-d H:i:s'),    //验车时间
            ];
            DB::beginTransaction();
            try {
                DB::table('t_consign_order_check')
                    ->where('id','=',$data->id)
                    ->update($update_data);
                OperationLog::saveLog(
                    $userInfo->org_id,
                    $userInfo->login_name,
                    'update',
                    'id:'.$data->id.'扫描验车',
                    2
                );
                DB::commit();
            }catch (\Exception $ex){
                DB::rollBack();
                checkLogic(0,$ex->getMessage());
            }

            $consignOrder = new ConsignOrder();
            $result = $consignOrder->saveExecutedCount($waybill_no);
	        if (!$result) {
	            return hApiError('已执行验车数更新失败!');
	        }

        return object_to_array($data);
    }
    /**
     * 获取已验货商品信息
     * @param string $waybill_no
     * @return array
     */
    public function getVerifyList($waybill_no = ''){
        $data = DB::table('t_consign_order_check')
            ->select('waybill_no','check_type','related_no','plan_num','real_num','box_number','split_sku')
            ->where('waybill_no','=',$waybill_no)
            ->where('is_check','=',1)
            ->orderBy('check_time', 'desc')
            ->get()->toArray();
        return object_to_array($data);
    }

    /**
     * 核实验车数据是否存在异常
     * @param string $waybill_no
     * @return int
     */
    public function checkConsignData($waybill_no){
    	$need_num = DB::table('t_consign_order_check')
    		->select(
    			DB::raw('SUM(plan_num) - SUM(real_num) as need_num')
    		)
    		->whereRaw('plan_num <> real_num')
    		->where('waybill_no', $waybill_no)
    		->first();

    	if (!is_null($need_num->need_num)) {
    		try {
    			DB::table('t_consign_order')->where('waybill_no', $waybill_no)->update(['waybill_status' => 7]);
    			return '装车单:'.$waybill_no.',还有'.$need_num->need_num.'件数未完成,请去PC处理!';
    		} catch (\Exception $e) {
    			throw $e;
    		}
    	}
    	return true;
    }

    /**
     * 验车完成
     * @param string $waybill_no 		装车单单号
     * @param string $waybill_status    装车单状态
     * @param string $user_id           操作id
     * @param string $user_name         操作人
     * @return int
     */
    public function checkConsignFinish($waybill_no, $waybill_status, $user_id, $user_name){

    	$userInfo = User::getUserDetail($user_id);

       	// 异常处理则异常完成
    	if ($waybill_status == 7) {
    		$waybill_status = 8;
    	} else {
    		$waybill_status = 9;
    	}

    	// 获取部分拒收的sku
    	$part_sku = DB::table('t_consign_order_check')
    		->select(DB::raw('SUM(real_num) as real_num'), 'split_sku')
    		->where('waybill_no', $waybill_no)
    		->where('check_type', 'PART_REJECT')
    		->where('real_num', '<>', 0)
    		->groupBy('split_sku')
    		->get()
    		->toArray();

    	// 获取整单拒收的sku
    	$all_sku = DB::table('t_consign_order_check as coc')
    		->leftJoin('t_order_box_detail as obd', 'coc.box_number', '=', 'obd.box_number')
    		->select(DB::raw('SUM(obd.quantity) as real_num'), 'obd.product_code as split_sku')
    		->where('waybill_no', $waybill_no)
    		->where('check_type', 'ALL_REJECT')
    		->groupBy('obd.product_code')
    		->where('coc.real_num', '<>', 0)
    		->get()
    		->toArray();
    	$part_sku = object_to_array($part_sku);
    	$all_sku = object_to_array($all_sku);;

    	// 合并处理拒收sku
    	$part_info = [];
    	foreach ($part_sku as $item_part) {
    		$part_info[$item_part['split_sku']] = $item_part['real_num'];
    	}
    	
    	foreach ($all_sku as $k_all => $item_all) {
    		if (!isset($part_info[$item_all['split_sku']])) {
    			$part_info[$item_all['split_sku']] = $item_all['real_num'];
    		} else {
    			if (!is_null($part_info[$item_all['split_sku']])) {
    				$part_info[$item_all['split_sku']] = $item_all['real_num'] + $part_info[$item_all['split_sku']];
    			}
    		}
    	}

    	//需要出库的验货
    	$agency_after_info = DB::table('t_consign_order_check as coc')
    		->select('related_no', 'plan_num', 'real_num', 'split_sku', 'check_time')
			->where('waybill_no', $waybill_no)
			->where('related_no', 'like', 'ASAP%')
			->get()
			->toArray();
		$agency_after_info = object_to_array($agency_after_info);
		$agency_after_no = array_column($agency_after_info, 'related_no');

		//请求APS获取代理商信息
    	$as_number = implode(',', $agency_after_no);
    	$url = env('APS_API_URL','').'aftersales/internal/order/getShopByOrders?as_numbers='.$as_number;
    	$res = file_get_contents($url);
    	//api日志
        ApiRequestLog::saveLog(
            1,
            'getSupplierInfo',
            env('APS_API_URL','').'aftersales/internal/order/getShopByOrders',
            $as_number,
            json_encode($res)
        );
    	$result = json_decode($res, 1);

    	$after_agency_info = [];
    	if ($result['code'] == 200) {
    		if (!empty($result['data'])) {
    			foreach ($result['data'] as $key => $after) {
    				$after_agency_info[$key]['supplier'] = $after['name'];
    				$after_agency_info[$key]['destination_code'] = $after['warehouse_id'];
    			}
    		}
    	}

    	//构造数据
    	$check_out = [];
    	foreach ($agency_after_info as $agency_order) {
    		$check_out[] = [
    			'supplier'	=>	isset($after_agency_info[$agency_order['related_no']]['supplier']) ? $after_agency_info[$agency_order['related_no']]['supplier'] : '',
    			'warehouse_code'	=>	$userInfo->warehouse_code,
    			'destination_code'	=>	isset($after_agency_info[$agency_order['related_no']]['destination_code']) ? $after_agency_info[$agency_order['related_no']]['destination_code'] : '',
    			'status'	=>	1,
    			'related_no'	=>	$agency_order['related_no'],
    			'sku'	=>	$agency_order['split_sku'],
    			'plan_num'	=>	$agency_order['plan_num'],
    			'real_num'	=>	$agency_order['real_num'],
    			'handover_num'	=>	$agency_order['real_num']
    		];
    	}
    	
    	// 请求MP
    	$check_num = array_sum($part_info);;
    	$part_info = json_encode($part_info);
    	$data = [
    		'waybill_no'	=>	$waybill_no,
    		'product_info'	=>	$part_info,
    		'user_name'		=>	$user_name
    	];

    	$result = curl_post_erpapi('consign_order/tms_check_driver_back', $data);
    	if ($result['code'] != 200) {
    		return 'MP返回:'.$result['msg'];
    	}

    	// TMS开始验货
    	DB::beginTransaction();
        try {
            DB::table('t_consign_order')
                ->where('waybill_no', $waybill_no)
                ->update([
                	'waybill_status' =>	$waybill_status
                ]);

            //出库信息
            if (!empty($check_out)) {
            	DB::table('t_consign_order_check_out')->insert($check_out);	
            }

            OperationLog::saveLog(
                $user_id,
                $user_name,
                'update',
                $waybill_no.':完成验车',
                2
            );
            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            throw $ex;
        }

		foreach ($agency_after_no as $agency_after) {
	    	JobHelper::dispatchJob(
				SaveAgencyConsign::class, ['agency_after_no' => $agency_after]
			);
		}

    	return $check_num;
    }


    /**
     * 根据装车单单修改运货单
     * @DateTime  2018-11-12
     * @param     [type]      $waybill_no [装车单单号]
     * @param     [type]      $data       [修改的数据]
     * @return    bool
     */
    public function saveConsignDate($waybill_no, array $data){

    	$waybill = DB::table('t_consign_order')->where('waybill_no', $waybill_no)->first();
    	if (is_null($waybill)) {
    		return '装车单:'.$waybill_no.'不存在!';
    	}

    	if (in_array($waybill->waybill_status, [5, 6, 7, 8, 9])) {
    		return '装车单:'.$waybill_no.'已经是验货状态,请勿重复操作!';
    	}
    	$data['apply_time'] = hDate();
    	DB::beginTransaction();
    	try {
    		DB::table('t_consign_order')->where('waybill_no', $waybill_no)->update($data);
    	} catch (\Exception $e) {
    		DB::rollBack();
    		throw $e;
    	}

    	DB::commit();
    	return true;
    }

    /**
     * 根据装车单单修改运货单
     * @DateTime  2018-11-12
     * @param     [type]      $waybill_no [装车单单号]
     * @param     [type]      $data       [修改的数据]
     * @return    bool
     */
    public function saveConsignDateByWaybill_no($waybill_no, array $data){

    	$waybill = DB::table('t_consign_order')->where('waybill_no', $waybill_no)->first();
    	if (is_null($waybill)) {
    		return '装车单:'.$waybill_no.'不存在!';
    	}

    	DB::beginTransaction();
    	try {
    		DB::table('t_consign_order')->where('waybill_no', $waybill_no)->update($data);
    	} catch (\Exception $e) {
    		DB::rollBack();
    		throw $e;
    	}

    	DB::commit();
    	return true;
    }


    /**
     * 获取装车单列表
     * @Date 2018-11-16
     * @author zhangdahao
     * @param array $params 查询条件
     * @return array
     */
    public function checkConsignList($params){

    	$org_node = User::getUserDetail($params['user_id'])->org_node;
    	//拥有的仓库权限
        $checkWare = Warehouse::getWarehouseByOrgId($org_node);

    	$model = DB::table('t_consign_order as co')
			->leftJoin('t_car as c', 'c.id', '=', 'co.car_id')
			->select(
				'co.waybill_no',
				'c.plate_number',
			    'co.driver_name',
			    'co.apply_time',
			    'co.cash_money',
			    'co.waybill_status'
			)
			->whereIn('co.waybill_status', [5, 6, 7, 8, 9])
			->whereIn('co.warehouse_code', $checkWare)
			->orderByDesc('co.apply_time');

		//set where
		$condition[] = $this->buildPara($params, 'co.waybill_no', 'like');
		$condition[] = $this->buildPara($params, 'co.driver_name', 'like');
		$condition[] = $this->buildPara($params, 'c.plate_number', 'like');
		$condition[] = $this->buildPara($params, 'co.waybill_status', '=');
		$this->setWhereBetween($model, $params, 'co.apply_time', 'create_time_s', 'create_time_e');
		return $this->getList($model, $condition, $params);
    }

    /**
     * 执行调整验车数
     * @DateTime  2018-11-19
     * @param     [type]      $delivery_no  [运货单号]
     * @param     [type]      $check_number [验车号]
     * @param     [type]      $real_num     [实收数]
     * @param     [type]      $change_num   [改变数]
     * @return    bool
     */
    public function doConsignData($delivery_no, $check_number, $real_num, $change_num, $user_id, $user_name){

    	DB::beginTransaction();
    	try {
    		DB::table('t_consign_order_check')
    			->where([['delivery_no', $delivery_no], ])
    			->where(function($query) use ($check_number) {
			        $query->where('box_number', '=', $check_number)
			              ->orWhere('split_sku', '=', $check_number)
			              ->orWhere('related_no', '=', $check_number);
			    })
    			->update([
    				'real_num'	=>	$real_num,
    				'remark'	=>	$user_name.'将实收件数调整了'.$change_num,
    				'is_check'	=>	1
    			]);

    		OperationLog::saveLog(
                $user_id,
                $user_name,
                'update',
                $delivery_no.':调整了实收数。由原来的'.($real_num - $change_num).'改为了'.$real_num,
                2
            );

    	} catch (\Exception $e) {
    		DB::rollBack();
    		throw $e;
    	}

    	DB::commit();
    	return true;
    }

    /**
     * 获取缺失商品的列表
     * @Date 2018-11-19
     * @author zhangdahao
     * @param array $params 查询条件
     * @return array
     */
    public function loseConsignProduct($params){

    	$model = DB::table('t_consign_order_check as coc')
    			->select(
    				'co.waybill_no',
    				'coc.related_no',
    				'coc.delivery_no',
    				'plate_number',
    				'c.car_name',
    				'coc.box_number',
    				'coc.split_sku',
    				'coc.plan_num',
    				'coc.real_num',
    				'co.apply_time',
    				'coc.unit_price'
    			)
    			->leftJoin('t_consign_order as co', 'co.waybill_no', '=', 'coc.waybill_no')
    			->leftJoin('t_car as c', 'c.id', '=', 'co.car_id')
    			->where('co.waybill_status', 8);
    	if (isset($params['box_sku']) && !empty($params['box_sku'])) {
    		$model->where(function ($query) use($params) {
		        $query->where('coc.box_number', 'like', "%{$params['box_sku']}%")
		              ->orWhere('coc.split_sku', 'like', "%{$params['box_sku']}%");
		    });
    	}
    	$model->whereRaw('coc.plan_num > coc.real_num');
    	//set where
		$condition[] = $this->buildPara($params, 'co.waybill_no', 'like');
		$condition[] = $this->buildPara($params, 'coc.related_no', 'like');
		$condition[] = $this->buildPara($params, 'coc.delivery_no', 'like');
		$condition[] = $this->buildPara($params, 'c.car_name', 'like');
		$condition[] = $this->buildPara($params, 'c.plate_number', 'like');
		$this->setWhereBetween($model, $params, 'co.apply_time', 'create_time_s', 'create_time_e');

		return $this->getList($model, $condition, $params);
    }


    /**
     * 缺失商品赔付
     * @DateTime  2018-11-20
     * @param     [string]      $delivery_no  [运货单号]
     * @param     [string]      $check_number [校验码]
     * @param     [string]      $price        [赔付的金额]
     * @param     [string]      $user_id      [用户id]
     * @param     [string]      $user_name    [用户名]
     * @return    bool
     */
    public function productCompensate($delivery_no, $check_number, $price, $user_id, $user_name){
    	DB::beginTransaction();
    	try {
    		DB::table('t_consign_order_check')
    			->where([['delivery_no', $delivery_no], ])
    			->where(function($query) use ($check_number) {
			        $query->where('box_number', '=', $check_number)
			              ->orWhere('split_sku', '=', $check_number)
			              ->orWhere('related_no', '=', $check_number);
			    })
    			->update([
    				'real_num'	=>	DB::raw('plan_num'),
    				'remark'	=>	$user_name.'赔付了'.$price
    			]);

    		OperationLog::saveLog(
                $user_id,
                $user_name,
                'update',
                $delivery_no.':赔付了'.$price,
                2
            );

    	} catch (\Exception $e) {
    		DB::rollBack();
    		throw $e;
    	}

    	DB::commit();
    	return true;
    }


    /**
     * 缺失商品补货
     * @DateTime  2018-11-20
     * @param     [string]      $delivery_no  [运货单号]
     * @param     [string]      $check_number [校验码]
     * @param     [string]      $price        [赔付的金额]
     * @param     [string]      $user_id      [用户id]
     * @param     [string]      $user_name    [用户名]
     * @return    bool
     */
    public function productReplenish($waybill_no, $delivery_no, $check_number, $num, $user_id, $user_name){


    	if (substr($check_number, 0, 2) == 'RB') {
    		$product_info = DB::table('t_order_box_detail')->where('box_number', $check_number)->pluck('quantity', 'product_code')->toArray();
    	} else {
    		$product_info = [
    			$check_number	=>	$num
    		];
    	}

    	//构造数据
    	$data = [
    		'waybill_no'	=>	$waybill_no,
    		'product_info'	=>	json_encode($product_info)
    	];
    	$result = curl_post_erpapi('/consign_order/tms_product_replenish', $data);
    	if ($result['code'] != 200) {
    		return $result['msg'];
    	}

    	DB::beginTransaction();
    	try {
    		DB::table('t_consign_order_check')
    			->where([['delivery_no', $delivery_no], ])
    			->where(function($query) use ($check_number) {
			        $query->where('box_number', '=', $check_number)
			              ->orWhere('split_sku', '=', $check_number)
			              ->orWhere('related_no', '=', $check_number);
			    })
    			->update([
    				'real_num'	=>  DB::raw("real_num+($num)"),
    				'remark'	=>	$user_name.'补货，补了'.$num
    			]);

    		OperationLog::saveLog(
                $user_id,
                $user_name,
                'update',
                $delivery_no.':补货，补了'.$num,
                2
            );

    	} catch (\Exception $e) {
    		DB::rollBack();
    		throw $e;
    	}

    	DB::commit();
    	return true;
    }


    /**
     * @Author    Hybrid
     * @DateTime  2018-11-22
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     * @param     [type]      $check_type  [description]
     * @param     [type]      $delivery_no [description]
     * @param     [type]      $search_code [description]
     * @return    [type]                   [description]
     */
    public function getProductInfoDetail($check_type, $delivery_no, $search_code){

    	$waybill = DB::table('t_consign_order_check')
    	    ->where('check_type', $check_type)
    	    ->where('delivery_no', $delivery_no)
    	    ->where(function($query) use ($search_code) {
		        $query->where('box_number', '=', $search_code)
		              ->orWhere('split_sku', '=', $search_code)
		              ->orWhere('related_no', '=', $search_code);
		    })
		    ->first();
		$waybill = object_to_array($waybill);

		if(empty($waybill)){
			return  false;
		}

		if ($check_type == 'AGAIN' || $check_type == 'ALL_REJECT') {
			$product = DB::table('t_order_box_detail')->where('box_number', $search_code)->pluck('quantity', 'product_code')->toArray();
		} elseif($check_type == 'AFTER' || $check_type == 'PART_REJECT'){
			 $product = [$waybill['split_sku'] => $waybill['plan_num']];
		} else{
			return false;
		}
		$product_info = json_encode(array_keys($product));
 		$result = curl_post_erpapi('/consign_order/get_product_info', ['product_info' => $product_info, 'delivery_no'=>$delivery_no]);
        dd($result);
    	if ($result['code'] != 200) {
    		return false;
    	}

    	foreach ($result['data'] as $key => $val) {

    		$result['data'][$key]['picture'] = 'http://static.ruigushop.com'.$val['picture'];
    		$result['data'][$key]['quantity'] = $product[$val['product_code']];
    		$result['data'][$key]['price'] = sprintf("%.2f", $val['price']);
    		$result['data'][$key]['is_check'] = $waybill['is_check'];
    		$result['data'][$key]['check_user'] = $waybill['check_user'];
    		$result['data'][$key]['check_time'] = $waybill['check_time'];
            $result['data'][$key]['delivery_note'] = $val['delivery_note'];
   
    	}

    	return $result['data'];
    }


    /**
     * 获取装车单异常验货详情
     * @DateTime  2018-11-24
     * @param     [type]      $waybill_no [装车单号]
     * @return    array
     */
    public function getCheckConsignDetail($waybill_no){
    	$check_info = DB::table('t_consign_order_check')
    		->where('waybill_no', $waybill_no)
    		->get()
    		->toArray();

    	return object_to_array($check_info);
    }

    /**
     * 格式化验车数据
     * @DateTime  2018-11-26
     * @param     [type]      验车明细
     * @return    array
     */
    public function dealBackData($back_data){
    	$check_info = [
    		'all_reject'	=>	[],
    		'part_reject'	=>	[],
    		'again'	=>	[],
    		'after'	=>	[]
    	];

    	// 整单拒收
    	$all_reject_order = [];
    	$check_reject_order = [];

    	// 拆单拒收
    	$part_reject_order = [];
    	$check_part_reject_order = [];

    	// 改日送
    	$again_order = [];
    	$check_again_order = [];

    	//售后提货
    	$after_order = [];
    	$check_after_order = [];

    	$part_reject_num = [];

    	$again_num = [];
    	$after_num = [];

    	// 初始化数据
    	$waybill_data = [
    		"againCount"			=>    0,            //改日送单数
            "againCheckCount"		=>    0,            //改日送已验货单数
            "againNum"			    =>    0,            //改日送箱数
            "againCheckNum"			=>    0,            //改日送已验货箱数
            "allRejectCount"		=>    0,            //整单拒收单数
            "allRejectCheckCount"	=>    0,            //整单拒收已验货单数
            "allRejectNum"			=>    0,            //整单拒收箱数
            "allRejectCheckNum"		=>    0,            //整单拒收已验货箱数
            "partRejectCount"		=>    0,            //部分拒收单数
            "partRejectCheckCount"	=>    0,            //部分拒收已验货单数
            "partRejectNum"			=>    0,            //部分拒收件数
            "partRejectCheckNum"	=>    0,            //部分拒收已验货件数
            "afterCount"			=>    0,            //提货单数
            "afterCheckCount"		=>    0,            //提货已验货箱数
            "afterNum"				=>    0,            //提货件数
            "afterCheckNum"			=>    0             //提货已验货件数executed_count
    	];
    	
    	// 处理验车数据
    	foreach ($back_data as $key => $back_info) {
    		if (!isset($back_info['check_type'])) {
    			continue;
    		}
    		
    		$all_reject = [];
    		$part_reject = [];
    		$again = [];
    		$after = [];

    		switch ($back_info['check_type']) {
    			case 'ALL_REJECT':
	    			$all_reject = [
		    			'delivery_no'	=>	$back_info['delivery_no'],
		    			'related_no'	=>	$back_info['related_no'],
		    			'plan_num'	=>	$back_info['plan_num'],
		    			'real_num'	=>	isset($back_info['real_num']) ? $back_info['real_num'] : 0,
		    			'is_check'	=>	isset($back_info['is_check']) ? $back_info['is_check'] : 0
		    		];
    				$all_reject['check_number'] = $back_info['box_number'];
    				break;
    			case 'PART_REJECT':
    				$part_reject = [
		    			'delivery_no'	=>	$back_info['delivery_no'],
		    			'related_no'	=>	$back_info['related_no'],
		    			'plan_num'	=>	$back_info['plan_num'],
		    			'real_num'	=>	isset($back_info['real_num']) ? $back_info['real_num'] : 0,
		    			'is_check'	=>	isset($back_info['is_check']) ? $back_info['is_check'] : 0
		    		];
    				$part_reject['check_number'] = $back_info['split_sku'];
    				break;
    			case 'AGAIN':
    				$again = [
		    			'delivery_no'	=>	$back_info['delivery_no'],
		    			'related_no'	=>	$back_info['related_no'],
		    			'plan_num'	=>	$back_info['plan_num'],
		    			'real_num'	=>	isset($back_info['real_num']) ? $back_info['real_num'] : 0,
		    			'is_check'	=>	isset($back_info['is_check']) ? $back_info['is_check'] : 0
		    		];
    				$again['check_number'] = $back_info['box_number'];
    				$again['is_again_time'] = $back_info['is_again_time'];
    				break;
    			case 'AFTER':
    				$after = [
		    			'delivery_no'	=>	$back_info['delivery_no'],
		    			'related_no'	=>	$back_info['related_no'],
		    			'plan_num'	=>	$back_info['plan_num'],
		    			'real_num'	=>	isset($back_info['real_num']) ? $back_info['real_num'] : 0,
		    			'is_check'	=>	isset($back_info['is_check']) ? $back_info['is_check'] : 0
		    		];
    				$after['check_number'] = $back_info['related_no'];
    				break;
    		}

	    	// 组合数据
	    	if (!empty($all_reject)) {
	    		$check_info['all_reject'][] = $all_reject;
	    	}
	    	if (!empty($part_reject)) {
	    		$check_info['part_reject'][] = $part_reject;
	    	}
	    	if (!empty($again)) {
	    		$check_info['again'][] = $again;
	    	}
	    	if (!empty($after)) {
	    		$check_info['after'][] = $after;
	    	}
    	}

    	// 整理整单拒收
    	if (!empty($check_info['all_reject'])) {
    		foreach ($check_info['all_reject'] as $key => $item) {
    			//整单拒收箱数
	    		$waybill_data['allRejectNum'] = (isset($waybill_data['allRejectNum']) ? $waybill_data['allRejectNum'] : 0) + 1;
	    		// 整单拒收单数
	    		$all_reject_order[$item['related_no']] = 1;
    			if ($item['is_check'] == 1) {
    				//整单拒收已验箱数
	    			$waybill_data['allRejectCheckNum'] = (isset($waybill_data['allRejectCheckNum']) ? $waybill_data['allRejectCheckNum'] : 0) + 1;
	    			//整单拒收已验单数
	    			$check_reject_order[$item['related_no']] = 1;
    			}
    		}

    		$waybill_data['allRejectCount'] = count($all_reject_order);
    		$waybill_data['allRejectCheckCount'] = count($check_reject_order);
    	}

    	//拆单拒收
    	if (!empty($check_info['part_reject'])) {
    		foreach ($check_info['part_reject'] as $key => $item) {
    			//拆单拒收件数
	    		$waybill_data['partRejectNum'] = (isset($waybill_data['partRejectNum']) ? $waybill_data['partRejectNum'] : 0) + $item['plan_num'];
	    		// 拆单拒收单数
	    		$part_reject_order[$item['related_no']] = 1;
    			if ($item['is_check'] == 1) {
    				//拆单拒收已验件数
	    			$waybill_data['partRejectCheckNum'] = (isset($waybill_data['partRejectCheckNum']) ? $waybill_data['partRejectCheckNum'] : 0) + $item['real_num'];
	    			//拆单拒收已验单数
	    			$check_part_reject_order[$item['related_no']] = 1;
    			}
    		}
    		
    		$waybill_data['partRejectCount'] = count($part_reject_order);
    		$waybill_data['partRejectCheckCount'] = count($check_part_reject_order);
    	}

    	//改日送
    	if (!empty($check_info['again'])) {
    		foreach ($check_info['again'] as $key => $item) {
    			//改日箱数
	    		$waybill_data['againNum'] = (isset($waybill_data['againNum']) ? $waybill_data['againNum'] : 0) + 1;
	    		// 改日单数
	    		$again_order[$item['related_no']] = 1;
    			if ($item['is_check'] == 1) {
    				//改日已验箱数
	    			$waybill_data['againCheckNum'] = (isset($waybill_data['againCheckNum']) ? $waybill_data['againCheckNum'] : 0) + 1;
	    			//改日已验单数
	    			$check_again_order[$item['related_no']] = 1;
    			}
    		}

    		$waybill_data['againCount'] = count($again_order);
    		$waybill_data['againCheckCount'] = count($check_again_order);
    	}

    	// 售后提货
    	if (!empty($check_info['after'])) {
    		foreach ($check_info['after'] as $key => $item) {
    			//提货件数
	    		$waybill_data['afterNum'] = (isset($waybill_data['afterNum']) ? $waybill_data['afterNum'] : 0) + $item['plan_num'];
	    		// 提货单数
	    		$after_order[$item['related_no']] = 1;
    			if ($item['is_check'] == 1) {
    				//提货已验件数
	    			$waybill_data['afterCheckNum'] = (isset($waybill_data['afterCheckNum']) ? $waybill_data['afterCheckNum'] : 0) + $item['real_num'];
	    			//提货已验单数
	    			$check_after_order[$item['related_no']] = 1;
    			}
    		}

    		$waybill_data['afterCount'] = count($after_order);
    		$waybill_data['afterCheckCount'] = count($check_after_order);
    	}

    	return ['waybill_data' => $waybill_data, 'check_info' => $check_info];
    }

    /**
     * 更新验车执行数
     * @DateTime  2018-11-29
     * @version   [version]
     * @return    [type]      [description]
     */
    public function saveExecutedCount($waybill_no){

    	$num = DB::table('t_consign_order_check')->where('waybill_no', $waybill_no)->value(DB::raw('SUM(real_num)'));
    	if (is_null($num)) {
    		return true;
    	}
    	return DB::table('t_consign_order')->where('waybill_no', $waybill_no)->update(['executed_count'=>$num]);
    }


    // 获取装车单详情
    public function getDeliveryDetail($waybillNo) {

		return DB::table('t_consign_order_consign_box')->where('waybill_no', $waybillNo)->select('delivery_number')->get()->toArray();
	}
	

	/**
	 * 根据关联单号获取一条验车信息
	 *@param $agency_after_no 关联单号
	 *@return $result
	 */
	public function getCheckConsignByRelated($related_no){
		return DB::table('t_consign_order_check as coc')
			->where('related_no', $related_no)
			->select('related_no', 'plan_num', 'real_num', 'delivery_no', 'split_sku')
			->first();
	}

	/**
	 * 省代出库确认列表
	 */
	public function getAgencyConfirmOutList($params){
		$model = DB::table('t_consign_order_check_out as coco');
		$model->where('related_no', 'like', 'ASAP%')
				->select(
					'supplier',
					'status',
					'sku',
					'plan_num',
					'related_no',
					'real_num',
					'handover_user',
					'handover_num',
					'handover_time',
					'admin',
					'handover_mobile',
					'coco.created_at'
				);

		if (isset($params['related_nos']) && is_array($params['related_nos']) && !empty($params['related_nos'])) {
			$model->whereIn('related_no', $params['related_nos']);
		}

		//setWhere
		$condition = [];
		$condition[] = $this->buildPara($params, 'coco.related_no', 'like');
		$condition[] = $this->buildPara($params, 'coco.supplier', 'like');
		$condition[] = $this->buildPara($params, 'coco.sku', 'like');
		$this->setWhereBetween($model, $params, 'coco.handover_time', 'start_time', 'end_time');
		$this->setWhereBetween($model, $params, 'coco.created_at', 'c_start_time', 'c_end_time');
		if (isset($params['print_confirm']) && $params['print_confirm'] == 1) {
			$model->where('coco.status', 2);
			//不分页
			$params['request_file'] = 1;
		}else{		
			$condition[] = $this->buildPara($params, 'coco.status', '=');
		}

		return $this->getList($model, $condition, $params);
	}

	/**
	 * 修改分仓出库
	 */
    public function saveOutAgencyDataByRelatedNo($params, $related_no){
    	$userInfo = User::getUserDetail($params['user_id']);
    	$params['admin'] = $userInfo->login_name;
    	$data = $this->filterRegroup($this->t_consign_order_check_out, $params);

    	if (empty($data) || $data === false) {
    		return 0;
    	}

    	try {
    		DB::table($this->t_consign_order_check_out)->where('related_no',$related_no)->update($data);
    	} catch (\PDOException $e) {
    		throw $e;
    	}

    	return true;
    }

    /**
     * 根据关联单号获取分仓验车出库的一条信息
     */
    public function getConsignCheckOut($related_no){
    	return DB::table($this->t_consign_order_check_out)->where('related_no', $related_no)->first();
    }

    /**
     * 分仓确认出库
     */
    public function saveConsignCheckOut($user_id, $related_no){
    	// checkLogic(Role::checkUserRole($user_id, 'DISTRIBUTION_MANAGER'), '当前用户不是仓管用户');
    	$userInfo = User::getUserDetail($user_id);

    	$check_out = $this->getConsignCheckOut($related_no);
    	checkLogic(!is_null($check_out), '未找到关联单号是'.$related_no.'出库确认单');
    	checkLogic($check_out->handover_user != '', '提货人不能为空');
    	checkLogic($check_out->handover_mobile != '', '提货人手机不能为空');

    	checkLogic($check_out->status == 1, '只允许“新建状态下的才能确认出库”');

    	DB::beginTransaction();
    	try {
    		DB::table($this->t_consign_order_check_out)->where('related_no', $related_no)->update([
    			'status'	=>	2,
    			'handover_time'	=>	hDate()
    		]);

			OperationLog::saveLog(
				$userInfo->org_id,
				$userInfo->login_name,
				'update',
				'分仓确认出库（回路）' . $related_no,
				2
			);
			DB::commit();
    	} catch (\PDOException $e) {
    		DB::rollBack();
    		throw $e;
    	}

    	JobHelper::dispatchJob(
			SaveAgencyConsignOut::class, ['related_no' => $related_no]
		);

		// JobHelper::dispatchJob(
		// 	CreateReturnOrder::class, ['related_no' => $related_no]
		// );

    	return true;

    }
}