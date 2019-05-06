<?php
/**
 * Created by sublime3.
 * User: zhangdahao
 * Date: 2018/5/20
 * Time: 20:45
 */

namespace App\Models\AgencyOrder;

use App\User;
use Illuminate\Support\Facades\Log;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use App\Models\Logs\OperationLog;
use App\Models\Logs\StockRemain;
use App\Models\Permission\Organization;
use App\Models\Permission\Role;
use App\Models\BasicInfo\Warehouse;
use App\Jobs\JobHelper;
use App\Jobs\OrderJobs\SaveSmOrder;
use App\Jobs\AgencyJobs\SaveAgencyAfterOut;
use App\Jobs\AgencyJobs\SaveAgencyAfterIn;
use App\Jobs\BoxJobs\CreateAgencyAfterBox;
use App\Jobs\BoxJobs\CreateAgencyOrderBox;
use App\Jobs\OmsJobs\SendOmsExpressOut;
use App\Models\CenterWarehouse\CollectionArea;

class AgencyOrder extends BaseModel
{
	//表名
    protected $table = 't_agency_order';
    protected $table_detail= 't_agency_order_detail';
    protected $warehouse = 't_warehouse_info';
    protected $secquence = 't_date_dealer_secquence_number';
    protected $order_line_info = 't_order_line_info';
    protected $send_express = 't_send_express';
    protected $send_express_detail = 't_send_express_detail';
// 
    public function getAgencyInfo($params){
    	$model = DB::table($this->table.' as ao')
    			->select(
    				'ao.order_number',
					'ao.sm_order',
					'ao.agency_main_id',
					'aod.box_number',
					'aod.scan_status',
					'aod.scan_time',
					'ao.supplier',
					'ao.collection_code'
    			)
    			->where('ao.agency_main_id', '<>' ,'836')
    			->where('ao.agency_main_id', '<>' ,'839')
    			->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
    			->where('ao.is_del', 0)
    			->where('aod.is_del', 0);

    	if (isset($params['order_status']) && !empty($params['order_status'])) {
    		$model->where('ao.status', $params['order_status']);
    	}else{
    		$model->whereIn('ao.status', [1, 2, 3]);
    	}

    	if (isset($params['out_time_end'])) {
    		if (strlen($params['out_time_end']) == 10) {
    			$params['out_time_end'] .= ' 23:59:59'; 
    		}
    	}

    	$this->setWhereLike($model, $params, 'ao.supplier');
    	$this->setWhereBetween($model, $params, 'ao.out_time', 'out_time_start', 'out_time_end');

    	$list = $model->get()->toArray();

    	return $list;
    }


    /**
     * 分仓收货
     */
    public function verifyAgentOrder($params){

    	//set超时
    	$overtime = 3600 * 24 * 2;

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
		$supplier = $params['supplier'];
		$checkBox = substr($boxNumber, 0, 4);

		checkLogic($checkBox == 'RBAP', '箱号扫描不正确,请核实是否是RBAP*********');

		$boxInfo = DB::table($this->table_detail. ' as aod')
			->where('aod.is_del', 0)
			->select(
				'ao.order_number',
				'wi.warehouse_code',
				'ao.sm_order',
				'ao.agency_main_id',
				'aod.scan_status',
				'ao.supplier',
				'ao.bind_number',
				'ao.out_ware_day',
				'ao.collection_code'
			)
			->leftJoin($this->table.' as ao', 'ao.id', 'aod.agency_id')
			->leftJoin($this->warehouse.' as wi', 'wi.dealer_id', 'ao.dealer_id')
			->where('box_number', $boxNumber)
			->where('ao.supplier', $supplier)
			->first();

		checkLogic(!is_null($boxInfo), '系统不存在此箱号，或者该店铺没有此箱号!');

		$over_date = strtotime(date('Y-m-d')) - $overtime;
		checkLogic(strtotime($boxInfo->out_ware_day) > $over_date, '超时拒收');//超时拒收
		$orderCount = DB::table($this->table_detail.' as aod')
				->leftJoin($this->table.' as ao', 'ao.id', 'aod.agency_id')
				->where('aod.scan_status', 0)
				->where('ao.is_del', 0)
				->where('ao.agency_main_id', $boxInfo->agency_main_id)
				->count(DB::raw('DISTINCT aod.box_number'));
				
		$mpres = curl_post_erpapi('/consign_order/get_order_info_by_order', ['order'=>$boxInfo->order_number]);

		checkLogic($mpres['code'] == 200, 'mp返回:'.$mpres['msg']);
		$order_info = $mpres['data'];
		checkLogic(!empty($order_info), '订单信息为空');
		$collection_model = new CollectionArea();
		$collection_code = $collection_model->getCollectionArea($order_info['location_provinceCode'], $order_info['location_regionCode'], $order_info['location_cityCode'], $dealer_id);
		
		if ($boxInfo->scan_status == 1) {
			return [
				'supplier'		=>	$boxInfo->supplier,
				'box_number'	=>	$boxNumber,
				'order_number'	=>	$boxInfo->order_number,
				'collection_code'	=>	$collection_code
			];
		}

		// 开始收货
		DB::beginTransaction();
		try {

			$status = 2;
			if ($orderCount == 1) {
				$status = 3;
			}

			$is_express = 0;
			if ($collection_code == '物流区') {
				$is_express = 1;
			}

			DB::table($this->table)->where('is_del', 0)->where('agency_main_id', $boxInfo->agency_main_id)->update([
				'status' => $status,
				'collection_code'	=>	$collection_code,
				'is_express'	=>	$is_express,
				'recive_time'	=>	hDate()     //收货完成时间
			]);

			// 标记收货
			DB::table($this->table_detail)
				->where([
					['is_del', '=', 0],
					['box_number', '=', $boxNumber],
					['scan_status', '=', 0]
				])->update([
					'scan_status' => 1,
					'scan_time' => hDate(),
					'scan_user' => $userInfo->login_name
				]);

			// 在途日志
			$roadData = [$boxNumber => $boxInfo->sm_order];

			if (empty($boxInfo->warehouse_code)) {
				$boxInfo->warehouse_code = '';
			}
			StockRemain::roadRecord(
				'SUB_RECEIVE',
				$boxInfo->warehouse_code,
				$userInfo->login_name,
				$roadData
			);

			// 操作日志
			OperationLog::saveLog(
				$userInfo->org_id,
				$userInfo->login_name,
				'update',
				'扫码收货（省代）' . $boxInfo->sm_order . ' ' . $boxNumber,
				2
			);
			DB::commit();
			return [
				'supplier'		=>	$boxInfo->supplier,
				'box_number'	=>	$boxNumber,
				'order_number'	=>	$boxInfo->order_number,
				'collection_code'	=>	$collection_code
			];
		} catch (\PDOException $e) {
			DB::rollBack();
			throw $e;
		}
    }

