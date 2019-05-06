<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/17
 * Time: 15:14
 */

namespace App\Http\Controllers\Mobile\V1_0;

use App\Http\Controllers\MobileApiController;
use App\Http\Controllers\MpApiController;
use App\Models\OrderInfo\ConsignOrder;
use App\Models\Permission\Role;

class ConsignOrderController extends MobileApiController
{
    public function listConsignOrder(ConsignOrder $consignOrder)
    {
        $data = $consignOrder->listConsignOrder($this->input);
        checkLogic(!empty($data), '无数据');
        return hSucceed('', $data);
    }

    public function getConsignOrderDetail(ConsignOrder $consignOrder)
    {
        $waybillNo = $this->getInput('waybill_no')->isString()->value();
        $data = $consignOrder->getConsignOrderDetail($waybillNo);
        return hSucceed('', $data);
    }

    public function verifyWaybillBox(ConsignOrder $consignOrder)
    {
        $waybillNo = $this->getInput('waybill_no')->isString()->value();
        $boxNumber = $this->getInput('box_number')->isString()->value();
        $remark = $consignOrder->verifyWaybillBox($this->user_id, $waybillNo, $boxNumber);
        if (is_array($remark)) {
            return hSucceed('验证成功'.$remark['msg']);
        }
        return hError($remark);
    }

    public function finishLoadConsign(ConsignOrder $consignOrder)
    {
        $waybillNo = $this->getInput('waybill_no')->isString()->value();
        $consignOrder->finishLoadConsign($this->user_id, $waybillNo);
        return hSucceed('装车完成');
    }

