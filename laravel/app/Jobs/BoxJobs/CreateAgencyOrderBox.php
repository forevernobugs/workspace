<?php

namespace App\Jobs\BoxJobs;

use App\Jobs\BaseJob;
use App\Models\Logs\ApiRequestLog;
use App\Models\AgencyOrder\AgencyOrder;
use App\Models\OrderInfo\ConsignOrder;
use Illuminate\Support\Facades\DB;
use App\Models\BasicInfo\Warehouse;

/**
 * 创建省代箱号任务
 * Created by PhpStorm.
 * User: zhangdahao
 * Date: 2019/2/21
 * Time: 16:09
 */
class CreateAgencyOrderBox  extends BaseJob
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
    	checkLogic(isset($this->para['sm_order']), '参数中未包含sm_order');
        $sm_order = $this->para['sm_order'];
        // return $sm_order;

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
        // return 'asdf';
        DB::beginTransaction();
        try {
            DB::table('t_order_box')->insert($insertBoxData);

            if ($checkLineInfo <= 0) {
                DB::table('t_order_line_info')->insert($insertBoxLineData);
            }
            DB::table('t_order_box_detail')->insert($insertBoxDetailData);
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollback();
            throw $e;
        }

        return 'create-order-box-done';
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