    //获取所有供应商
    public function getSupplierList(){
    	return DB::table($this->table)->where('is_del', 0)->groupBy('supplier')->pluck('supplier')->toArray();
    }

    //标记拒收
    public function markOrderReject($agency_main_id, $user_id){
    	checkLogic(Role::checkUserRole($user_id, 'DISTRIBUTION_MANAGER'), '当前用户不是仓管用户');
    	// 获取当前登录用户的详细信息
		$userInfo = User::getUserDetail($user_id);

		//获取待修改ID
		$agency_id = DB::table($this->table)->where('is_del', 0)->where('agency_main_id', $agency_main_id)->pluck('id')->toArray();


		checkLogic(!empty($agency_id), '找不到agency_main_id='.$agency_main_id.'省代信息');

		DB::beginTransaction();
		try {
			DB::table($this->table)->whereIn('id', $agency_id)->update([
				'status'	=>	2,
				'remark'	=>	'省代标记退回'
			]);

			DB::table($this->table_detail)->whereIn('agency_id', $agency_id)->update([
				'scan_status'	=>	0,
				'remark'		=>	'箱号-省代标记退回'
			]);

			// 操作日志
			OperationLog::saveLog(
				$userInfo->org_id,
				$userInfo->login_name,
				'update',
				'省代标记退回' . json_encode($agency_id),
				2
			);
			DB::commit();
			return true;
		} catch (\Exception $e) {
			DB::rollBack();
			throw $e;
		}
    }

    //省代退回
    public function rollbackAgencyOrder($agency_main_id, $user_id){
    	checkLogic(Role::checkUserRole($user_id, 'DISTRIBUTION_MANAGER'), '当前用户不是仓管用户');
    	// 获取当前登录用户的详细信息
		$userInfo = User::getUserDetail($user_id);

    	if (is_array($agency_main_id)) {
    		$agency_id = DB::table($this->table)->where('is_del', 0)->whereIn('agency_main_id', $agency_main_id)->pluck('id');
    		DB::beginTransaction();
    		try {
    			$orderInfo = [];
    			foreach ($agency_main_id as $order) {
	    			$orderInfo = DB::table($this->table.' as ao')
		    			->select(
							'aod.box_number',
							'agency_main_id',
							'ao.supplier',
							'ao.is_del',
							'ao.id'
		    			)
		    			->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
		    			->where([['ao.is_del', '=', 0], ['ao.agency_main_id', '=', $order]])
		    			->get()
		    			->toArray();
	    		}

			    $orderInfo = object_to_array($orderInfo);
			    // 没有要退回的单子
			    if (empty($orderInfo)) {
			    	return 0;
			    }

    			DB::table($this->table)->whereIn('agency_main_id', $agency_main_id)->update([
    				'status' 	=>	 	4,
    				'is_del' 	=> 		1,
    				'remark'	=>		'省代退回'
    			]);

    			DB::table($this->table_detail.' as aod')->whereIn('aod.agency_id', $agency_id)->update([
    				'aod.is_del' 	=> 		1,
    				'aod.remark'	=>		'省代退回'
    			]);

    			$res = curl_post_apsapi('manage/order/order/refuse', ['main_ids'=>json_encode($agency_main_id)]);
				checkLogic($res['code'] == 200, '通知第三方退货失败:'.$res['msg']);

				foreach ($agency_main_id as $order) {
					// 操作日志
					OperationLog::saveLog(
						$userInfo->org_id,
						$userInfo->login_name,
						'update',
						'省代退回' . $order,
						2
					);

			    	$roadData = [];
			    	foreach ($orderInfo as $key => $item) {
			    		$roadData[$item['box_number']] = $item['agency_main_id'];
			    	}
					//在途日志
					StockRemain::roadRecord(
						'SUB_RECEIVE',
						isset($orderInfo[0]['supplier']) ? $orderInfo[0]['supplier'] : "",
						$userInfo->login_name,
						$roadData
					);
				}

				DB::commit();

				return count($agency_main_id);
    		} catch (\PDOException $e) {
    			DB::rollBack();
    			throw $e;
    			
    		}
    	}else{

			$orderInfo = DB::table($this->table.' as ao')
	    			->select(
	    				'ao.id',
						'aod.box_number',
						'agency_main_id',
						'ao.supplier'
	    			)
	    			->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
	    			->where('ao.is_del', 0)
	    			->where('ao.agency_main_id', $agency_main_id)
	    			->get()
	    			->toArray();

	    	$orderInfo = object_to_array($orderInfo);

	    	$agency_id = array_column($orderInfo, 'id');
			checkLogic(!empty($orderInfo), '订单不存在或者不需要退回'.$agency_main_id);
			DB::beginTransaction();
    		try {
    			$res = curl_post_apsapi('manage/order/order/refuse', ['main_ids'=>json_encode([$agency_main_id])]);
				checkLogic($res['code'] == 200, '通知第三方退货失败:'.$res['msg']);

    			DB::table($this->table)->where('agency_main_id', $agency_main_id)->update([
    				'status' 	=>	 	4,
    				'is_del' 	=> 		1,
    				'remark'	=>		'省代退回'
    			]);

    			DB::table($this->table_detail)->where('agency_id', $agency_id)->update([
    				'is_del' 	=> 		1,
    				'remark'	=>		'省代退回'
    			]);


				// 操作日志
				OperationLog::saveLog(
					$userInfo->org_id,
					$userInfo->login_name,
					'update',
					'省代退回' . $agency_main_id,
					2
				);

		    	$roadData = [];
		    	foreach ($orderInfo as $key => $item) {
		    		$roadData[$item['box_number']] = $item['agency_main_id'];
		    	}
				//在途日志
				StockRemain::roadRecord(
					'SUB_RECEIVE',
					isset($orderInfo[0]['supplier']) ? $orderInfo[0]['supplier'] : "",
					$userInfo->login_name,
					$roadData
				);

				DB::commit();

				return 1;
    		} catch (\PDOException $e) {
    			DB::rollBack();
    			throw $e;
    		}
    	}
    }


