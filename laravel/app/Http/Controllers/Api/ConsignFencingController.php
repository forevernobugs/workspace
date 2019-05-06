<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\DirectVerifyController;
use App\Http\Controllers\MpApiController;
use App\Models\BasicInfo\ConsignFencing;
use App\Models\OrderInfo\ConsignOrder;
use App\Models\AgencyOrder\AgencyOrder;

/**
 * Created by subline3.
 * User: zhangdahao
 * Date: 2018/6/1
 * Time: 15:29
 */
class ConsignFencingController extends DirectVerifyController
{
    /**
     * 当MP系统发生线路新增 线路时 调用此接口 同步线路
     * @Author    zhangdahao
     * @DateTime  2018-06-01
     * @return string
     * @throws \Exception
     */
    public function insertConsignFencing(ConsignFencing $model)
    {
        $data['dealer_id'] = $this->getInput('dealer_id')->isNumeric()->value();
        $data['created'] = $this->getInput('created')->isString()->value();
        $data['created_by'] = $this->getInput('created_by')->isString()->value();
        $data['reg_code'] = $this->getInput('reg_code')->isString()->value();
        $data['enabled'] = $this->getInput('enabled')->isString()->value('0');
        $data['fencing'] = $this->getInput('fencing')->isString()->value();
        $result = $model->saveConsignFencing($data);
        if ($result) {
            return hSucceed('done');
        }
    }

    /**
     * 当MP系统发生线路修改 线路时 调用此接口 同步线路
     * @Author    zhangdahao
     * @DateTime  2018-06-04
     * @return string
     * @throws \Exception
     */
    public function updateConsignFencing(ConsignFencing $model)
    {
        $data['id'] = $this->getInput('id')->isString()->value();
        $data['reg_code'] = $this->getInput('reg_code')->isString()->value();
        $data['enabled'] = $this->getInput('enabled')->isString()->value('0');
        $data['fencing'] = $this->getInput('fencing')->isString()->value();

        $result = $model->saveConsignFencing($data);
        if ($result) {
            return hSucceed('done');
        }
    }

    /**
     * 当MP系统发生线路删除 线路时 调用此接口 同步线路
     * @Author    zhangdahao
     * @DateTime  2018-06-04
     * @param     ConsignFencing $model [description]
     * @return string
     * @throws \Exception
     */
    public function deleteConsignFencting(ConsignFencing $model)
    {
        $reg_code = $this->getInput('reg_code')->isNumeric()->value();
        $result = $model->deleteConsignFencting($reg_code);
        if ($result) {
            return hSucceed('done');
        }
    }


    /**
     * 当MP系统发生线路删除 线路时 调用此接口 同步线路
     * @Author    zhangdahao
     * @DateTime  2018-06-04
     * @param     ConsignFencing $model [description]
     * @return string
     * @throws \Exception
     */
    public function checkBackConsign(ConsignOrder $model, MpApiController $controller){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $user_name = $this->getInput('user_name')->isString()->value();
        $waybill = $model->getCarInfoByWaybill($waybill_no);
        if (empty($waybill)) {
            return hError('找不到装车单:'.$waybill_no);
        }
        //验证tms是否验车
        $consignCheck =$model->getConsignCheck($waybill_no);
        if (!empty($consignCheck)) {
           $check_res = $model->checkBackWaybillAll($waybill_no, $user_name, 'MP');
        } else {
            //同步验车信息到tms
            $result = $controller->getCheckConsignWayillInfo($waybill_no);
            if($result['code'] == 200){
                $check_res = $model->checkBackWaybillAll($waybill_no, $user_name, 'MP');
            } else {
                return hError($result['msg']);
            }
        }
        if ($check_res !== true) {
            return hError($check_res);
        } else {
            return hSucceed('done');
        }
    }

    /**
     * 装车单详情
     * @param MpApiController $mpApiController
     * @param string waybill_no 装车单号
     * @param string check_type 商品列表类型
     * @return string
     * @throws \App\Exceptions\ApiParaException
     * @throws \App\Exceptions\KnownLogicException
     */
    public function consignInfo(MpApiController $mpApiController){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $check_type = $this->getInput('check_type')->isString()->value();
        $result = $mpApiController->consignInfo($waybill_no,$check_type);
        if ($result['code'] == 200) {
            return hSucceed('查询完成',$result['data']);
        }else{
            return hError($result['msg']);
        }
    }

    /**
     * 装车单详情  保存最后一次修改将数据写入tms
     * @param MpApiController $mpApiController
     * @param string waybill_no 装车单号
     * @param string check_type 商品列表类型
     * @return string
     * @throws \App\Exceptions\ApiParaException
     * @throws \App\Exceptions\KnownLogicException
     */
    public function getCheckConsignWayillInfo(MpApiController $mpApiController){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $result = $mpApiController->getCheckConsignWayillInfo($waybill_no);
        if ($result['code'] == 200) {
            return hSucceed('查询完成',$result['data']);
        }else{
            return hError($result['msg']);
        }
    }


    /**
     * 装车单详情  实时查询不写入tms
     * @param MpApiController $mpApiController
     * @param string waybill_no 装车单号
     * @param string check_type 商品列表类型
     * @return string
     * @throws \App\Exceptions\ApiParaException
     * @throws \App\Exceptions\KnownLogicException
     */
    public function getCheckWayillInfo(MpApiController $mpApiController, ConsignOrder $model){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $check_info =$model->getCheckConsignDetail($waybill_no);
        if (!empty($check_info)) {
            return hSucceed('查询完成', $check_info);
        }
        $result = $mpApiController->getCheckWayillInfo($waybill_no);
        if ($result['code'] == 200) {
            return hSucceed('查询完成',$result['data']);
        }else{
            return hError($result['msg']);
        }
    }

