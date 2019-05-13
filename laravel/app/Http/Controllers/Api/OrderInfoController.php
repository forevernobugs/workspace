<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\DirectVerifyController;
use App\Models\BaseModel;
use App\Models\Logs\OperationLog;
use Faker\Provider\Base;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/10
 * Time: 15:35
 */
class OrderInfoController extends DirectVerifyController
{
    /**
     * 当出库单出库之后调用本接口传入订单的线路、装箱信息
     * @return string
     * @throws \Exception
     */
    public function saveOrderLine()
    {
        $delivery_info = $this->getInput('delivery_info')->isArray()->value();
        $warehouseId = $this->getInput('warehouse_id')->isString()->value();
        $smOrder = $this->getInput('sm_order')->isString()->value();
        $orderNumber = $this->getInput('order_number')->isString()->value();
        $lineCode = $this->getInput('line_code')->isString()->value();
        $storeId = $this->getInput('store_id')->isString()->value();
        $outStockTime = $this->getInput('outstock_time')->isDateTime()->value();
        $dealerId = $this->getInput('dealer_id')->isNumeric()->value();
        $boxInfo = $this->getInput('box')->isString()->value();
        $is_urgent = $this->getInput('is_urgent')->isNumeric()->value();
        $is_single = $this->getInput('is_single')->isString()->value('0');
        $weight = $this->getInput('weight')->isString()->value();

        $check = DB::table('t_order_line_info')->where('sm_order', $smOrder)->count();
        $check_box = DB::table('t_order_box')->where('sm_order', $smOrder)->count();
        $check_detail = DB::table('t_order_box_detail')->where('out_order', $smOrder)->count();

        // 不处理已经存在的运单
        if ($check > 0 && $check_box > 0 && $check_detail > 0) {
             return hSucceed('订单已存在，不做任何处理');
        }

        $boxInfo = json_decode($boxInfo, 1);

        checkLogic(is_array($boxInfo), '箱码格式传递不正确！');

        // 判断是否需要创建集货号
        $order_secquence = DB::table('t_date_dealer_secquence_number')
                    ->where('order_number', $orderNumber)
                    ->orderBy('id', 'desc')
                    ->value('order_secquence_number');
        $order_secquence_number = $order_secquence;
        if (empty($order_secquence)) {
            //获取序列号
            $order_secquence_number = DB::table('t_date_dealer_secquence_number')
                        ->where('dete_dealer_prefix', date('ymd').$dealerId)
                        ->orderBy('id', 'desc')
                        ->value('order_secquence_number');
            if (empty($order_secquence_number)) {
                $order_secquence_number = 0;
            }
            $order_secquence_number++;

            // 添加序列号
            DB::beginTransaction();
            try {
                DB::table('t_date_dealer_secquence_number')
                    ->insert([
                        'dete_dealer_prefix'      =>  date('ymd').$dealerId,
                        'order_number'            =>  $orderNumber,
                        'order_secquence_number'  =>  $order_secquence_number,
                    ]);
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                throw $ex;
            }
        }

        $date = hdate();
        $bind_number = date('ymd').$dealerId.'-'.$order_secquence_number;
        $insertingData = [
            'warehouse_id'=>$warehouseId,
            'sm_order'=>$smOrder,
            'order_number'=>$orderNumber,
            'line_code'=>$lineCode,
            'store_id'=>$storeId,
            'outstock_time'=>$outStockTime,
            'create_time'=>$date,
            'dealer_id'=>$dealerId,
            'is_urgent'=>$is_urgent,
            'is_single'=>$is_single,
            'bind_number'=>$bind_number,
            'weight'    =>  $weight
        ];

        $insertingBoxData = [];
        $insertingBoxDetail = [];
        $destination_code = DB::table('t_warehouse_info')->where('dealer_id', $dealerId)->value('warehouse_code');
        checkLogic(!is_null($destination_code), '排车线路分仓找不到');
        $agency_box = [];

        foreach ($boxInfo as $k => $box) {
            checkLogic(isset($box['box_number']), 'box数组中未传入箱号');
            checkLogic(isset($box['detail']) && is_array($box['detail']), 'box数组中未传入明细');

            $insertingBoxData[] = [
                'sm_order' => $smOrder,
                'order_number' => $orderNumber,
                'box_number' => $box['box_number'],
                'create_time' => $date,
                'destination_code' => $destination_code
            ];
            if (substr($box['box_number'], 0, 4) == 'RBAP') {
                $agency = explode('-', $box['box_number'])[0];
                
                if (!in_array($agency, $agency_box)) {
                    foreach ($box['detail'] as $d) {
                        $insertingBoxDetail[] = [
                            'out_order'=>$smOrder,
                            'order_number'=>$orderNumber,
                            'box_number'=>$agency,
                            'product_code'=>$d['sku'],
                            'quantity'=>$d['qty'],
                        ];
                    }
                    $agency_box[] = $agency;
                }
            }else{
                foreach ($box['detail'] as $d) {
                    $insertingBoxDetail[] = [
                        'out_order'=>$smOrder,
                        'order_number'=>$orderNumber,
                        'box_number'=>$box['box_number'],
                        'product_code'=>$d['sku'],
                        'quantity'=>$d['qty'],
                    ];
                }
            }
        }

        DB::beginTransaction();
        try {
            if ($check <= 0) {
                DB::table('t_order_line_info')->insert($insertingData);
            }
            if ($check_box <= 0) {
                DB::table('t_order_box')->insert($insertingBoxData);
            }
            if ($check_detail <= 0) {
                DB::table('t_order_box_detail')->insert($insertingBoxDetail);
            }
            $result = $this->saveDeliveryOrder($delivery_info);
            if (!$result) {
                throw new \Exception("运货单添加失败!", 1);
            }
            DB::commit();
            return hSucceed('done123');
        } catch (\PDOException $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * 当仓管保存装车单之后需要同步到TMS为分仓出库提供数据
     * @return string
     * @throws \Exception
     */
    public function saveConsignOrder()
    {
        $waybillNo = $this->getInput('waybill_no')->isString()->value();
        $carPlate = $this->getInput('car_plate')->isString(false)->value('');
        $warehouseCode = $this->getInput('warehouse_code')->isString()->value();
        $weight = $this->getInput('weight')->isNumeric()->value();
        $volume = $this->getInput('volume')->isNumeric()->value();
        $remark = $this->getInput('remark')->isString()->value('');
        $orderItems = $this->getInput('item')->isString()->value();
        if (is_string($orderItems)) {
            $orderItems = json_decode($orderItems, 1);
        }
        $existedItem = null;
        $check = DB::table('t_consign_order')->where('waybill_no', $waybillNo)->first();

        $carInfo = DB::table('t_car')
            ->where('plate_number', $carPlate)
            ->first();
        if ($check == null) {
            $headData = [
                'waybill_no'=>$waybillNo,
                'car_id'=>$carInfo == null ? 0 : $carInfo->id,
                'driver_id'=>0,
                'weight'=>$weight,
                'volume'=>$volume,
                'waybill_status'=>1,
                'created'=>hdate(),
                'load_time'=>'0000-00-00 00:00:00',
                'warehouse_code'=>$warehouseCode,
                'remark'=>$remark,
                'modified'=>hdate(),
                'createdBy'=>0,
                'driver_name'=>$carInfo == null ? '' : $carInfo->driver_name,
                'contact'=>$carInfo == null ? '' : $carInfo->driver_contact
            ];
        } else {
            $headData = [
                'car_id'=>$carInfo == null ? 0 : $carInfo->id,
                'driver_id'=>0,
                'weight'=>$weight,
                'volume'=>$volume,
                'waybill_status'=>1,
                'warehouse_code'=>$warehouseCode,
                'remark'=>$remark,
                'modified'=>hdate(),
                'driver_name'=>$carInfo == null ? '' : $carInfo->driver_name,
                'contact'=>$carInfo == null ? '' : $carInfo->driver_contact
            ];
            $existedItem = DB::table('t_consign_order_item')
                ->where('waybill_no', $waybillNo)
                ->get();
        }
        #region 准备插入数据
        $consignItem = []; //即将插入的装车单明细
        $tempOutOrder = [];  //临时保存单号
        $updateIndexIds = []; //检查是否需要更新配送顺序
        $time = hdate();
        foreach ($orderItems as $item) {
            checkLogic(isset($item['delivery_number']), '订单明细未传入运货单号');
            checkLogic(isset($item['order_number']), '订单明细未传入客户单号'.$item['delivery_number']);
            checkLogic(isset($item['out_order_no']), '订单明细未传入出库单号'.$item['delivery_number']);
            checkLogic(isset($item['store_id']), '订单明细未传入店铺ID'.$item['delivery_number']);
            checkLogic(isset($item['waybill_index']), '订单明细未传入配送顺序'.$item['delivery_number']);
            checkLogic(isset($item['weight']), '订单明细未传入店铺ID'.$item['delivery_number']);
            checkLogic(isset($item['volume']), '订单明细未传入店铺ID'.$item['delivery_number']);
            if ($existedItem != null) {
                //判断本次传入的数据是否运货单已经存在
                $hasSameItem = $existedItem->first(function ($key, $value) use ($item) {
                    return $item['delivery_number'] == $key->delivery_number;
                });

                //如果已经存在则跳过插入item跳过插入装箱情况
                if ($hasSameItem != null) {
                    if ($hasSameItem->waybill_index != $item['waybill_index']) {
                        $updateIndexIds[] = ['id'=>$hasSameItem->id, 'waybill_index'=>$item['waybill_index']];
                    }
                    continue;
                }
            }

            $tempOutOrder[$item['out_order_no']] = $item['delivery_number'];

            //新增一条装车单明细
            $consignItem[] = [
                'waybill_no'=>$waybillNo,
                'delivery_number'=>$item['delivery_number'],
                'order_number'=>$item['order_number'],
                'out_order_no'=>$item['out_order_no'],
                'waybill_index'=>$item['waybill_index'],
                'store_id'=>$item['store_id'],
                'weight'=>$item['weight'],
                'volume'=>$item['volume'],
                'create_time'=>$time
            ];
        }

        //获取本次新插入数据的装箱信息
        $boxInfo = DB::table('t_order_box')
            ->whereIn('sm_order', array_keys($tempOutOrder))
            ->select('sm_order', 'order_number', 'box_number')
            ->get();

        $insertBoxDetail = [];
        foreach ($boxInfo as $box) {
            $insertBoxDetail[] = [
                'waybill_no'=>$waybillNo,
                'delivery_number'=>$tempOutOrder[$box->sm_order],
                'order_number'=>$box->order_number,
                'box_number'=>$box->box_number,
                'scan_user'=>'',
                'scan_time'=>'',
                'box_status'=>0,
                'out_order_no'=>$box->sm_order,
                'create_time'=>$time,
            ];
        }
        $headData['box_count'] = count($boxInfo);
        #endregion

        #region 准备删除数据
        //当原装车单明细在最新的明细中不存在时表示需要删除，并且需要扣除装箱信息
        $deletingItem = [];
        $checkOrderItems = collect($orderItems);
        if (!empty($existedItem)) {
            foreach ($existedItem as $oldItem) {
                if (!$checkOrderItems->contains(function ($key, $value) use ($oldItem) {
                    return $key['delivery_number'] == $oldItem->delivery_number;
                })) {
                    $deletingItem[] = $oldItem->delivery_number;
                }
            }
        }
        #endregion


        DB::beginTransaction();
        try {
            if ($check == null) {
                DB::table('t_consign_order')->insert($headData);
                DB::table('t_consign_order_item')->insert($consignItem);
                DB::table('t_consign_order_consign_box')->insert($insertBoxDetail);
            } else {
                if (!empty($consignItem)) {
                    //是否有新的明细插入
                    DB::table('t_consign_order_item')->insert($consignItem);
                }
                if (!empty($insertBoxDetail)) {
                    //是否有新的装箱情况插入
                    DB::table('t_consign_order_consign_box')->insert($insertBoxDetail);
                }

                if (!empty($deletingItem)) {
                    //是否有新的装车单明细需要删除
                    DB::table('t_consign_order_item')
                        ->whereIn('delivery_number', $deletingItem)
                        ->delete();
                    DB::table('t_consign_order_consign_box')
                        ->whereIn('delivery_number', $deletingItem)
                        ->delete();
                }
                if (!empty($updateIndexIds)) {
                    BaseModel::updateBatchStatic('t_consign_order_item', $updateIndexIds);
                }
                $newBoxCount = DB::table('t_consign_order_consign_box')
                    ->where('waybill_no', $waybillNo)
                    ->count();
                $weightAndVolume = DB::Table('t_consign_order_item')
                    ->where('waybill_no', $waybillNo)
                    ->select(DB::raw('sum(weight) as w, sum(volume) as v'))
                    ->first();

                $headData['weight'] = $weightAndVolume->w;
                $headData['volume'] = $weightAndVolume->v;
                $headData['box_count'] = $newBoxCount;
                DB::table('t_consign_order')->where('waybill_no', $waybillNo)->update($headData);
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
        return hSucceed('Done');
    }

    /**
     * 当仓管移除某装车单内的运货单时调用
     * @return string
     * @throws \Exception
     */
    public function removeDelivery()
    {
        $waybillNo = $this->getInput('waybill_no')->isString()->value();
        $deliveryNo = $this->getInput('delivery_no')->isString()->value();

        $waybillInfo = DB::table('t_consign_order')
            ->where('waybill_no', $waybillNo)
            ->select('id', 'weight', 'volume', 'waybill_no')
            ->first();

        checkLogic($waybillInfo != null, '未找到指定装车单');

        $deliveryInfo = DB::table('t_consign_order_item')
            ->where('waybill_no', $waybillNo)
            ->where('delivery_number', $deliveryNo)
            ->select('id', 'weight', 'volume', 'waybill_no')
            ->first();

        if ($deliveryInfo == null) {
            return hSucceed('未找到装车单，无需移除');
        }

        checkLogic($deliveryInfo != null, '装车单内未找到指定运货单');

        DB::beginTransaction();
        try {
            DB::table('t_consign_order_item')->where('id', $deliveryInfo->id)->delete();
            DB::table('t_consign_order_consign_box')
                ->where(['waybill_no'=>$waybillNo, 'delivery_number'=>$deliveryNo])
                ->delete();

            $updateData = [
                'weight'=>$waybillInfo->weight - $deliveryInfo->weight,
                'volume'=>$waybillInfo->volume - $deliveryInfo->volume,
            ];

            //如果装车单明细全部被删除则直接取消发货装车单
            $itemCount = DB::table('t_consign_order_item')->where('waybill_no', $waybillNo)->count();
            if ($itemCount == 0) {
                $updateData['waybill_status'] = -1;
            }

            DB::table('t_consign_order')
                ->where('waybill_no', $waybillNo)
                ->update($updateData);
            OperationLog::saveLog(
                0,
                'api',
                'update',
                '出库单车被更新'.$waybillInfo->waybill_no. ' 移除了'.$deliveryNo,
                2
            );
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
        return hSucceed('done');
    }


    /**
     * 取消装车单
     * @return string
     * @throws \Exception
     */
    public function cancelConsignOrder()
    {
        $waybillNo = $this->getInput('waybill_no')->isString()->value();

        $waybillInfo = DB::table('t_consign_order')->where('waybill_no', $waybillNo)->first();

        checkLogic($waybillInfo != null, '未找到装车单');

        DB::beginTransaction();
        try {
            DB::table('t_consign_order')->where('waybill_no', $waybillNo)->update(['waybill_status'=>-1]);

            OperationLog::saveLog(
                0,
                'api',
                'update',
                '出库单车被取消'.$waybillInfo->waybill_no,
                2
            );
            DB::commit();
            return hSucceed('更新成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }


    /**
     * 更新装车单的司机情况
     * @return string
     * @throws \Exception
     */
    public function updateConsignCar()
    {
        $waybillNo = $this->getInput('waybill_no')->isString()->value();
        $carPlate = $this->getInput('plate')->isString()->value();

        $waybillId = DB::table('t_consign_order')->where('waybill_no', $waybillNo)->value('id');

        checkLogic($waybillId != null, '未找到排车信息');
        $carInfo = DB::table('t_car')->where('plate_number', $carPlate)->first();
        if ($carInfo == null) {
            return hSucceed('车辆未找到,暂不更新');
        } else {
            DB::beginTransaction();
            try {
                DB::table('t_consign_order')
                    ->where('id', $waybillId)
                    ->update([
                        'car_id'=>$carInfo->id,
                        'contact'=>$carInfo->driver_contact,
                        'driver_name'=>$carInfo->driver_name,
                        ]);

                OperationLog::saveLog(
                    0,
                    'api',
                    'update',
                    '出库单车辆信息被更改'.$waybillId. ' 新车辆ID：'.$carInfo->id,
                    2
                );
                DB::commit();
                return hSucceed('更新成功');
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }
        }
    }

    /**
     * 设置加急订单
     * @return string
     * @throws \Exception
     */
    public function setOrderUrgent()
    {
        $order_number = $this->getInput('order_number')->isString()->value();

        $is_urgent = DB::table('t_order_line_info')->where('order_number', $order_number)->value('is_urgent');

        checkLogic(!is_null($is_urgent), '订单'.$order_number.'已经出库,但是尚未同步到TMS中,请稍后再试!');

        if ($is_urgent == 1) {
            return hSucceed('订单'.$order_number.',已被设置为加急状态!');
        } else {
            DB::beginTransaction();
            try {
                DB::table('t_order_line_info')
                    ->where('order_number', $order_number)
                    ->update([
                        'is_urgent'=>1
                        ]);

                OperationLog::saveLog(
                    0,
                    'api',
                    'update',
                    '订单'.$order_number.',被改为加急单',
                    2
                );
                DB::commit();
                return hSucceed('已经设为加急');
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }
        }
    }

    /**
     * 将派车设为加急
     * @return string
     * @throws \Exception
     */
    public function setConsignUrgent()
    {
        $delivery_number = $this->getInput('delivery_number')->isString()->value();

        $is_urgent = DB::table('t_consign_order_item')->where('delivery_number', $delivery_number)->value('is_urgent');

        checkLogic(!is_null($is_urgent), '订单'.$delivery_number.'已经排车,但是尚未同步到TMS中,请稍后再试!');

        if ($is_urgent == 1) {
            return hSucceed('运货单订单'.$delivery_number.',已被设置为加急状态!');
        } else {
            DB::beginTransaction();
            try {
                DB::table('t_consign_order_item')
                    ->where('delivery_number', $delivery_number)
                    ->update([
                        'is_urgent'=>1
                        ]);

                OperationLog::saveLog(
                    0,
                    'api',
                    'update',
                    '运货单'.$delivery_number.',被改为加急单',
                    2
                );
                DB::commit();
                return hSucceed('已经设为加急');
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }
        }
    }

    /**
     * 当出库单出库之后调用本接口传入运货单信息
     * @return string
     * @throws \Exception
     */
    public function saveDeliveryOrder(array $delivery_info){
        // 如果运货单为空 不做处理
        if (empty($delivery_info)) {
            return true;
        }

        // 运单初始化
        $delivery_field = [
            'delivery_no',
            'user_id',
            'stock_dc_id',
            'related_no',
            'bind_no',
            'dealer_order_id',
            'detrusion_no',
            'returns_no',
            'waybill_no',
            'waybill_sort',
            'pay_money',
            'pay_money_real',
            'weight',
            'volume',
            'created',
            'modified',
            'confirm_time',
            'assign_time',
            'run_time',
            'verify_time',
            'finish_time',
            'remark', 
            'lat',
            'lng',
            'trade_no',
            'pay_type',
            'is_again',
            'can_delivery',
            'consume_no',
            'reciver_sign_path',
            'sign_photo',
            'need_refund',
            'reserve_time',
            'consign_region_code',
            'box_count',
            'is_again_img',
            'dc_id'
        ];
        foreach ($delivery_info as $k => $item) {
            // 初始化运单基本信息
            $delivery_data[$k]['org_id'] = DB::table('t_warehouse_info')->where('dealer_id', $item['dc_id'])->value('org_id');
            $delivery_data[$k]['deliver_type'] = $item['type'];
            $delivery_data[$k]['deliver_status'] = $item['status'];
            $delivery_data[$k]['province_code'] = $item['location_province_code'];
            $delivery_data[$k]['region_code'] = $item['location_region_code'];
            $delivery_data[$k]['city_code'] = $item['location_city_code'];
            $delivery_data[$k]['prefix'] = $item['location_prefix'];
            $delivery_data[$k]['address'] = $item['location_address'];
            $delivery_data[$k]['consignee'] = $item['location_consignee'];
            $delivery_data[$k]['consignee_mobile'] = $item['location_consignee_mobile'];
            $delivery_data[$k]['consignee_tel'] = $item['location_consignee_tel'];
            $delivery_data[$k]['postcode'] = $item['location_postcode'];

            // 构造数据
            foreach ($delivery_field as $field) {
                $delivery_data[$k][$field] = isset($item[$field]) ? $item[$field] : '';
            }
            $delivery_count = DB::table('t_delivery_order')->where('related_no', $delivery_data[$k]['related_no'])->count();
            if ($delivery_count > 0) {
                unset($delivery_data[$k]);
            }
        }


        // 生成运货单
        DB::beginTransaction();
        try {
            DB::table('t_delivery_order')->insert($delivery_data);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 省代出库
     */
    public function saveAgencyOrder(){
        
        $order_number = $this->getInput('order_number')->isString()->value();
        $sm_order_info = $this->getInput('sm_order')->isString()->value();
        $weight = $this->getInput('weight')->isString()->value();
        $supplier = $this->getInput('supplier')->isString()->value();
        $out_ware_day = $this->getInput('out_ware_day')->isString()->value();
        $agency_main_id = $this->getInput('main_id')->isNumeric()->value();
        $dealer_id = $this->getInput('dealer_id')->isString()->value();
        // $express_task_merge = $this->getInput('express_task_merge')->isString()->value();
        $from_warehouse_code = $this->getInput('from_warehouse_code')->isString()->value();
        $out_time = $this->getInput('out_time')->isString()->value();
        $box = $this->getInput('box')->isString()->value();
        $product_info = $this->getInput('product_info')->isString()->value();

        $box_info = json_decode($box, 1);
        checkLogic(is_array($box_info), '订单明细格式错误');
        checkLogic(!empty($box_info), '箱子不能为空');

        $sm_order_info = json_decode($sm_order_info, 1);
        checkLogic(is_array($sm_order_info), '出库单格式错误');
        checkLogic(!empty($sm_order_info), '出库单不能为空');


        $result = curl_post_erpapi('for_tms/get_user_order_info_by_related_no' , ['related_no' => $order_number]);
        if ($result['code'] != 200) {
            return hError('MP返回'.$result['msg']);
        }
        if (is_string($result['data'])) {
             return hError('MP返回data:'.$result['data']);
        }

        $express_task_merge = md5($result['data']['receiver_mobile'].$result['data']['address']);
        // $check = DB::table('t_agency_order')->where('sm_order', $sm_order)->where('is_del', 0)->count();

        // // 不处理已经存在的运单
        // if ($check > 0) {
        //      return hSucceed('订单已存在，不做任何处理');
        // }


        // 校验sku
        $product_data = json_decode($product_info, 1);
        foreach ($product_data as $product) {
            checkLogic(isset($product['product_code']), 'product_info数组中未传入sku');
            checkLogic(isset($product['quantity']), 'product_info数组中未传入sku的数量');
        }

        //获取集货号
        $bind_number = BaseModel::getBindNumber($order_number, $dealer_id);


        //开始任务
        DB::beginTransaction();
        try {
            foreach ($sm_order_info as $sm_order) {
                $check = DB::table('t_agency_order')->where('sm_order', $sm_order)->where('is_del', 0)->count();

                // 不处理已经存在的运单
                if ($check > 0) {
                     continue;
                }
                //构造数据
                $apency_data = [
                    'order_number'      =>      $order_number,
                    'sm_order'          =>      $sm_order,
                    'weight'            =>      $weight,
                    'supplier'          =>      $supplier,
                    'out_ware_day'      =>      $out_ware_day,
                    'agency_main_id'    =>      $agency_main_id,
                    'product_info'      =>      $product_info,
                    'from_warehouse_code'=>      $from_warehouse_code,
                    'dealer_id'         =>      $dealer_id,
                    'express_task_merge'=>      $express_task_merge,
                    'out_time'          =>      $out_time,
                    'bind_number'       =>      $bind_number
                ];

                $apency_box_data = [];
                if ($check <= 0) {
                    $agency_id = DB::table('t_agency_order')->insertGetId($apency_data);
                    foreach ($box_info as $key => $box) {
                        checkLogic(isset($box['box_number']), 'box数组中未传入箱号');
                        $apency_box_data[] = [
                            'agency_id'         =>      $agency_id,
                            'box_number'        =>      $box['box_number']
                        ];
                    }
                    DB::table('t_agency_order_detail')->insert($apency_box_data);
                }
            }

            DB::commit();
            return hSucceed('done-agency');
        } catch (\PDOException $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