    /**
     * 修改集货号
     */
    public function saveBindNumber($order_number, $bind_head, $bind_body){

    	DB::beginTransaction();
    	try {
    		//修改线路集货号
    		DB::table($this->order_line_info)->where('order_number', $order_number)->update(
    			['bind_number' => $bind_head.'-'.$bind_body]
    		);

    		//修改省代出库
    		DB::table($this->table)->where('order_number', $order_number)->update(
    			['bind_number' => $bind_head.'-'.$bind_body]
    		);

    		//修改集货号记录表
    		DB::table($this->secquence)->where('order_number', $order_number)->update(
    			['dete_dealer_prefix' => $bind_head, 'order_secquence_number' => $bind_body]
    		);
    		DB::commit();
    		return true;

    	} catch (\PDOException $e) {
    		DB::rollBack();
    		throw $e;
    	}
    }

    /**
     * 分仓收货结束
     */
    public function saveAgencyOver($agency_main_id, $user_id){

    	checkLogic(Role::checkUserRole($user_id, 'DISTRIBUTION_MANAGER'), '当前用户不是仓管用户');

    	// 获取当前登录用户的详细信息
		$userInfo = User::getUserDetail($user_id);

    	// 拒收 向MP推送出库单
    	try {
    		//售后不拒收
    		$reject_model = DB::table($this->table.' as ao')->where('ao.is_del', 0)->where('ao.order_number', 'not like', 'ASAP%');
    		$recive_model = DB::table($this->table.' as ao')->where('ao.is_del', 0);

    		// 获取能够修改的省代出库id。 以及拒收的订单号
	    	if (is_array($agency_main_id)) {
	    		$recive_model->whereIn('ao.agency_main_id', $agency_main_id);
	    		$reject_model->whereIn('ao.agency_main_id', $agency_main_id);
	    	} else {
	    		$recive_model->where('ao.agency_main_id', $agency_main_id);
	    		$reject_model->where('ao.agency_main_id', $agency_main_id);
	    	}

	    	// 获取已收货的箱号sku
	    	$recive_list = $recive_model->select('ao.sm_order', 'ao.order_number', 'ao.from_warehouse_code', 'ao.product_info', 'aod.box_number', 'ao.agency_main_id', 'ao.is_express')
	    		->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
	    		->where('ao.status', 3)
	    		->get()
	    		->toArray();

	    	$recive_list = object_to_array($recive_list);
	    	$recive_main_id = array_fu(array_column($recive_list, 'agency_main_id'));
	    	// 同主单箱号归并
	    	$recive_box = [];
	    	$as_product = [];
	    	foreach ($recive_list as $k => $item) {
	    		if (isset($recive_box[$item['agency_main_id']]) && isset($recive_box[$item['agency_main_id']]['box_info'])) {
		    		if (!in_array($item['box_number'], $recive_box[$item['agency_main_id']]['box_info'])) {
		    			$recive_box[$item['agency_main_id']]['box_info'][] = $item['box_number'];
		    		}
	    		}else{
	    			$recive_box[$item['agency_main_id']]['box_info'][] = $item['box_number'];
	    		}
	    	}
	    	// 组合出库信息
	    	$box_info = [];
	    	$as_box = [];
	    	foreach ($recive_list as $k => $item) {
	    		$products = [];
		    	checkLogic(isset($recive_box[$item['agency_main_id']]), '箱号归并数据格式有误！');
		    	$product_info = json_decode($item['product_info'], 1);

	    		if ($item['agency_main_id'] < 0) {
		    		if(!empty($product_info)){
		    			foreach ($product_info as $key => $product) {
				    		$products[] = [
				    			$product['product_code']	=>	$product['quantity']
				    		];
		    			}
		    		}

	    			$as_box[$item['sm_order']] = [
	    				'aftersales_no'	=>	$item['order_number'],
	    				'deliver_type'	=>	'DELIVER_AFTER_SALE',
	    				'box_number'	=>	$recive_box[$item['agency_main_id']]['box_info'],
	    				'product_code'	=>	$products
	    			];
	    		}else{
		    		$box_info[$item['sm_order']]['fwarehouseid186number'] = $item['from_warehouse_code'];
		    		$box_info[$item['sm_order']]['finterfacenumbera186'] = $item['sm_order'];
		    		$box_info[$item['sm_order']]['box_info'] = $recive_box[$item['agency_main_id']]['box_info'];
		    		$box_info[$item['sm_order']]['is_express'] = $item['is_express'];

		    		if(!empty($product_info)){
		    			if (!isset($box_info[$item['sm_order']]['send_info'])) {
			    			foreach ($product_info as $key => $product) {
					    		$box_info[$item['sm_order']]['send_info'][] = [
					    			'sku'	=>	$product['product_code'],
					    			'qty'	=>	$product['quantity']
					    		];
			    			}
		    			}
		    		}else{
		    			$box_info[$item['sm_order']]['send_info'][] = [
			    			'sku'	=>	'',
			    			'qty'	=>	''
			    		];
		    		}
	    		}
	    	}

	    	$reject_agency_main_id = $reject_model->where('ao.status', 2)
    			->pluck('ao.agency_main_id')
    			->toArray();

    		$reject_agency_main_id = array_fu($reject_agency_main_id);

    		// 标记三方标记退回
    		$reject_num = 0;
    		if (!empty($reject_agency_main_id)) {
    			$reject_num = $this->rollbackAgencyOrder($reject_agency_main_id, $user_id);
    		}

    		foreach ($recive_main_id as $k => $main_id) {
	    		if ($main_id<0) {
	    			unset($recive_main_id[$k]);
	    		}
	    	}
    		//推送三方标记已收货
    		if (!empty($recive_main_id)) {
	    		$res = curl_post_apsapi('manage/order/order/receipt', ['main_ids'=>json_encode($recive_main_id)]);
				checkLogic($res['code'] == 200, '通知第三方失败:'.$res['msg']);
	    	}

	    	//修改收货状态
	    	if (!empty($as_box)) {
	    		$sm_order_arr = array_keys($as_box);

	    		$updateAgency = DB::table($this->table)->where('is_del', 0)->where('status', 3)->whereIn('sm_order', $sm_order_arr)->update([
	    			'status'	=> 	5
	    		]);

	    		checkLogic($updateAgency, '省代收货更新状态失败');
	    	}

	    	//省代售后出库
	    	foreach ($as_box as $sm_order => $item) {
	    		//操作日志
				OperationLog::saveLog(
					$userInfo->org_id,
					$userInfo->login_name,
					'insert',
					'推送省代售后出库单' . $item['aftersales_no'],
					1
				);

				$params = [
    				'aftersales_no'		=>		$item['aftersales_no'],
    				'deliver_type'		=>		$item['deliver_type'],
    				'box_number'		=>		$item['box_number'],
    				'product_code'		=>		$item['product_code']
    			];
    			//通知OMS省代出库
	     		JobHelper::dispatchJob(
					SaveAgencyAfterOut::class, $params
				);

	     		//已入分仓售后 同事 省代仓
				JobHelper::dispatchJob(
					SaveAgencyAfterIn::class, ['as_number' => $item['aftersales_no']]
				);

				//省代
				JobHelper::dispatchJob(
					CreateAgencyAfterBox::class, [
						'sm_order' => $sm_order
					]
				);
	    	}

    		// 推送出库单
    		foreach ($box_info as $sm_order => $item) {
    			// 操作日志
				OperationLog::saveLog(
					$userInfo->org_id,
					$userInfo->login_name,
					'insert',
					'推送省代出库单' . $sm_order,
					1
				);

    			$params = [
    				'fwarehouseid186number'		=>		$item['fwarehouseid186number'],
    				'finterfacenumbera186'		=>		$item['finterfacenumbera186'],
    				'box_info'					=>		$item['box_info'],
    				'send_info'					=>		$item['send_info']
    			];

    			//自配送单自己创建箱码
    			if ($item['is_express'] !== 1) {
	    			//省代箱码自动保存箱码
	    			JobHelper::dispatchJob(
						CreateAgencyOrderBox::class, ['sm_order'	=>	$sm_order]
					);
    			}

    			//已收货出库单确认出库
	     		JobHelper::dispatchJob(
					SaveSmOrder::class, $params
				);
    		}

    	} catch (\Exception $e) {
    		throw $e;
    	}


    	$receive_box_count = count($box_info) + count($as_box);
    	return '入库'.$receive_box_count.'单';//,拒收了'.$reject_num.'单';
    }