    /**
     * 查看商品详情
     * @param MpApiController $mpApiController
     * @param string delivery_no 运货单号
     * @param string check_type 类型
     * @param string product_code 产品编码
     * @return string
     * @throws \App\Exceptions\ApiParaException
     * @throws \App\Exceptions\KnownLogicException
     */
    public function productInfo(MpApiController $mpApiController){
        $check_type = $this->getInput('check_type')->isString()->value();
        $delivery_no = $this->getInput('delivery_no')->isString()->value();
        $search_code = $this->getInput('search_code')->isString()->value();

        $result = $mpApiController->getProductInfoDetail($check_type, $delivery_no, $search_code);
        if ($result['code'] !== 200) {
            return hError($result['msg']);
        }
        return hSucceed('查询完成', $result['data']);


        // $result = $mpApiController->productInfo($check_type, $delivery_no, $search_code);
        // if ($result['code'] == 200) {
        //     return hSucceed('查询完成', $result['data']);
        // } else {
        //     return hError($result['msg']);
        // }
    }




    /**
     * CRM提交装车单修改装车单状态 为 5 申请验车
     * @DateTime  2018-11-12
     * @param     string waybill_no 装车单号
     * @return    [result]    
     */
    public function changConsignStatus(ConsignOrder $consignOrder, MpApiController $mpApiController){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $waybill_status = $this->getInput('waybill_status')->isString()->value('5');

        if($waybill_status == 5){
            $res = $mpApiController->getCheckConsignWayillInfo($waybill_no);
            if ($res['code'] != 200) {
                return hError($res['msg']);
            }
        }

        // 申请验货
        $result = $consignOrder->saveConsignDate($waybill_no, ['waybill_status' => $waybill_status]);
        if ($result !== true) {
            return hError($result);
        }
        
        return hSucceed('执行成功');
    }

    /**
     * 设置已发车
     */
    public function setDepart(ConsignOrder $consignOrder){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $result = $consignOrder->saveConsignDateByWaybill_no($waybill_no, ['waybill_status' => 12]);
        if ($result !== true) {
            return hError($result);
        }
        
        return hSucceed('执行成功');
    }

    /**
     * CRM司机提交回仓验车
     * @DateTime  2018-11-12
     * @param     string waybill_no 装车单号
     * @return    [result]    
     */
    // public function pullCheckList(MpApiController $mpApiController){
    //     $waybill_no = $this->getInput('waybill_no')->isString()->value();
    //     $result = $mpApiController->getCheckConsignWayillInfo($waybill_no);

    //     if ($result['code'] !== 200) {
    //         return hError($result['msg']);
    //     }
    //     return hSucceed('执行成功');
    // }
    

    /**
     * 查看商品
     */
    public function getProductInfoDetail(MpApiController $mpApiController){

        $check_type = $this->getInput('check_type')->isString()->value();
        $delivery_no = $this->getInput('delivery_no')->isString()->value();
        $search_code = $this->getInput('search_code')->isString()->value();
        $result = $mpApiController->getProductInfoDetail($check_type, $delivery_no, $search_code);
        if ($result['code'] !== 200) {
            return hError($result['msg']);
        }
        return hSucceed('查询完成', $result['data']);
    }

    /**
     * 获取验货装车单
     */
    public function getCheckConsignInfoDetail(ConsignOrder $consignOrder, MpApiController $mpApiController){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        //读表
        $waybill_detail = $consignOrder->getCheckConsignDetail($waybill_no);
        $waybill_info = $consignOrder->getCarInfoByWaybill($waybill_no);
        if (empty($waybill_info)) {
            return hError('TMS装车单不存在，请联系管理员!');
        }
        if (empty($waybill_detail) && !in_array($waybill_info->waybill_status, ['5','6','7','8','9'])) {
            // 从mp拉取数据
            $waybill_detail = $mpApiController->getCheckWayillInfo($waybill_no);
            $check_info = $waybill_detail['data'];
            if (!empty($waybill_detail['data'])) {
                $check_info['apply_time'] = '';
                $back_data = $check_info['back_consign'];
                $check_info['remark'] = '';
                unset($check_info['back_consign']);
            }
        }else{
            $check_info = [
                'waybill_no'        =>      $waybill_info->waybill_no,
                'real_money'        =>      $waybill_info->real_money,
                'cash_money'        =>      $waybill_info->cash_money,
                'executed_count'    =>      $waybill_info->executed_count,
                'driver_name'       =>      $waybill_info->car_name,
                'apply_time'        =>      $waybill_info->apply_time,
                'plate_number'      =>      $waybill_info->plate_number,
                'remark'            =>      $waybill_info->remark
            ];
            $back_data = $waybill_detail;
        }
        // 格式化数据
        $check_data = $consignOrder->dealBackData($back_data);
        $check_info = array_merge($check_info, $check_data['waybill_data']);
        $check_info['back_data'] = $check_data['check_info'];

        return hSucceed('执行成功', $check_info);
    }

    /**
     * 三方出库取消
     * @param $batch_number 批次号
     * @return
     */
    public function cancelAgencyOrder(AgencyOrder $agency){
        $batch_number = $this->getInput('batch_number')->isString()->value();

        $result = $agency->cancelAgencyOrder($batch_number);
        
        if ($result === true) {
            return hSucceed('done');
        }

        return hError('error:'.$result);

    }
}
