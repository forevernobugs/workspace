<?php

namespace App\Jobs\AgencyJobs;

use App\Jobs\BaseJob;
use App\Models\Logs\ApiRequestLog;
use App\Models\AgencyOrder\AgencyOrder;
use App\Models\OrderInfo\ConsignOrder;
/**
 * Created by PhpStorm.
 * User: zhangdahao
 * Date: 2019/2/19
 * Time: 21:27
 */
class saveAgencyAfterOutCustomer  extends BaseJob
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
        checkLogic(isset($this->para['as_number']), '参数中未包含as_number');
        checkLogic(isset($this->para['delivery_number']), '参数中未包含delivery_number');

        //构造参数
        $params = [
            'as_number'  => $this->para['as_number'],
            'delivery_number'  => $this->para['delivery_number']
        ];

        $result = curl_post_apsapi('aftersales/internal/transport/reShippedByWarehouse', $params);
        //api日志
        ApiRequestLog::saveLog(
            1, 
            'saveAgencyAfterOutCustomer',
            env('APS_API_URL','').'aftersales/internal/transport/reShippedByWarehouse',
            json_encode($params),
            $result['msg']
        );
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