    /**
     * 推送MP入库
     */
    public function saveAgencyOutFinish($sm_order){
    	try {
    		DB::table($this->table)->where('sm_order', $sm_order)->update(['status'=>5]);
    		return true;
    	} catch (\PDOException $e) {
    		throw $e;
    	}
    }

    /**
     * 根据关联单号获取一个省代信息
     * @param box_number 关联单号
     * @return result
     */
    public function getAgencyDetailByBoxNumber($box_number){
    	return DB::table($this->table.' as ao')
    		->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
    		->where('ao.is_del', 0)
    		->where('aod.is_del', 0)
    		->where('aod.box_number', $box_number)
    		->first();
    }


    /**
     * 获取要发快递的省代单据
     */
    public function getWantSendExpress($user_id){

    	$userInfo = User::getUserDetail($user_id);

    	$model = DB::table($this->table.' as ao')
    			->select(
    				'ao.order_number',
					'ao.sm_order',
					'ao.agency_main_id',
					'aod.box_number',
					'sed.print_time',
					'sed.status',
					'sed.child_no'
    			)
    			->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
    			->leftJoin($this->send_express_detail.' as sed', 'sed.box_number', 'aod.box_number')
    			->where('ao.dealer_id', $userInfo->dealer_id)
    			->where('ao.is_express', 1)
    			->where('ao.status', 5)
    			->where('ao.is_del', 0)
    			->groupBy('aod.box_number');
		Log::info('仓管信息：'.json_encode($userInfo));
    	return $list = $model->get()->toArray();
    }

    //快递箱号扫描
    public function sacnExpressBox($box_number){

    	$agency = DB::table($this->table.' as ao')
    			->select(
    				'ao.order_number as related_no',
					'aod.box_number'
    			)
    			->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
    			->where('ao.is_express', 1)
    			->where('ao.status', 5)
    			->where('ao.is_del', 0)
    			->where('aod.box_number', $box_number)
    			->first();
    	checkLogic(!is_null($agency), '系统不存在要发快递此箱号'.$box_number);

    	return [
    		'box_number'	=>	$box_number,
    		'related_no'	=>	$agency->related_no
    	];
    }

    //获取要需要交接快递出库单局
	public function getExpressHandOut($user_id){
		$userInfo = User::getUserDetail($user_id);

    	$model = DB::table($this->table.' as ao')
    			->select(
    				'ao.order_number as related_no',
    				'se.express_no',
    				'sed.child_no',
					'ao.agency_main_id',
					'aod.box_number',
					'sed.print_time',
					'sed.is_send_off',
					'sed.child_no'
    			)
    			->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
    			->leftJoin($this->send_express_detail.' as sed', 'sed.box_number', 'aod.box_number')
    			->leftJoin($this->send_express.' as se', 'sed.parent_no', 'se.express_no')
    			->where('ao.dealer_id', $userInfo->dealer_id)
    			->where('ao.is_express', 1)
    			->whereIn('se.status', [3, 4])
    			->where('ao.status', 6)
    			->where('ao.is_del', 0)
    			->groupBy('aod.box_number');

    	$list = $model->get()->toArray();
    	return object_to_array($list);
	}

