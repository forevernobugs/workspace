<?php

namespace App\Jobs\AgencyJobs;

use App\Jobs\BaseJob;
use App\Models\Logs\ApiRequestLog;
use App\Models\AgencyOrder\AgencyOrder;
use App\Models\OrderInfo\ConsignOrder;
/**
 * Created by PhpStorm.
 * User: zhangdahao
 * Date: 2019/1/25
 * Time: 21:27
 */
class SaveAgencyConsignOut extends BaseJob
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
        checkLogic(isset($this->para['related_no']), '参数中未包含related_no');
        
        $related_no = $this->para['related_no'];

        $consign = new ConsignOrder();

        //跟据省代售后单号获取验完车的省代售后信息
        $agency = $consign->getConsignCheckOut($related_no);

        if (is_null($agency)) {
            return 'TMS没有此售后信息'.$related_no;
        }

        //构造参数
        $params = [
            'detail'    =>  json_encode([
                [
                    'as_number' =>  $agency->related_no,
                    'ruigu_code' =>  $agency->sku,
                    'shipped_num' =>  $agency->handover_num
                ]
            ]),
            'shipper'   =>  $agency->handover_user,
            'shipper_mobile'   =>  $agency->handover_mobile
        ];

        $result = curl_post_apsapi('aftersales/internal/transport/shippedByWarehouse', $params);
        //api日志
        ApiRequestLog::saveLog(
            1,
            'SaveAgencyConsignOut',
            env('APS_API_URL','').'aftersales/internal/transport/shippedByWarehouse',
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