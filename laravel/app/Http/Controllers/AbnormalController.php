<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Jobs\JobHelper;
use App\Jobs\OrderJobs\SaveSmOrder;
use App\Jobs\AgencyJobs\SaveAgencyAfterOut;
use App\Jobs\AgencyJobs\SaveAgencyAfterIn;
use App\Jobs\BoxJobs\CreateAgencyAfterBox;
use App\Models\CenterWarehouse\CollectionArea;
use App\Models\AgencyOrder\AgencyOrder;


class AbnormalController extends SuperController
{   
    // 矫正箱码与子单号的关联
    public function correctBoxExpressNo(){
        
    }

    //矫正分仓合单地址标记
    public function correctOrderAddressMd5(){
    	$order_number = $this->getInput('order_number')->isString()->value();

    	$order_number_arr = json_decode($order_number, 1);
    	checkLogic(is_array($order_number_arr), 'order_number数据格式有误');

    	foreach ($order_number_arr as $order_number) {
	    	$result = curl_post_erpapi('for_tms/get_user_order_info_by_related_no' , ['related_no' => $order_number]);
	        if ($result['code'] != 200) {
	            return hError('MP返回'.$result['msg']);
	        }
	        if (is_string($result['data'])) {
	             return hError('MP返回data:'.$result['data']);
	        }

	        $express_task_merge = md5($result['data']['receiver_mobile'].$result['data']['address']);

	        try {
	        	DB::table('t_agency_order')->where('order_number', $order_number)->where('express_task_merge', '<>', $express_task_merge)->update([
	        		'express_task_merge'	=>	$express_task_merge
	        	]);

	        	echo '订单'.$order_number.'已成功矫正地址'.PHP_EOL;
	        } catch (\PDOException $e) {
	        	throw $e;
	        	
	        }
    	}
    }


    //手动收货结束
    public function manualSaveAgencyOver(){


    	$agency_main_id = $this->getInput('agency_main_id')->isString()->value();
    	$agency_main_id = explode(',', $agency_main_id);

    	// 拒收 向MP推送出库单
    	try {
    		//售后不拒收
    		$reject_model = DB::table('t_agency_order as ao')->where('ao.is_del', 0)->where('ao.order_number', 'not like', 'ASAP%');
    		$recive_model = DB::table('t_agency_order as ao')->where('ao.is_del', 0);

    		// 获取能够修改的省代出库id。 以及拒收的订单号
	    	if (is_array($agency_main_id)) {
	    		$recive_model->whereIn('ao.agency_main_id', $agency_main_id);
	    		$reject_model->whereIn('ao.agency_main_id', $agency_main_id);
	    	} else {
	    		$recive_model->where('ao.agency_main_id', $agency_main_id);
	    		$reject_model->where('ao.agency_main_id', $agency_main_id);
	    	}

	    	// 获取已收货的箱号sku
	    	$recive_list = $recive_model->select('ao.sm_order', 'ao.order_number', 'ao.from_warehouse_code', 'ao.product_info', 'aod.box_number', 'ao.agency_main_id')
	    		->leftJoin('t_agency_order_detail as aod', 'ao.id', 'aod.agency_id')
	    		->get()
	    		->toArray();
	    		
	    	$recive_list = object_to_array($recive_list);
	    	$recive_main_id = array_fu(array_column($recive_list, 'agency_main_id'));
	    	// 同主单箱号归并
	    	$recive_box = [];
	    	$as_box = [];
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

    		foreach ($recive_main_id as $k => $main_id) {
	    		if ($main_id<0) {
	    			unset($recive_main_id[$k]);
	    		}
	    	}

	    	//省代售后出库
	    	foreach ($as_box as $sm_order => $item) {

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

    			$params = [
    				'fwarehouseid186number'		=>		$item['fwarehouseid186number'],
    				'finterfacenumbera186'		=>		$item['finterfacenumbera186'],
    				'box_info'					=>		$item['box_info'],
    				'send_info'					=>		$item['send_info']
    			];

	     		JobHelper::dispatchJob(
					SaveSmOrder::class, $params
				);
    		}

    	} catch (\Exception $e) {
    		throw $e;
    	}

    	return '入库'.count($box_info).'单';
    }
}