    /**
     * 打印快递面单
     * @param $box_number
     * @param $user_id
     * @return mixed
     * @throws \App\Exceptions\KnownLogicException
     */
    public function printExpressDocuments($box_number, $user_id){

    	$agencyInfo =  DB::table($this->table.' as ao')
    		->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
    		->where('aod.box_number', $box_number)
    		->where('aod.is_del', 0)
    		->first();

    	checkLogic(!is_null($agencyInfo), '未找到箱号为:'.$box_number.'的省代出库信息');

    	if ($this->isCheckExpress($box_number)) {
    		$agency = DB::table($this->send_express_detail)->where('box_number', $box_number)->first();
    		$parent_no = $agency->parent_no;
    		DB::beginTransaction();
    		try {
	    		//箱号所在的订单是否已生成面单已生成
	    		DB::table($this->send_express_detail)->where('box_number', $box_number)->update([
	    			'status'	=>	1,
	    			'print_time'	=>	hDate()
	    		]);

	    		$express = DB::table($this->send_express.' as se')
		    		->select(
		    			'se.box_num',
		    			DB::raw('COUNT(DISTINCT sed.box_number) as print_box')
		    		)
		    		->leftjoin($this->send_express_detail.' as sed', function ($join) {
			            $join->on('se.express_no', '=', 'sed.parent_no')
			                 ->where('sed.status', '=', 1);
			        })
		    		->where('sed.parent_no', $parent_no)
		    		->first();	

//		    	 $related_box_count = DB::table($this->send_express_detail.' as sed')
//		    	 	->where('sed.related_no', $agencyInfo->order_number)
//		    	 	->where('status', 0)
//		    	 	->count();

//		    	 if ($related_box_count <= 0) {
//	    		 	DB::table($this->table)->where('order_number', $agencyInfo->order_number)->update([
//	    		 		'status'	=>	6
//	    		 	]);
//		    	 }

	    		if ($express->box_num === $express->print_box && $express->print_box != 0) {

	    			DB::table($this->send_express)->where('express_no', $parent_no)->update([
	    				'status'	=>	3
	    			]);
	    		}
 				DB::commit();
    		} catch (\PDOException $e) {
    			DB::rollBack();
    			throw $e;
    		}

    		//打印面单
    		$res = curl_post_tsapi('order/Transport/getTransportTaskInfo', ['transport_no'	=> $parent_no]);

			if ($res['code'] != 200) {
	            return 'TS返回'.$res['msg'].$parent_no;
	        }
	        if (is_string($res['data'])) {
	             return 'TS返回data:'.$res['data'];
	        }

	        $res['data']['box_number'] = $agency->box_number;
	        $res['data']['order_number'] = $agency->related_no;
	        $res['data']['serial_number'] = $express->print_box;
	        $res['data']['provider_number'] = $agency->child_no;
	        $res['data']['api_para'] = json_decode($res['data']['api_para'], 1);
	        return $res['data'];
    	}else{

	    	$related_no = $agencyInfo->order_number;
    		//箱号所在的订单是否已生成面单未生成
	    	$result = curl_post_erpapi('for_tms/get_user_order_info_by_related_no' , ['related_no' => $related_no]);

	    	if ($result['code'] != 200) {
	            return hError('MP返回'.$result['msg']);
	        }
	        if (is_string($result['data'])) {
	             return hError('MP返回data:'.$result['data']);
	        }

	    	$userInfo = User::getUserDetail($user_id);
	        $send_info = Warehouse::getWarehouseLocation('dealer_id', $userInfo->dealer_id);
	    	$model = DB::table($this->table.' as ao')
	    			->select(
	    				'ao.order_number',
						'ao.sm_order',
						'aod.box_number'
	    			)
	    			->leftJoin($this->table_detail.' as aod', 'ao.id', 'aod.agency_id')
	    			->where('ao.dealer_id', $userInfo->dealer_id)
	    			->where('ao.is_express', 1)
	    			->where('ao.status',5)
	    			->where('express_task_batch', 0)
	    			->where('ao.is_del', 0);

	    	if (empty($agencyInfo->express_task_merge)) {
	    		$model->where('aod.box_number', $box_number);
	    	}else{
	    		$model->where('ao.express_task_merge', $agencyInfo->express_task_merge);
	    	}

    		$expressInfo = $model->groupBy('aod.box_number')->get()->toArray();
    		$expressInfo = object_to_array($expressInfo);
            if(count($expressInfo) == 0){
                return hError('请先确认是否收货!');
            }
            $goodsNameResult = curl_post_apsapi('manage/order/internalorder/getGoodsName',['box_code'=>$box_number]);
            $goods_name = '货物';
            if($goodsNameResult['code'] == 200){
                $goods_name = $goodsNameResult['data']['goods_name'];
            }
            //订单批次唯一
    		$related_key = $related_no.'_'.$agencyInfo->agency_main_id;

    		//构造数据
    		if (substr($related_no, 0, 2) == 'AS' && false) {
    			// 销售订单
	    		$ky_data = [
	    			'aftersales_no'	=>		$related_key,
	    			'service_type'	=>	1,
	    			'warehouse_code'	=>	$userInfo->warehouse_code,
	    			'from_warehouse'	=>	'',
	    			'suggested_provider'	=>	'KYWL',
	    			'province'	     =>	 $send_info->provience_name,
	    			'region'	     =>	 $send_info->city_name,
	    			'city'	     =>	 $send_info->region_name,
	    			'address'	     =>	 $send_info->address,
	    			'contact'	     =>	 $userInfo->login_name,
	    			'mobile'	     =>	 $userInfo->mobile,
	    			'order_amount'	=>	0,
	    			'volume'	=>	'0',//
	    			'weight'	=>	'0',//
	    			'piece_count'	     =>	 count($expressInfo),
	    			'content'			=>	$goods_name,
	    			'sync'				=>	1
	    		];

	    		$res = curl_post_tsapi('order/return/create', $ky_data);

	    		if ($res['code'] != 200) {
	    			return 'TS创建售后运单任务失败，返回'.$res['msg'];
	    		}
	    		$express_no = $res['data']['transport_no'];

    		}else{
    			// 销售订单
	    		$ky_data = [
	    			'sale_order_no'	=>		$related_key,
	    			'receiver'	=>		$result['data']['receiver'],
	    			'receiver_contact'	=>		$result['data']['receiver'],
	    			'receiver_mobile'	=>		$result['data']['receiver_mobile'],
	    			'province'	=>		$result['data']['province'],
	    			'city'	=>		$result['data']['city'],
	    			'region'	=>		$result['data']['region'],
	    			'address'	=>		$result['data']['address'],
	    			'suggested_provider'	=>	'KYWL', 
	    			'order_amount'	=>	'0',//
	    			'volume'	=>	'0',//
	    			'weight'	=>	'0',//
	    			'wanted_time'	=>	hDate(),
	    			'warehouse_code'	=>	$userInfo->warehouse_code,
	    			'sender_contact'	     =>	 $userInfo->login_name,
	    			'sender_mobile'	     =>	 $userInfo->mobile,
	    			'sender_province'	     =>	 $send_info->provience_name,
	    			'sender_city'	     =>	 $send_info->city_name,
	    			'sender_region'	     =>	 $send_info->region_name,
	    			'sender_address'	     =>	 $send_info->address,
	    			'piece_count'	     =>	 count($expressInfo),
	    			'content'			=>	$goods_name,
	    			'sync'				=>	1
	    		];

	    		$res = curl_post_tsapi('order/sale/create', $ky_data);
	    		if ($res['code'] != 200) {
	    			return 'TS创建运单任务失败，返回'.$res['msg'];
	    		}

	    		$express_no = $res['data']['transport_no'];
    		}
    		
    		$res = curl_post_tsapi('order/Transport/getTransportTaskInfo', ['transport_no'	=> $express_no]);

    		if ($res['code'] != 200) {
    			return 'TS获取运单任务失败，返回'.$res['msg'].$express_no;
    		}

			checkLogic($res['data']['provider_number'] != '', '物流母单号不能为空');

			if (count($expressInfo) == 1) {
                $child_box = $res['data']['provider_number'];
            } else {
                $child_box = isset($res['data']['sub_order']) ? $res['data']['sub_order'] : '';
            }
			$child_box_arr = explode(',', $child_box);
			if (count($expressInfo)  !== count($child_box_arr)) {
				return '箱数与面单数不等';
			}
    		$send_express_data = [];
    		$send_express_datail_data = [];

    		foreach ($expressInfo as $k => $item) {
    			if (count($expressInfo) === 1) {
					$child_box_arr[$k] = $res['data']['provider_number'];
				}
	    		$send_express_datail_data[$k] = [
	    			'related_no'	=>		$item['order_number'],
	    			'parent_no'		=>		$express_no,
	    			'child_no'	=>		$child_box_arr[$k],
	    			'box_number'	=>		$item['box_number'],
	    			'status'		=>	0,
	    			'print_time'		=>	hDate()
	    		];
	    		if ($item['box_number'] == $box_number) {
	    			$send_express_datail_data[$k]['status'] = 1;
	    		}
    		}

    		$send_express_data = [
    			'express_no' =>	$express_no,
    			'dealer_id' =>	$userInfo->dealer_id,
    			'box_num'	 =>	 count($expressInfo),
    			'receiver'	 =>	 $result['data']['receiver'],
    			'receiver_contact'	 =>	$result['data']['receiver'],
    			'receiver_mobile'	 =>	 $result['data']['receiver_mobile'],
    			'receiver_province'	 =>	 $result['data']['province'],
    			'receiver_city'	 =>	 $result['data']['city'],
    			'receiver_region'	 =>	 $result['data']['region'],
    			'receiver_address'	 =>	 $result['data']['address'],
    			'provider'	 =>	 '跨越速运',
    			'wanted_time'	     =>	 hDate(),
    			'real_time'	     =>	 '',
    			'sender_contact'	     =>	 $userInfo->login_name,
    			'sender_mobile'	     =>	 $userInfo->mobile,
    			'sender_province'	     =>	 $send_info->provience_name,
    			'sender_city'	     =>	 $send_info->city_name,
    			'sender_region'	     =>	 $send_info->region_name,
    			'sender_address'	     =>	 $send_info->address
    		];

    		if (count($expressInfo) == 1) {
    			$send_express_data['status'] = 3;
    		}

    		DB::beginTransaction();

    		try {
    			DB::table($this->send_express)->insert($send_express_data);
    			DB::table($this->send_express_detail)->insert($send_express_datail_data);
    			// if (count($expressInfo) == 1) {
    			//是否已经打印面单
    			DB::table($this->table)->where('id', $agencyInfo->agency_id)->update([
    				'express_task_batch'	=>	1
    			]);
	    		// }
    			DB::commit();
    		} catch (\PDOException $e) {
    			DB::rollBack();
    			throw $e;
    		}

    		$agency = DB::table($this->send_express_detail)->where('box_number', $box_number)->first();
    		
    		$res['data']['box_number'] = $box_number;
    		$res['data']['order_number'] = $agency->related_no;
    		$res['data']['serial_number'] = 1;
    		$res['data']['provider_number'] = $agency->child_no;
    		$res['data']['api_para'] = json_decode($res['data']['api_para'], 1);
	        return $res['data'];

    	}	
    }

