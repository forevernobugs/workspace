<?php

namespace App\Jobs\OrderJobs;

use App\Jobs\BaseJob;
use App\Models\Logs\ApiRequestLog;
use App\Models\AgencyOrder\AgencyOrder;
use App\Models\OrderInfo\ConsignOrder;
use Illuminate\Support\Facades\DB;
/**
 * Created by PhpStorm.
 * User: zhangdahao
 * Date: 2019/1/25
 * Time: 21:27
 */
class SaveAgencyConsign extends BaseJob
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
        checkLogic(isset($this->para['agency_after_no']), '参数中未包含agency_after_no');
        
        $agency_after_no = $this->para['agency_after_no'];

        $consign = new ConsignOrder();



        //跟据省代售后单号获取验完车的省代售后信息
        $agency = $consign->getCheckConsignByRelated($agency_after_no);

        $agency_express = DB::table('t_do_goods')->where('related_no', $agency_after_no)->first();

        if (!is_null($agency)) {
            //构造参数
            $params = [
                'as_number'  => $agency->related_no, 
                'sku'  => $agency->split_sku,
                'plan_num'  => $agency->plan_num, 
                'real_num'  => $agency->real_num,
                'delivery_number'  => $agency->delivery_no
            ];
        }else if(!is_null($agency_express)){
            //构造参数
            $params = [
                'as_number'  => $agency_express->related_no, 
                'sku'  => $agency_express->sku,
                'plan_num'  => $agency_express->apply_number, 
                'real_num'  => $agency_express->real_number,
                'delivery_number'  => $agency_express->carrier_number
            ];
        }else{
            return 'TMS没有此售后信息：'.$agency_after_no;
        }
        
        $result = curl_post_apsapi('aftersales/internal/transport/receiptByWarehouse', $params);
        //api日志
        ApiRequestLog::saveLog(
            1,
            'SaveAgencyConsign',
            env('APS_API_URL','').'aftersales/internal/transport/receiptByWarehouse',
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