    /**
     * @param     [string]      $waybill_no [分仓出库单号]
     * @param     [string]      $box_number [箱号]
     * @return    [type]                  [description]
     */
    public function consignBoxCheck(ConsignOrder $consignOrder)
    {
        $box_number = $this->getInput('box_number')->isString()->value();
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $user_id = $this->input['user_id'];

        $result = $consignOrder->consignBoxCheck($waybill_no, $box_number, $user_id);
        
        if ($result === true) {
            //构造数据
            $boxNumber = json_encode([$box_number]);
            $res = $consignOrder->verifyWaybillBox($this->user_id, $waybill_no, $boxNumber);
            if (is_array($res)) {
                return hSucceed('');
            } else {
                return hSucceed('', ['box_number' => $box_number,'msg' => $res]);
            }
        } else {
            return hSucceed('', ['box_number' => $box_number,'msg' => $result]);
        }
    }
    /**
     * 扫描箱号获取改送日期
     * @param \App\Http\Controllers\MpApiController $mpApiController
     * @param string box_number 箱号
     * @return string
     * @throws \App\Exceptions\ApiParaException
     */
    public function verifyBoxBack(MpApiController $mpApiController)
    {
        $box_number = $this->getInput('box_number')->isString()->value();
        $result = $mpApiController->verifyBoxBack($box_number);
        if ($result['code'] == 200) {
            return hSucceed('查询完成',$result['data']);
        }else{
            return hError($result['msg']);
        }
    }
    /**
     * 获取验货单列表接口
     * @param \App\Http\Controllers\MpApiController $mpApiController
     * @param string  user_id  用户ID
     * @return string
     */
    public function consignOrderList(MpApiController $mpApiController){
        $param = $this->input;
        $result = $mpApiController->consignOrderList($this->user_id,$param);
        if ($result['code'] == 200) {
            return hSucceed('查询完成',$result['data']);
        }else{
            return hError($result['msg']);
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
        // var_dump($result);die;
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
    public function getProductInfo(MpApiController $mpApiController){
        $check_type = $this->getInput('check_type')->isString()->value();
        $delivery_no = $this->getInput('delivery_no')->isString()->value();
        $search_code = $this->getInput('search_code')->isString()->value();


        $result = $mpApiController->getProductInfoDetail($check_type, $delivery_no, $search_code);
        if ($result['code'] == 200) {
            return hSucceed('查询完成', $result['data']);
        } else {
            return hError($result['msg']);
        }
    }

    /**
     * 快速验车
     * @param string waybill_no 装车单号
     * @return
     */
    public function quickCheckConsign(ConsignOrder $consignOrder, MpApiController $mpApiController){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        if (Role::checkUserRole($this->user_id, 'DISTRIBUTION_MANAGER') == false) {
            return hError('当前用户不是分仓仓管用户');
        }
        $user_name = $this->getUserName();
        $result = $consignOrder->checkBackWaybillAll($waybill_no, $user_name);

        if ($result !== true) {
            return hError($result);
        }
        return hSucceed('验车成功');
    }
    /**
     * 验货扫描详情
     * @param MpApiController $mpApiController
     * @param string waybill_no 装车单号
     * @param string verify_code 验货码
     * @return string
     * @throws \App\Exceptions\ApiParaException
     * @throws \App\Exceptions\KnownLogicException
     */
    public function verifyBack(MpApiController $mpApiController, ConsignOrder $consignOrder)
    {
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $verify_code = $this->getInput('verify_code')->isString()->value();

        $verify_num = 0;
        $fix_code = substr($verify_code, 0, 2);

        checkLogic(in_array($fix_code, ['RB', 'PR', 'AS']), '未识别的扫码格式-'.$verify_code);

        if ($fix_code != 'RB') {
            $verify_num = $this->getInput('verify_num')->isString()->value();
        }

        if ($fix_code == 'PR') {
            $verify = explode('-', $verify_code);
            $verify_code = substr($verify[0], 2);
        }
        $result = $mpApiController->verifyBack($waybill_no,$verify_code, $verify_num);
        if ($result['code'] == 200) {
            return hSucceed('查询完成',$result['data']);
        }else{
            return hError($result['msg']);
        }
    }

    /**
     * 核实验车数据是否存在异常
     * @DateTime  2018-11-07
     * @copyright [copyright]
     * @param     string waybill_no 装车单号
     * @return    [result]           
     */
    public function checkConsignData(ConsignOrder $consignOrder){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $result = $consignOrder->checkConsignData($waybill_no);
        if ($result !== true) {
            return hSucceed($result);
        }
        $num = $consignOrder->checkConsignFinish($waybill_no, 9, $this->user_id, $this->getUserName());
        if (!is_numeric($num)) {
            return hError($num);
        }
        return hSucceed('验车完成:共入库商品'.$num.'件');
    }

    /**
     * 验车完成 并 同步MP
     * @DateTime  2018-11-08
     * @copyright [copyright]
     * @param     string waybill_no 装车单号
     * @param     string waybill_status 装车单状态
     * @return    [result]           
     */
    public function checkConsignFinish(ConsignOrder $consignOrder){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $waybill_status = $this->getInput('waybill_status')->isString()->value();
        $num = $consignOrder->checkConsignFinish($waybill_no, $waybill_status, $this->user_id, $this->getUserName());
        if (!is_numeric($num)) {
            return hError($num);
        }
        return hSucceed('验车完成:共入库商品'.$num.'件');
    }

    /**
     * 获取验货明细
     */
    public function getCheckConsignInfo(ConsignOrder $consignOrder){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        //读表
        $waybill_detail = $consignOrder->getCheckConsignDetail($waybill_no);
        // 获取装车单基本信息
        $waybill_info = $consignOrder->getCarInfoByWaybill($waybill_no);
        if ($waybill_info->waybill_status == 5) {
            $result = $consignOrder->saveConsignDateByWaybill_no($waybill_no, ['waybill_status' => 6]);
            if ($result !== true) {
                return hError($result);
            }
        }
        $check_info = [
            'waybill_no'        =>      $waybill_info->waybill_no,
            'waybill_status'    =>      $waybill_info->waybill_status,
            'real_money'        =>      $waybill_info->real_money,
            'cash_money'        =>      $waybill_info->cash_money,
            'executed_count'    =>      $waybill_info->executed_count,
            'driver_name'       =>      $waybill_info->car_name,
            'apply_time'        =>      $waybill_info->apply_time,
            'plate_number'      =>      $waybill_info->plate_number,
            'remark'            =>      $waybill_info->remark
        ];
        $back_data = $waybill_detail;
        // 格式化验车数据
        $check_data = $consignOrder->dealBackData($back_data);
        $check_info = array_merge($check_info, $check_data['waybill_data']);
        $check_info['back_data'] = $check_data['check_info'];
        return hSucceed('执行成功', $check_info);
    }

    /**
     * CRM提交装车单修改装车单状态 为 5 申请验车
     * @DateTime  2018-11-12
     * @param     string waybill_no 装车单号
     * @return    [result]    
     */
    public function changConsignStatus(ConsignOrder $consignOrder){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        // 申请验货
        $result = $consignOrder->saveConsignDate($waybill_no, ['waybill_status' => 5]);
        if ($result !== true) {
            return hError($result);
        }

        return hSucceed('执行成功');
    }

    /**
     * 扫码验车
     * @DateTime  2018-11-12
     * @param     string waybill_no 装车单号
     * @return    [result]    
     */
    public function scanCodeCheckWaybill(ConsignOrder $consignOrder){
        $waybill_no = $this->getInput('waybill_no')->isString()->value();
        $waybill_info = $consignOrder->getCarInfoByWaybill($waybill_no);
        if ($waybill_info->waybill_status == 5) {
            // 申请验货
            $result = $consignOrder->saveConsignDateByWaybill_no($waybill_no, ['waybill_status' => 6]);
            if ($result !== true) {
                return hError($result);
            }
        }
        
        return hSucceed('执行成功');
    }


    /**
     * @获取运输单详情
     * @DateTime  2018-11-29
     * @copyright   
     * @license   [license]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function getWaybillDetail(ConsignOrder $consignOrder){
        // 55751b5ed3819cf9b07e806df76b7f38
        $waybillNo = $this->getInput('waybill_no')->isString()->value();
        $param = ['sign'=>'55751b5ed3819cf9b07e806df76b7f38', 'waybill_id'=>$waybillNo, 'rg_id'=>'web', 'rg_ver'=>'9999'];
        $url = '/v0.2/ps_waybill/waybill_detail';
        $url = env('CRM_API_URL','').$url;
        
        $result = curl_post($url, $param);

        if ($result['code'] != 200) {
            return hError($result['msg']);
        }

        if (!is_array($result['data'])) {
            return hError($result['data']);
        }

        return hSucceed('执行成功', $result['data']);

        /*************暂时关闭**************/
        die;
        // $waybill = $consignOrder->getCarInfoByWaybill($waybillNo);
        // $waybill = object_to_array($waybill);
        // $delivery = $consignOrder->getDeliveryDetail($waybillNo);
        // $waybill['details'] = $delivery;
        // return hSucceed('', $waybill);
        /************************************/
    }
}