    /**
     * 检验箱号所在的订单是否已生成面单
     */
    public function isCheckExpress($box_number){
    	$count = DB::table($this->send_express_detail)
    		->where('box_number', $box_number)
    		->count();
    	if ($count > 0) {
    		return true;
    	}
    	return false;
    }

    /**
     * 收货结束
     */
    public function overExpressPrint($user_id){

    	$userInfo = User::getUserDetail($user_id);

    	$box_number = DB::table($this->send_express.' as se')
    		->leftjoin($this->send_express_detail.' as sed', 'sed.parent_no', 'se.express_no')
	        ->where('se.dealer_id', $userInfo->dealer_id)
    		->where('se.status', 3)
    		->pluck('box_number')
    		->toArray();

    	$box_number = array_fu($box_number);

    	checkLogic(!empty($box_number), '没有打印完成的单子,请先打印快递面单');

    	$agency_main_id = DB::table($this->table.' as ao')
    				->leftjoin($this->table_detail.' as aod', 'aod.agency_id', 'ao.id')
    				->whereIn('aod.box_number', $box_number)
    				->where('ao.is_del', 0)
    				->where('ao.is_express', 1)
    				->where('ao.status', 5)
    				->pluck('ao.agency_main_id')
    				->toArray();
    	$agency_main_id = array_fu($agency_main_id);

    	checkLogic(!empty($agency_main_id), '没有需要打印结束的省代批次');

    	DB::beginTransaction();
		try {

			DB::table($this->table)
				->whereIn('agency_main_id', $agency_main_id)
				->where('is_del', 0)
				->where('is_express', 1)
				->where('status', 5)
				->update([
					'status'	=>	6
				]);
			DB::commit();
		} catch (\PDOException $e) {
			DB::rollBack();
			throw $e;
		}

    	return '打印了'.count($agency_main_id).'单';
    }

