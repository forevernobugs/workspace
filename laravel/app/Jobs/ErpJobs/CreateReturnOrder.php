<?php

namespace App\Jobs\ErpJobs;

use App\Jobs\BaseJob;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: zhangdahao
 * Date: 2019/3/27
 * Time: 21:27
 */
class CreateReturnOrder  extends BaseJob
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
        checkLogic(isset($this->para['related_no']), '参数中未包含arelated_no');

        $related_no = $this->para['related_no'];

        $checkAfterInfo = DB::table('t_consign_order_check_out as coco')->where('related_no', $related_no)->first();


        //构造参数
        $params = [
            'order_type'  => 1,
            'sale_order'  => $related_no,
            'skuArr'  => json_encode([
            	'productCode'	=>	$checkAfterInfo->sku,
            	'minUnitQuantity'	=>	$checkAfterInfo->handover_num,
            	'return_quantity'	=>	$checkAfterInfo->handover_num,
            	'good_quantity'	=>	0
            ])
        ];

        $result = curl_post_erpqueue('agent_platform/agent_return', $params);

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