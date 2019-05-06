<?php
/**
 * Created by Subline.
 * User: zhangdahao
 * Date: 2018/6/5
 * Time: 14:27
 */
namespace App\Http\Controllers\ConsignOrder;

use App\Http\Controllers\LoginRequireController;
use App\Models\BasicInfo\Warehouse;
use App\Models\Logs\AbNormalLog;
use App\Models\OrderInfo\ConsignOrder;
use App\Models\AgencyOrder\AgencyOrder;

class ConsignOrderController extends LoginRequireController {
	//异常记录类型
	const SCAN_TYPE = [
		1 => 'COLLECT_LEAKAGE', //总仓漏扫
		2 => 'TRANSREGIONAL', //串货
		3 => 'REPEAT_OUTBOUND', //重复出库
		4 => 'NOT_LINE', //未排车
	];

	//类型值
	const SCAN_VALUE = [
		'COLLECT_LEAKAGE' => '总仓漏扫',
		'TRANSREGIONAL' => '串货',
		'REPEAT_OUTBOUND' => '重复出库',
		'NOT_LINE' => '未排车',
	];

	//装车单状态
	const WAYBILL_STATUS = [
		1 => '新建',
		2 => '装车中',
		3 => '装车完成',
		4 => '已交货',
		5 => '申请验货',
		6 => '验车中',
		7 => '异常处理中',
		8 => '异常完成',
		9 => '验车完成',
		10 => '已完成',
		-1 => '已取消'
	];

	//分仓排车信息
	public function getBoxInfo(ConsignOrder $model, Warehouse $warehouse) {
		$list = $model->getBoxInfo($this->input);
		$title = [
			'box_number' => '箱码',
			'line_code' => '线路',
			'created' => '派车时间',
			'warehouse_name' => '分拨仓'
		];

		//所有仓库信息
		$warehouse_info = object_to_array($warehouse->getWarehouseAll());
		$list['warehouse'] = $warehouse_info;

		return $this->returnList('加载成功', $list, $title, '菜单信息');
	}

	//分仓收货详单
	public function getDispenseList(ConsignOrder $consign, Warehouse $warehouse) {
		$list = $consign->getDispenseList($this->input);
		$list['title'] = [
			'order_number' => '出库单单号',
			'seal_number' => '封车码',
			'warehouse_name' => '仓库',
			'plate_number' => '车牌号',
			'contact_name' => '联系人',
			'should_count' => '应收箱数',
			'box_count' => '实收箱数',
			'finish_count' => '分仓收已扫箱数',
			'no_finish_count' => '分仓收未扫箱数',
			'leakage_count' => '总仓漏扫箱数',
			'abnormal_count' => '其他异常箱数',
			'revice_time' => '日期',
			'order_type' => '是否漏扫',
		];

		//所有仓库信息
		$warehouse_info = object_to_array($warehouse->getWarehouseAll());
		$list['warehouse'] = $warehouse_info;

		foreach ($list['list'] as $k => $order) {
			$list['list'][$k]['no_finish_count'] = $order['should_count'] - $order['finish_count'];
			$list['list'][$k]['box_count'] = $order['should_count'] + $order['abnormal_count'];
			if ($order['order_type'] == 1) {
				$list['list'][$k]['should_count'] = 0;
				$list['list'][$k]['finish_count'] = 0;
				$list['list'][$k]['box_count'] = 0;
				$list['list'][$k]['no_finish_count'] = 0;
				$list['list'][$k]['leakage_count'] = $order['should_count'];
			}
		}

		//漏扫箱数
		$list['leakage_count'] = array_sum(array_column($list['list'], 'leakage_count'));
		//其他异常箱数
		$list['abnormal_count'] = array_sum(array_column($list['list'], 'abnormal_count'));
		//已收箱数
		$list['finish_count'] = array_sum(array_column($list['list'], 'finish_count'));
		//未收箱数
		$list['no_finish_count'] = array_sum(array_column($list['list'], 'no_finish_count'));
		//到达箱数
		$list['box_count'] = $list['finish_count'] + $list['abnormal_count'] + $list['no_finish_count'] + $list['leakage_count'];
		//应收箱数
		$list['should_count'] = $list['finish_count'] + $list['no_finish_count'];

		return $this->returnList('加载成功', $list, $list['title'], '分仓收货详单');
	}

	//收货单详情
	public function getDispenseDetail(ConsignOrder $consign) {
		$order_number = $this->getInput('order_number')->isString()->value();

		$list['list'] = $consign->getDispenseDetail($order_number, $this->input);

		$list['title'] = [
			'goods_no' => '箱号',
			'scan_status' => '状态',
			'scan_time' => '扫描时间',
			'scan_user' => '扫描人',
			'scan_type' => '异常记录'
		];

		return $this->returnList('加载成功', $list, $list['title'], '分仓收货详情');
	}