    /**
     * 交接完成
     */
    public function finishExpressOut($user_id){

    	$userInfo = User::getUserDetail($user_id);
    	$expressInfo = DB::table($this->send_express.' as se')
    		->select(
    			'se.box_num',
    			'sed.box_number',
    			DB::raw('COUNT(DISTINCT sed.box_number) as sacn_box'),
    			'parent_no'
    		)
    		->leftjoin($this->send_express_detail.' as sed', function ($join) {
	            $join->on('se.express_no', '=', 'sed.parent_no')
	                 ->where('sed.is_send_off', '=', 1);
	        })
	        ->where('se.dealer_id', $userInfo->dealer_id)
    		->groupBy('parent_no')
    		->where('se.status', 4)
    		->get()
    		->toArray();

		$expressInfo = object_to_array($expressInfo);
    	$falg = 0;
    	$express_no	= [];
    	foreach ($expressInfo as $k => $item) {
    		$falg++;
    		if ($item['box_num'] === $item['sacn_box'] && $item['sacn_box'] != 0) {
    			$express_no[] = $item['parent_no'];
    		}
    	}
    	

		$parent_no = $express_no;

		$box_number = DB::table($this->send_express_detail)->whereIn('parent_no', $parent_no)->pluck('box_number')->toArray();

		$agency_main_id = DB::table($this->table.' as ao')
    				->leftjoin($this->table_detail.' as aod', 'aod.agency_id', 'ao.id')
    				->whereIn('aod.box_number', $box_number)
    				->where('ao.is_del', 0)
    				->where('ao.is_express', 1)
    				->where('ao.status', 6)
    				->pluck('ao.agency_main_id')
    				->toArray();
    	$agency_main_id = array_fu($agency_main_id);

    	checkLogic(!empty($agency_main_id), '没有需要出库的省代批次');

    	

    	$express_no = array_fu($express_no);

    	$related = DB::table($this->send_express_detail)->whereIn('parent_no', $express_no)->pluck('related_no')->toArray();

 		DB::beginTransaction();
		try {

			DB::table($this->send_express)->whereIn('express_no', $express_no)->update([
				'status'	=>	5
			]);

			DB::table($this->table)
				->whereIn('agency_main_id', $agency_main_id)
				->where('is_del', 0)
				->where('is_express', 1)
				->where('status', 6)
				->update([
					'status'	=>	7
				]);
			DB::commit();
		} catch (\PDOException $e) {
			DB::rollBack();
			throw $e;
		}

		//去空去重复
		$related = array_fu($related);

		//通知OMS 
		foreach ($related as $order) {
			JobHelper::dispatchJob(
				SendOmsExpressOut::class, ['aftersales_no'	=>	$order]
			);
		}

    	return '共有'.$falg.'单需要出库，实际出库了'.count($express_no).'单';
    }


    //出库交接扫码出
    public function verifyExpressOut($box_number, $user_id){
    	$express_box = DB::table($this->send_express_detail)
    		->where('box_number', $box_number)
    		->where('status', 1)
    		->first();
    	checkLogic(!is_null($express_box), '系统不存在要发货的此箱号，请核实!');

    	if ($express_box->is_send_off == 1) {
    		return [
    			'child_no'	=>	$express_box->child_no,
    			'box_number'	=>	$express_box->box_number,
    			'related_no'	=>	$express_box->related_no
    		];
    	}else{

    		DB::beginTransaction();
    		try {
	    		DB::table($this->send_express_detail)->where('box_number', $box_number)->update([
	    			'is_send_off'	=>	1
	    		]);

	    		DB::table($this->send_express)->where('express_no', $express_box->parent_no)->update([
	    			'status'	=>	4
	    		]);

	    		$userInfo = User::getUserDetail($user_id);
	    		// 操作日志
				OperationLog::saveLog(
					$userInfo->org_id,
					$userInfo->login_name,
					'update',
					'分仓快递出库' . $box_number,
					1
				);

				$roadData = [$box_number => $express_box->child_no];
				StockRemain::roadRecord(
					'SEND_EXPRESS',
					$express_box->child_no,
					$userInfo->username,
					$roadData
				);
				DB::commit();
    		} catch (\PDOException $e) {
    			DB::rollBack();
    			throw $e;
    		}

    		return [
    			'child_no'	=>	$express_box->child_no,
    			'box_number'	=>	$express_box->box_number,
    			'related_no'	=>	$express_box->related_no
    		];
    	}

    }

    /**
     * 省代拒收列表
     */
   //  public function getAgencyRejectList($params){
 		// if (!isset($params['warehouse_code']) || empty($params['warehouse_code'])) {
 		// 	 return '未选择仓库';
 		// }

 		// $model = DB::table($this->table.'as ao')
 		// 	->select(
 		// 		'ao.order_number',
 		// 		'ao.supplier',
 		// 		DB::raw('COUNT(DISTINCT aod.box_number) as real_box_num'),
 		// 		DB::raw('COUNT(DISTINCT aods.box_number) as plan_box_num'),
 		// 		'wi.warehouse_name',
 		// 		'operation_status'
 		// 	)->where('ao.status', '4');

 		// 	// setWhere
 		// 	$condition[] = $this->buildPara($params, 'ao.supplier', 'like');
	  //       $condition[] = $this->buildPara($params, 'ao.order_number', 'like');
	  //       $condition[] = $this->buildPara($params, 'wi.warehouse_code', 'like');
	  //       $condition[] = $this->buildPara($params, 'wi.operation_status', 'like');


 		// 	return $this->getList($model, $condition, $params);

   //  }












########################################################. WEB开始 . ###############################################################

    /**
     * 获取省代出库列表
     * @return result
     */
    public function getAgencyList($params){
    	$model = DB::table($this->table.' as ao')
    			->select(
    				'ao.order_number',
					'ao.agency_main_id',
					'ao.supplier',
					'ao.out_ware_day',
					'ao.status',
					'ao.out_time'
    			);

    	if (isset($params['status']) && $params['status'] == 4) {
    		$model->where('ao.is_del', 1);
    	}else{
    		$model->where('ao.is_del', 0);
    	}

		// setWhere
		$condition = [];
		$condition[] = $this->buildPara($params, 'ao.supplier', '=');
        $condition[] = $this->buildPara($params, 'ao.order_number', 'like');
        $condition[] = $this->buildPara($params, 'ao.agency_main_id', 'like');
        $condition[] = $this->buildPara($params, 'ao.status', '=');
        $this->setWhereBetween($model, $params, 'ao.out_ware_day', 'out_ware_day_start', 'out_ware_day_end');

        $model->groupBy('ao.agency_main_id')->get();
        return $this->getList($model, $condition, $params);

    }

