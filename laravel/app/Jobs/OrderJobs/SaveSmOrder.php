<?php

namespace App\Jobs\OrderJobs;

use App\Jobs\BaseJob;
use App\Models\Logs\ApiRequestLog;
use App\Models\AgencyOrder\AgencyOrder;
/**
 * Created by PhpStorm.
 * User: zhangdahao
 * Date: 2018/4/19
 * Time: 21:27
 */
class SaveSmOrder extends BaseJob
{
    /**
     * get the queue name
     * to enable auto restart failed task do not change anything but write those:
     * return __CLASS__;
     * @return mixed
     */
    protected function getQueueName()
    {
        return __CLASS__;
        // TODO: Implement getQueueName() method.
    }

    /**
     * Task实际执行的代码，
     * @return mixed 返回false表示任务失败， 返回字符串时将会被记录在return_msg中
     */
    protected function realHandler()
    {
        // TODO: Implement realHandler() method.
        checkLogic(isset($this->para['fwarehouseid186number']), '参数中未包含fwarehouseid186number');
        checkLogic(isset($this->para['finterfacenumbera186']), '参数中未包含finterfacenumbera186');
        checkLogic(isset($this->para['box_info']), 'box_info');
        checkLogic(isset($this->para['send_info']), '参数中未包含send_info');

        // 校验sku
        // foreach ($this->para['send_info'] as $key => $product) {
        // 	checkLogic(isset($product['sku']), 'sku传递有误');
        // 	checkLogic(isset($product['qty']), 'qty传递有误');
        // }
        // checkLogic(isset($product['sku']), '箱号传递有误,不能为空');

        // 构造出库数据
        $box_info = [];
        $flag = 0;
        foreach ($this->para['box_info'] as $box) {
        	// $product = [];
        	// if ($flag === 0) {
        	// 	$product = $this->para['send_info'];
        	// }
			$box_info[] = [
				'box_number' => $box,
				'box_type' => 1
			];        	
        }
        $send_sm_order =[
        	'job_name'					=>	"out_order",
        	'job_data'					=>	[
	        		[
					'fwarehouseid186number'		=>		$this->para['fwarehouseid186number'],
					'finterfacenumbera186'		=>		$this->para['finterfacenumbera186'],
					'fstatevalue623'			=>		'SO_OUT_ED',
					'logistics_pro'				=>		'',
					'logistics_no'				=>		'',
					'operationtime'				=>		hDate(),
					'box_info'					=>		$box_info,
					'send_info'					=>		$this->para['send_info']
				]
			]
		];

		$result = curl_post_erpqueue('/wms_job/do_job', $send_sm_order);

		//api日志
        ApiRequestLog::saveLog(
        	1,
        	'out_order',
        	env('ERP_API_URL','').'/wms_job/do_job',
        	json_encode($send_sm_order),
        	$result['msg']
        );

		if ($result['code'] == 200) {
			$agency = new AgencyOrder();
			$res = $agency->saveAgencyOutFinish($this->para['finterfacenumbera186']);
			if ($res !== true) {
				return '请求mp成功，修改TMS失败'.$this->para['finterfacenumbera186'];
			}
		}

        return $result['msg'];
    }

    /**
     * 添加队列之前验证数据是否正确，本方法接收$this->para
     * @param  array $itemData 本次需要检查的数据
     * @return null /bool 返回null时表示可以执行队列，返回非null将视为数据验证失败
     */
    protected function checkDataError($itemData)
    {
        // TODO: Implement checkDataError() method.
    }
}