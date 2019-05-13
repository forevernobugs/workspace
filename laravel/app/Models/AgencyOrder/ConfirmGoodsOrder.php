<?php
/**
 * Created by PhpStorm.
 * User: wangxiaoyang
 * Date: 2019/4/1
 * Time: 9:58
 */

namespace App\Models\AgencyOrder;


use App\Common\MyRedis;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfirmGoodsOrder extends BaseModel
{
    public $do_goods = "t_do_goods";
    public $consign_order_check_out = 't_consign_order_check_out';
    const SALE_AFTER_STATUS =[
        '退货'=>'1',
        '维修'=>'2',
        '换货'=>'3',
        '未指定'=>'0',
    ];
    const IS_NORMAL = [
        2=>'异常',
        1=>'正常'
    ];
    //所有数据的集合名称
    const ALL_SET = 'ALL_SET';
    /**
     * 提交数据
     * @param array $data
     * @return bool|string
     */
    public function confirmGoods(array $data){
        $redis =new MyRedis();
        $result = [];
        $success = [];
        $error = [];
        foreach ($data as $after_sale_no){
            $key = $redis->createKey($after_sale_no);
            if(!$redis->exists($key)){
                $error[$after_sale_no] = "不存在指定的服务单号或此单号未打印标签";
                continue;
            }
            $params = $redis->get($key);
            //写入数据
            DB::beginTransaction();
            try {
                $resultGoods = $this->addDoGoods(json_decode($params,true));
                $resultOrder = $this->addConsignOrder(json_decode($params,true));
                DB::commit();
            }catch (\PDOException $e) {
                $resultGoods = '失败';
                $resultOrder = '失败';
                DB::rollBack();
                Log::info($after_sale_no.'写入失败,'.$e);
            }
            if ($resultGoods === true && $resultOrder === true) {
                $success[$after_sale_no] = $resultGoods;
            }else{
                $error[$after_sale_no] = $resultGoods.';'.$resultOrder;
            }
        }
        $result['success'] = $success;
        $result['error'] = $error;
        return $result;
    }

    /**
     * 记录入库
     * @param $params
     * @return bool
     */
    public function addConsignOrder($params)
    {
        //数据调整
        $data = self::getFirstInfo($params['aftersales_no'],$this->consign_order_check_out,'related_no');
        if($data){
            return "已经存在数据,写入分仓回仓交接表失败";
        }
        $consign_order_data = [
            'supplier'=>$params['name'],
            'warehouse_code'=>isset($params['warehouse_code'])?$params['warehouse_code']:'',
            'destination_code'=>isset($params['destination_code'])?$params['destination_code']:'',
            'status'=>1,
            'related_no'=>$params['aftersales_no'],
            'sku'=>isset($params['shop'][0]['bianma'])?$params['shop'][0]['bianma']:'',
            'plan_num'=>$params['shop'][0]['count'],
            'real_num'=>$params['real_number'],
        ];
        $res = DB::table($this->consign_order_check_out)->insert($consign_order_data);
        if(!$res){
            return "写入分仓回仓交接表失败";
        }
        return true;
    }

    /**
     * 添加验货信息
     * @param $params
     * @return bool
     */
    public function addDoGoods($params)
    {
        //数据调整
        $data = self::getFirstInfo($params['aftersales_no'],$this->do_goods,'related_no');
        if($data){
            return "已经存在数据,写入验货信息表失败";
        }
        $do_goods_data = [
            'express_platform'=>isset($params['express_platform'])?$params['express_platform']:'',
            'carrier_number'=>isset($params['transport_no'])?$params['transport_no']:'',
            'provider_name'=>isset($params['name'])?$params['name']:'',
            'related_no'=>$params['aftersales_no'],
            'sale_after_status'=>self::changeStatus(isset($params['status'])?$params['status']:'未定义',self::SALE_AFTER_STATUS),
            'service_check_time'=>hDate(),
            'sku'=>isset($params['shop'][0]['bianma'])?$params['shop'][0]['bianma']:'',
            'goods_name'=>isset($params['shop'][0]['i_name'])?$params['shop'][0]['i_name']:'',
            'goods_picture'=>json_encode($params['shop'][0]['imgs']),
            'sale_goods_picture'=>$params['shop'][0]['i_picture'],
            'apply_number'=>$params['shop'][0]['count'],
            'real_number'=>$params['real_number'],
            'status'=>1,
            'do_goods_time'=>hDate(),
            'receiver_contact'=>isset($params['contact'])?$params['contact']:'',
           // 'contact_mobile'=>isset($params['contact_mobile'])?$params['contact_mobile']:'',
            'different_reason'=>isset($params['reason'])?$params['reason']:'',
            'is_normal'=>isset($params['is_normal'])?$params['is_normal']:'',
            'created_at'=>hDate(),
            'updated_at'=>hDate(),
        ];
        $res = DB::table($this->do_goods)->insert($do_goods_data);
        if(!$res){
            return "写入验货信息表失败";
        }
        return true;
    }

    /**
     * 将易读文本转为数据库可存储文本
     * @param $string
     * @param $array
     * @return int
     */
    public static function changeStatus($string,$array)
    {
        if(key_exists($string,$array)){
            return $array[$string];
        }
        return 0;
    }

    /**
     * 获取一条数据
     * @param $afterSale
     * @return bool|\Illuminate\Database\Query\Builder
     */
    public function getFirstInfo($afterSale,$table,$column)
    {
        $result = DB::table($table)->where($column,'=',$afterSale)->first();
        if($result == null){
            return false;
        }
        return $result;
    }

    /**
     * 设置一个有序集合的redis键值,用于存储正常和异常状态的验货信息
     * @param $key
     * @param $value
     */
    public static function setOrder($key,$value)
    {
        $redis = new MyRedis();
        $key = $redis->createKey($key);
        self::adjustData($key,$value);
        $allKey = $redis->createKey(self::ALL_SET);
        self::adjustData($allKey,$value);
    }

    //更新redis中的数据
    public static function adjustData($set,array $value)
    {
        $redis = new MyRedis();
        $data = [];
        if($redis->exists($set)){
            $data = $redis->sMembers($set);
        }
        if(!empty($data)) {
            foreach ($data as $key => $item) {
                $itemFlag = json_decode($item, 1);
                if ($itemFlag['aftersales_no'] == $value['aftersales_no']) {
                    $redis->sRem($set, $item);
                }
            }
        }
        $redis->sAdd($set,json_encode($value));
    }

    //从集合中取出一条或者多条指定的数据
    //$relateNumber关联单号为手机号/服务单号/订单号
    public static function getOneOrder($set,$relateNumber)
    {
        $redis = new MyRedis();
        $key = $redis->createKey($set);
        if(!$redis->exists($key)){
            return '未找到'.$set.'集合';
        }
        $data = $redis->sMembers($key);
        if (!$data) {
            return '未找到'.$relateNumber.'服务信息';
        }
        if (!is_array($data)) {
            return 'redis'.$relateNumber.'数据格式返回不正确';
        }
        $tempData = [];
        foreach ($data as $datum){
            $v = json_decode($datum,1);
            if(in_array($relateNumber,$v)){
                array_push($tempData,$v);
            }
        }
        return $tempData;
    }

    //获取指定集合中所有的数据
    public static function getSetOrders($set)
    {
        $redis = new MyRedis();
        $key = $redis->createKey($set);
        if(!$redis->exists($key)){
            return '未找到'.$set.'数据';
        }
        $data = $redis->sMembers($key);
        if (!$data) {
            return '未找到'.$set.'服务信息';
        }
        if (!is_array($data)) {
            return 'redis'.$set.'数据格式返回不正确';
        }
        $tempData = [];
        foreach ($data as $datum){
            $v = json_decode($datum,1);
            array_push($tempData,$v);
        }
        return $tempData;
    }
    public static function getAllOrder($aftersales_no,$searchNumber)
    {
        $redis = new MyRedis();
        $key = $redis->createKey($searchNumber);
        if(strpos($searchNumber,'AS') !==false){
            $key = $redis->createKey('sale'.$searchNumber);
        }
        if(!$redis->exists($key)){
            return '未查询到数据';
        }
        $dataJson = $redis->get($key);
        //转化数组格式
        $list = json_decode($dataJson,1);
        foreach ($list as $item => $value){
            if($item == 'current' && !empty($value[0])){
                $tempArray = $value[0];
                if($aftersales_no == $tempArray['aftersales_no']){
                    return $tempArray;
                }
            }elseif($item == 'orther'){
                foreach ($value as $k=>$v){
                    if($aftersales_no == $v['aftersales_no']){
                        return $v;
                    }
                }
            }
        }
        return [];
    }

    public static function delSetRedisSet($setName,$delValue)
    {
        $redis = new MyRedis();
        $key = $redis->createKey($setName);
        if(!$redis->exists($key)){
            return '未找到'.$setName.'集合';
        }
        $list = $redis->sMembers($key);
        foreach ($list as $item => $value){
            $tempArray = json_decode($value,1);
            if($tempArray['aftersales_no'] == $delValue['aftersales_no']){
                $redis->sRem($key,$value);
            }
        }
        return '删除成功';
    }

    /**
     * 获取确认入库列表
     * @param array $params
     * @return \App\Models\结果集
     */
    public function getConfirmGoodOrders($params = [])
    {
        $condition[] = $this->buildPara($params, 'dog.express_platform', 'like');
        $condition[] = $this->buildPara($params, 'dog.carrier_number', 'like');
        $condition[] = $this->buildPara($params, 'dog.related_no', 'like');
        $condition[] = $this->buildPara($params, 'dog.provider_name', 'like');
        $condition[] = $this->buildPara($params, 'dog.sku', 'like');
        $condition[] = $this->buildPara($params, 'dog.is_normal', '=');
        $condition[] = $this->buildPara($params, 'dog.goods_name', 'like');
        $condition[] = $this->buildPara($params, 'dog.service_check_time', '=');
        $condition[] = $this->buildPara($params, 'dog.do_goods_time', '=');
        $condition[] = $this->buildPara($params, 'dog.receiver_contact', 'like');
        $model = DB::table($this->do_goods . ' as dog')
            ->select(
                'dog.express_platform',
                'dog.carrier_number',
                'dog.related_no',
                'dog.sku',
                'dog.goods_name',
                'dog.goods_picture',
                'dog.sale_goods_picture',
                'dog.apply_number',
                'dog.real_number',
                'dog.do_goods_time',
                'dog.receiver_contact',
                'dog.is_normal',
                'dog.provider_name',
                'dog.different_reason',
                'dog.service_check_time'
            );

        $this->setWhereBetween($model, $params, 'dog.service_check_time', 'service_check_time_s', 'service_check_time_e');
        $this->setWhereBetween($model, $params, 'dog.do_goods_time', 'do_goods_time_s', 'do_goods_time_e');
        $list = $this->getList($model, $condition, $params);
        foreach ($list['list'] as $item =>$value){
            if(!empty($value['sale_goods_picture'])){
                $list['list'][$item]['sale_goods_picture'] = "<img src='{$value['sale_goods_picture']}'></img>";
            }else{
                $list['list'][$item]['sale_goods_picture'] = '';
            }
            if($value['goods_picture'] != '[]'){
                $pictures = json_decode($value['goods_picture']);
                foreach ($pictures as $k=>$v){
                    $list['list'][$item]['goods_picture'] = "<img src='{$v}'></img>";
                }
            }else{
                $list['list'][$item]['goods_picture'] = '';
            }
            $list['list'][$item]['is_normal'] = self::changeStatus($value['is_normal'],self::IS_NORMAL);
        }
        return $list;
    }
}