	//分仓发货列表
	public function getConsignList(ConsignOrder $consign, Warehouse $warehouse) {
		$list = $consign->getConsignList($this->input);
		$list['title'] = [
			'waybill_no' => '装车单号',
			'warehouse_name' => '分拨仓',
			'line_code' => '派车线路',
			'plate_number' => '车牌号',
			'driver_name' => '司机姓名',
			'contact' => '司机电话',
			'box_count' => '箱数',
			'load_time' => '日期',
		];

		//所有仓库信息
		$warehouse_info = object_to_array($warehouse->getWarehouseAll());
		$list['warehouse'] = $warehouse_info;

		return $this->returnList('加载成功', $list, $list['title'], '分仓发货列表');
	}

	//分仓发货详情
	public function getConsignDetail(ConsignOrder $consign) {
		$waybill_no = $this->getInput('waybill_no')->isString()->value();

		$list['list'] = $consign->getConsignDetail($waybill_no, $this->input);
		$list['title'] = [
			'box_number' => '箱号',
			'box_status' => '状态',
			'scan_time' => '时间',
			'scan_user' => '扫描人',
			'box_normal' => '异常记录'
		];

		return $this->returnList('加载成功', $list, $list['title'], '分仓发货详请');
	}

	//分仓运输统计
	public function getStatisticalInfo(ConsignOrder $consign) {
		$info = $consign->getStatisticalInfo($this->input);
		$info['title'] = [
			'warehouse_code' => '仓库编码',
			'warehouse_name' => '分仓',
			'intos' => '收货箱数',
			'outs' => '发货箱数',
			'dates' => '日期',
		];

		return $this->returnList('加载成功', $info, $info['title'], '分仓统计');
	}

	//分仓运输统计收货信息
	public function getDispenseTransportDetail(ConsignOrder $consign) {
		$warehouse_code = $this->getInput('warehouse_code')->isString()->value();
		$date = $this->getInput('date')->isString()->value();
		$list['list'] = $consign->getDispenseTransportDetail($warehouse_code, $date, $this->input);

		$list['title'] = [
			'order_number' => '收货单号',
			'goods_no' => '箱号',
			'scan_status' => '状态',
			'revice_time' => '收货时间',
			'scan_user' => '收货人',
			'box_normal' => '异常'
		];

		return $this->returnList('加载成功', $list, $list['title'], '分仓统计收货');
	}

	//运输统计分仓发货
	public function getConsignTransportDetail(ConsignOrder $consign) {
		$warehouse_code = $this->getInput('warehouse_code')->isString()->value();
		$date = $this->getInput('date')->isString()->value();
		$list['list'] = $consign->getConsignTransportDetail($warehouse_code, $date, $this->input);
		$list['title'] = [
			'line_code' => '线路',
			'plate_number' => '车牌号',
			'driver_name' => '司机',
			'box_number' => '箱号',
			'box_status' => '状态',
			'scan_time' => '扫描时间',
			'scan_user' => '扫描人',
			'box_normal' => '异常'
		];
		return $this->returnList('加载成功', $list, $list['title'], '分仓统计发货');
	}

	//分仓司机交接表统计
	public function consignDirverList(ConsignOrder $consign, Warehouse $warehouse) {
		$list = $consign->consignDirverList($this->input);

		foreach ($list['list'] as $key => &$value) {
			$scan_user = array_filter(explode(',', $value['scan_user']));
			$value['scan_user'] = isset($scan_user[0]) ? $scan_user[0] : '';
		}

		$list['title'] = [
			'warehouse_name' => '分拨仓',
			'created' => '创建日期',
			'waybill_no' => '装车单号',
			'driver_name' => '司机',
			'scan_user' => '仓管姓名',
			'should_order' => '应发单数',
			'real_order' => '实发单数',
			'should_box' => '应发箱数',
			'real_box' => '实发箱数'
		];

		//所有仓库信息
		$warehouse_info = object_to_array($warehouse->getWarehouseAll());
		$list['warehouse'] = $warehouse_info;
		return $this->returnList('加载成功', $list, $list['title'], '分仓司机交接表统计');
	}

	//箱子异常日志
	public function abNormalList(AbNormalLog $model, Warehouse $warehouse) {
		$list = $model->getAbNormalList($this->input);

		foreach ($list['list'] as $k => $scan_val) {
			$list['list'][$k]['scan_type'] = self::SCAN_VALUE[$scan_val['scan_type']];
		}

		$list['title'] = [
			'warehouse_name' => '分拨仓',
			'box_number' => '箱号',
			'scan_user' => '扫描人',
			'scan_time' => '扫描时间',
			'related_order' => '关联单号',
			'scan_type' => '异常记录类型',
		];

		$list['scan_arr'] = self::SCAN_VALUE;
		//所有仓库信息
		$warehouse_info = object_to_array($warehouse->getWarehouseAll());
		$list['warehouse'] = $warehouse_info;

		return $this->returnList('加载成功', $list, $list['title'], '异常记录');
	}