    /**
     * 三方出库取消
     * @param $batch_number 批次号
     * @return
     */
    public function cancelAgencyOrder($batch_number){
    	$agency_id = DB::table($this->table)->where('agency_main_id', $batch_number)
                ->where('status', 1)
                ->where('is_del', 0)
                ->pluck('id');

        checkLogic(!empty($agency_id), 'TMS没有这个'.$batch_number.'的批次的单号,或者TMS状态不允许取消');

        DB::beginTransaction();
        try {
            DB::table($this->table)
                ->where('agency_main_id', $batch_number)
                ->where('is_del', 0)
                ->where('status', 1)
                ->update([
                    'is_del'    =>  1,
                    'remark'    =>  '三方出库取消',
                    'status'    =>  4
                ]);

            DB::table($this->table_detail)
                ->whereIn('agency_id', $agency_id)
                ->update([
                    'is_del'    =>  1,
                    'remark'    =>  '三方出库取消'
                ]);

            DB::commit();
            return true;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * 修改发货日
     * @param $agency_main_id 批次号
     * @param $new_out 新的发货日
     * @return 
     */
    public function saveOutTimeDay($agency_main_id, $new_out, $user_id){
    	
    	$check_agency = DB::table($this->table)->where('agency_main_id', $agency_main_id)->where('is_del', 0)->count();
    	checkLogic($check_agency > 0, '找不到批次号为'.$agency_main_id.'的出库单!');
    	$userInfo = User::getUserDetail($user_id);

    	DB::beginTransaction();
    	try {
    		//修改发货日
    		DB::table($this->table)->where([
    			['agency_main_id', '=', $agency_main_id],
    			['is_del', '=', 0]
    		])->update([
    			'out_ware_day'	=>	$new_out
    		]);

    		// 操作日志
			OperationLog::saveLog(
				$userInfo->org_id,
				$userInfo->login_name,
				'update',
				'修改发货日，批次号为:'.$agency_main_id,
				2
			);
			DB::commit();
			return true;
    	} catch (\PDOException $e) {
    		DB::rollBack();
    		throw $e;
    	}
    }


########################################################. WEB结束 . ###############################################################

    //345
	public function handPullSmOrder($sm_order){

        $agencyInfo = DB::table('t_agency_order as ao')
            ->leftJoin('t_agency_order_detail as aod', 'ao.id', '=', 'aod.agency_id')
            ->where('ao.sm_order', $sm_order)
            ->where('ao.is_del', 0)
            ->where('aod.is_del', 0)
            ->get()
            ->toArray();

        $agencyInfo = object_to_array($agencyInfo);

        checkLogic(!empty($agencyInfo), '找不到出库单为'.$sm_order.'的省代信息');
        $checkLineInfo = DB::table('t_order_line_info')->where('sm_order', $sm_order)->count();

        $insertBoxData = [];
        $insertBoxLineData = [];
        $insertBoxDetailData = [];
        foreach ($agencyInfo as $key => $item) {
            
            $checkBox = DB::table('t_order_box')->where('sm_order', $sm_order)->where('box_number', $item['box_number'])->count();
            checkLogic(!empty(Warehouse::getWarehouseCodeByDealerId($item['dealer_id'])), '找不到deal_id:'.$item['dealer_id'].'对应的仓库编码');
            if ($checkBox <= 0) {
                // 箱子基本信息
                $insertBoxData[] = [
                    'sm_order'  =>  $sm_order,
                    'order_number'  =>  $item['order_number'],
                    'box_number'    =>  $item['box_number'],
                    'destination_code'  =>  Warehouse::getWarehouseCodeByDealerId($item['dealer_id']),
                    'box_type'  =>  'ORDER'
                ];
            }

            if ($checkLineInfo <= 0) {
                $result = curl_post_erpapi('for_tms/get_line_info_by_related_no', ['related_no' => $item['order_number']]);
                checkLogic($result['code'] == 200, 'erp:msg'.$result['msg']);
                checkLogic(!empty($result['data']), 'erp:data'.$result['msg']);
                // 箱子的线路线路信息
                $insertBoxLineData[$sm_order] = [
                    'warehouse_id'  =>  $item['from_warehouse_code'],
                    'sm_order'      =>  $sm_order,
                    'order_number'  =>  $item['order_number'],
                    'line_code'     =>  $result['data']['line_code'],
                    'store_id'      =>  $result['data']['store_id'],
                    'dealer_id'     =>  $result['data']['dealer_id']
                ];
            }

            checkLogic(count(explode('-', $item['box_number'])) == 2 || substr($item['box_number'], 0, 4) == 'RBAP', '箱号格式不正确请检查是否是RBAP*******-***');
            $product = json_decode($item['product_info'], 1);
            if (!empty($product)) {
                foreach ($product as $key => $product_item) {
                    checkLogic(isset($product_item['product_code']), 'product必须是包含product_code');
                    checkLogic(isset($product_item['quantity']), 'product必须是包含quantity');
                    //箱子明细
                    $insertBoxDetailData[$sm_order] = [
                        'out_order' =>  $sm_order,
                        'order_number' =>  $item['order_number'],
                        'box_number' =>  explode('-', $item['box_number'])[0],
                        'product_code' =>  $product_item['product_code'],
                        'quantity' =>  $product_item['quantity'],
                    ];
                }
            }
        }

        DB::beginTransaction();
        try {
            DB::table('t_order_box')->insert($insertBoxData);
            DB::table($this->table)->where('sm_order', $sm_order)->update([
            	'status'	=>	5
            ]);
            if ($checkLineInfo <= 0) {
                DB::table('t_order_line_info')->insert($insertBoxLineData);
            }
            DB::table('t_order_box_detail')->insert($insertBoxDetailData);
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollback();
            throw $e;
        }

        return 'create-box-done';
	}
}