<?php

namespace App\Jobs\AgencyJobs;

use App\Jobs\BaseJob;
use App\Models\Logs\ApiRequestLog;
use App\Models\AgencyOrder\AgencyOrder;
use App\Models\OrderInfo\ConsignOrder;
use Illuminate\Support\Facades\DB;
/**
 * Created by PhpStorm.
 * User: zhangdahao
 * Date: 2019/2/19
 * Time: 21:27
 */
class SaveAgencyAfterOut extends BaseJob
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
        checkLogic(isset($this->para['aftersales_no']), '参数中未包含aftersales_no');
        checkLogic(isset($this->para['deliver_type']), '参数中未包含deliver_type');
        checkLogic(isset($this->para['box_number']), '参数中未包含box_number');
        checkLogic(isset($this->para['product_code']), '参数中未包含product_code');

        //构造参数
        $params = [
            'aftersales_no'  => $this->para['aftersales_no'], 
            'deliver_type'  => $this->para['deliver_type'],
            'box_number'  => $this->para['box_number'], 
            'product_code'  => $this->para['product_code']
        ];
        $is_express = DB::table('t_agency_order')->where('is_del', 0)->where('order_number', $this->para['aftersales_no'])->value('is_express');
        if ($is_express == 1) {
           return '分仓直发不需要通知OMS';
        }

        $result = curl_post_omsapi('aftersales/create_deliver_order', $params);
        //api日志
        ApiRequestLog::saveLog(
            1, 
            'SaveAgencyConsignOut',
            env('OMS_API_URL','').'aftersales/create_deliver_order',
            json_encode($params),
            $result['msg']
        );
        if ($result['code'] == 200) {
            try {
                DB::table('t_agency_order')->where('is_del', 0)->where('order_number', $this->para['aftersales_no'])->update([
                    'status'    =>  5
                ]);
            } catch (\PDOException $e) {
                return 'OMS'.$result['msg'].'TMS状态修改失败!';
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