	/**
	 * 验车列表
	 */
	public function checkConsignList(ConsignOrder $consign){

		//获取装车单状态
		$waybill_status_arr = self::WAYBILL_STATUS;
		$list = $consign->checkConsignList($this->input);
		$list['waybill_status_arr'] = $waybill_status_arr;
		foreach ($list['list'] as $key => $item) {
			$list['list'][$key]['waybill_status_name'] = isset($waybill_status_arr[$item['waybill_status']]) ? $waybill_status_arr[$item['waybill_status']] : '';
		}

		$list['title'] = [
			'waybill_no' => '装车单号',
			'plate_number' => '车牌号',
			'driver_name' => '司机',
			'apply_time' => '申请时间',
			'cash_money' => '现金收款',
			'waybill_status_name' => '状态'
		];

		return $this->returnList('加载成功', $list, $list['title'], '验车列表');
	}

	/**
	 * 验车详情
	 */
	public function checkConsignDetail(ConsignOrder $consign){

		$waybill_no = $this->getInput('waybill_no')->isString()->value();

		$consign_info = $consign->getConsignInfo($waybill_no, $this->input['user_id']);

		//获取验车各个明细
        $check_again = $consign->getCheckList($waybill_no, 'AGAIN', $this->user_id ? $this->user_id : 1);
        $check_all = $consign->getCheckList($waybill_no, 'ALL_REJECT', $this->user_id ? $this->user_id : 1);
        $check_part = $consign->getCheckList($waybill_no, 'PART_REJECT', $this->user_id ? $this->user_id : 1);
        $check_after = $consign->getCheckList($waybill_no, 'AFTER', $this->user_id ? $this->user_id : 1);
       	
       	$check_again = $this->getCheckDetail($check_again, 'AGAIN');
       	$check_all = $this->getCheckDetail($check_all, 'ALL_REJECT');
       	$check_part = $this->getCheckDetail($check_part, 'PART_REJECT');
       	$check_after = $this->getCheckDetail($check_after, 'AFTER');

       	$check_data = [
       		'AGAIN'	=>	$check_again,
       		'ALL_REJECT'	=>	$check_all,
       		'PART_REJECT'	=>	$check_part,
       		'AFTER'	=>	$check_after
       	];

        $consign_info->check_data = $check_data;

        $consign_info->waybill_status_arr = self::WAYBILL_STATUS;
        $consign_info = object_to_array($consign_info);

        return hSucceed('', $consign_info);
	}

	/**
	 * 处理验车详情
	 * @param object $[check_data] 验车明细
	 */
	public function getCheckDetail($check_data, $check_type){
		$check_list = [];
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
                'check_user' => $v['check_user'],
                'check_time' => $v['check_time'],
                'is_again_time' => $v['is_again_time'],
                'is_check' => $v['is_check'],
            ];
        }

        return $check_list;
	}

	/**
	 * 处理验货异常
	 * @DateTime  2018-11-19
	 * @return
	 */
	public function checkConsignData(ConsignOrder $consign){
		$waybill_no = $this->getInput('waybill_no')->isString()->value();

		$consign_info = $consign->getConsignInfo($waybill_no, $this->input['user_id']);

		if ($consign_info->waybill_status != 7) {
			return hError('装车单状态只允许对异常处理中作异常处理');
		}

		//获取验车各个明细
        $check_again = $consign->getCheckList($waybill_no, 'AGAIN', $this->user_id ? $this->user_id : 1);
        $check_all = $consign->getCheckList($waybill_no, 'ALL_REJECT', $this->user_id ? $this->user_id : 1);
        $check_part = $consign->getCheckList($waybill_no, 'PART_REJECT', $this->user_id ? $this->user_id : 1);
        $check_after = $consign->getCheckList($waybill_no, 'AFTER', $this->user_id ? $this->user_id : 1);
       	
       	$check_again = $this->getCheckDetail($check_again, 'AGAIN');
       	$check_all = $this->getCheckDetail($check_all, 'ALL_REJECT');
       	$check_part = $this->getCheckDetail($check_part, 'PART_REJECT');
       	$check_after = $this->getCheckDetail($check_after, 'AFTER');

       	$check_data = [
       		'AGAIN'	=>	$check_again,
       		'ALL_REJECT'	=>	$check_all,
       		'PART_REJECT'	=>	$check_part,
       		'AFTER'	=>	$check_after
       	];

        $consign_info->check_data = $check_data;

        $consign_info->waybill_status_arr = self::WAYBILL_STATUS;
        $consign_info = object_to_array($consign_info);
  
        return hSucceed('', $consign_info);
	}

	/**
	 * @DateTime  2018-11-19
	 * @param     string $waybill_no [装车单号]
	 * @param     string $real_num [实收数]
	 * @return    result
	 */
	public function doConsignData(ConsignOrder $consign){

		$delivery_no = $this->getInput('delivery_no')->isString()->value();
		$check_number = $this->getInput('check_number')->isString()->value();
		$real_num = $this->getInput('real_num')->isString()->value();
		$change_num = $this->getInput('change_num')->isString()->value();
		$user_name = $this->getUserName();
		
		if ($consign->doConsignData($delivery_no, $check_number, $real_num, $change_num, $this->user_id, $user_name)) {
			return hSucceed('执行成功');
		}
		
		return hError('执行失败');
	}

	/**
     * 验车完成 并 同步MP
     * @DateTime  2018-11-08
     * @copyright [copyright]
     * @param     string waybill_no 装车单号
     * @param     string waybill_status 装车单状态
     * @return    [result]           
     */
    public function checkNormalFinish(ConsignOrder $consign){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $check_data = $consign->checkConsignData($waybill_no);

        $waybill_status = 7;
        if ($check_data === true) {
        	$waybill_status = 6;
        }

        $num = $consign->checkConsignFinish($waybill_no, $waybill_status, $this->user_id, $this->getUserName());

        if (!is_numeric($num)) {
            return hError($num);
        }
       
        return hSucceed('验车完成:共入库商品'.$num.'件');
    }

    /**
     * 缺失商品
     * @DateTime  2018-11-19
     * @return   array
     */
    public function loseConsignProduct(ConsignOrder $consign){

    	$list = $consign->loseConsignProduct($this->input);

    	foreach ($list['list'] as $k => $item) {
    		$list['list'][$k]['lose_num'] = $item['plan_num'] - $item['real_num'];
    		$list['list'][$k]['check_number'] = !empty($item['split_sku']) ? $item['split_sku'] : $item['box_number'];
    		$list['list'][$k]['unit_price'] = ($item['unit_price'] == '0.00' || empty($item['unit_price'])) ? '--' : $item['unit_price'];
    	}

    	$list['title'] = [
			'waybill_no' => '装车单号',
			'related_no' => '关联单号',
			'delivery_no' => '运货单号',
			'plate_number' => '车牌号',
			'car_name' => '司机',
			'check_number'=> '箱号/SKU',
			'apply_time' => '申请时间',
			'lose_num' => '缺失商品数量',
			'unit_price' => '金额/(单价)'
		];

    	return $this->returnList('加载成功', $list, $list['title'], '缺失商品');
    }

    /**
     * 缺失商品补货
     * @DateTime  2018-11-20
     * @return   bool
     */
    public function productReplenish(ConsignOrder $consign){
    	$waybill_no = $this->getInput('waybill_no')->isString()->value();
    	$delivery_no = $this->getInput('delivery_no')->isString()->value();
		$check_number = $this->getInput('check_number')->isString()->value();
		$num = $this->getInput('num')->isString()->value();
		$result = $consign->productReplenish($waybill_no, $delivery_no, $check_number, $num, $this->user_id, $this->getUserName());
		if($result !== true){
			return hError('执行失败'.$result);
		}

		return hSucceed('执行成功');
    }

    /**
     * 缺失商品赔付
     * @DateTime  2018-11-20
     * @return   bool
     */
    public function productCompensate(ConsignOrder $consign){
    	$delivery_no = $this->getInput('delivery_no')->isString()->value();
		$check_number = $this->getInput('check_number')->isString()->value();
		$price = $this->getInput('price')->isString()->value();
		$result = $consign->productCompensate($delivery_no, $check_number, $price, $this->user_id, $this->getUserName());
		if($result !== true){
			return hError('执行失败');
		}

		return hSucceed('执行成功');
    }

    /**
     * 省代拒收
     */
    public function getAgencyRejectList(AgencyOrder $agency, Warehouse $warehouse){
    	$list = $agency->getAgencyRejectList($this->input);
    	$title = [
			'order_number' => '子单号',
			'supplier' => '供应商名称',
			'plan_box_num' => '打包箱数',
			'real_box_num' => '分仓收到箱数',
			'status'	=>	'状态',
			'operation_status'	=>	'分仓操作'
		];

		$list['warehouse'] = $warehouse;

		return $this->returnList('加载成功', $list, $title, '三方拒收情况');
    }
}