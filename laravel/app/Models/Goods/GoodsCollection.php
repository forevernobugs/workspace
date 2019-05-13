<?php

namespace App\Models\Goods;

use App\Models\BaseModel;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Models\Permission\Organization;
use App\Common\OrderNumber;
use App\Models\BasicInfo\Warehouse;
use App\Models\Logs\OperationLog;
use App\Models\Logs\StockRemain;
use App\Models\Permission\Role;
use App\Models\OrderInfo\OrderBox;
use App\Models\BasicInfo\WarehouseParameter;

/**
 * Created by Sublime.
 * User: zhangdahao
 * Date: 2018/5/4
 * Time: 17:21
 */
class GoodsCollection extends BaseModel
{
    protected $table = 't_goods_collection';

    // 集货方式可读文本
    protected $goodsTypeName = [
        '1'     =>      '托盘',
        '2'     =>      '箱'
    ];


    //获取集货列表
    public function getCollectionList($params = null)
    {
        $org_node = Organization::getOrgPath($params['user_id']);
        //集货单列表
        $model = DB::table('t_goods_collection as tgc')
                ->select(
                    'tgc.id',
                    'tgc.collection_code',
                    'tgc.destination_code',
                    'tgc.warehouse_code',
                    'tgc.order_no',
                    'tgc.create_time',
                    'tgc.collect_status',
                    'tgc.create_user',
                    'tgc.remark',
                    'tgc.operator',
                    DB::raw('COUNT(tgcd.order_no) as goods_num')
                )
                ->whereIn('tgc.org_id', $org_node)
                ->leftJoin('t_goods_collection_detail as tgcd', 'tgc.order_no', '=', 'tgcd.order_no');
        
        $condition[] = $this->buildPara($params, 'tgc.order_no', 'like');
        $condition[] = $this->buildPara($params, 'collect_status', '=');
        $condition[] = $this->buildPara($params, 'tgc.destination_code', '=');
        
        $this->setWhereBetween($model, $params, 'tgc.create_time', 'create_time_s', 'create_time_e');
        
        //分组排序
        $model->groupBy('tgc.id')->orderBy('tgc.id', 'desc');

        //通过移动端方式调用不分页 显示一周的数据
        if (isset($params['rg_id']) && $params['rg_id'] != 'web') {
            //过滤空条件
            foreach ($condition as $key => $value) {
                if (empty($value)) {
                    unset($condition[$key]);
                }
            }
            $date =date('Y-m-d H:i:s', strtotime(date('Y-m-d', time()-24*3600*7)));
            $result = json_decode($model->where($condition)->where('tgc.create_time', '>=', $date)->get()->toJson(), true);
            return ['list' => $result];
        }

        return $this->getList($model, $condition, $params);
    }

    //根据集货号获取一条集货信息
    public function getCollectionOneByOrderNo($order_no = null)
    {
        if (empty($order_no)) {
            return [];
        }

        //获取集货单
        $one = DB::table('t_goods_collection as tgc')
                ->select(
                    'tgc.id',
                    DB::raw("IFNULL(w.warehouse_name,'') as warehouse_name"),
                    'tgc.order_no',
                    'tgc.collection_code',
                    'tgc.warehouse_code',
                    'tgc.create_time',
                    'tgc.collect_time',
                    'tgc.collect_status',
                    'tgc.create_user',
                    'tgc.remark',
                    'tgc.update_time',
                    'tgc.operator',
                    'tgc.org_id',
                    'tgc.collect_status',
                    'tgc.destination_code'
                )
                ->where('order_no', $order_no)
                ->leftJoin('t_warehouse_info as w', 'w.warehouse_code', '=', 'tgc.warehouse_code')
                ->first();

        // 获取详情
        checkLogic(!empty($one), '集货单未找到');
        //获取所有仓库code name   code=>name
        $warehouse = Warehouse::getWarehouseCodeName();

        checkLogic(isset($warehouse[$one->destination_code]), '目的仓获取失败');
        $one->destination_name = $warehouse[$one->destination_code];
        $one->detail = DB::table('t_goods_collection_detail as gcd')
                ->select(
                    'gcd.*', 
                    DB::raw("IFNULL(oli.is_urgent, 0) as is_urgent"))
                ->leftJoin('t_order_box as ob', 'ob.box_number', '=', 'gcd.goods_code')
                ->leftJoin('t_order_line_info as oli', 'ob.sm_order', '=', 'oli.sm_order')
                ->where('order_no', $order_no)
                ->get();

        return $one;
    }

    //根据集货单号 获取箱号
    public function getGoodsCodeByOrderNo($order_no = null)
    {
        if (empty($order_no)) {
            return [];
        }

        $detail = DB::table('t_goods_collection_detail')
            ->where('order_no', $order_no)
            ->pluck('goods_code')
            ->toArray();

        return $detail;
    }

    //新增集货单
    public function addCollection($params = null)
    {
        checkLogic(Role::checkUserRole($params['user_id'], 'WAREHOUSE_MANAGER'), '当前用户不是仓管用户');

        //获取当前登录的用户信息
        $userInfo = User::getUserDetail($params['user_id']);
        //获取组织id
        $org_id = $userInfo->org_id;
        //匹配仓库
        $warehouse_code = Warehouse::getWarehouseCode($org_id);

        $collection_data = [];
        $collection_detail_data = [];

        $collection_code_arr = explode('-', $params['collection_code']);
        if (count($collection_code_arr) !== 3 || $collection_code_arr[0] !== 'WH') {
            return '集货号格式不是：WH-####-###';
        }
        $dealer_id = $collection_code_arr[1];
        $line_code = $collection_code_arr[2];

        //集货单信息
        $collection_data['org_id'] = $org_id;
        $collection_data['collection_code'] = $params['collection_code'];
        $collection_data['warehouse_code'] = $warehouse_code;
        $collection_data['collect_time'] = hDate();
        $collection_data['collect_status'] = 2 ;    //暂时写死 （集货状态）
        $collection_data['create_user'] = $userInfo->login_name;
        $collection_data['remark'] = isset($params['remark']) ? $params['remark'] : '';
        $collection_data['update_time'] = hDate();
        $collection_data['operator'] = $userInfo->login_name;
        $collection_data['line_code'] = $line_code;

        $detail = hArrayUnset($params['detail'], 'goods_code');//集货单明细
        $goods_code_arr = array_column($detail, 'goods_code');
        $finish_code_info = $this->getGoodsNoInfo($goods_code_arr);

        $remain_good_code_info = array_diff($goods_code_arr, $finish_code_info);
        if (empty($remain_good_code_info)) {
            return $collection_data;
        }

        //集货配置
        // $collectType = Warehouse::getWarehouseInfoDealerId($dealer_id, 'collect_type');  
        $collectType = WarehouseParameter::getWarehouseConfig('collect_type', $userInfo->warehouse_code);

        $destination_code = Warehouse::getWarehouseInfoDealerId($dealer_id, 'warehouse_code');
        if (is_null($destination_code)) {
            return '找不到warehouse_code为：'.$destination_code.'的仓库！';
        }

        $collection_data['destination_code'] = $destination_code;

        $get_box_dealer_info = $this->getBoxDealerIdInfo($remain_good_code_info);

        if ($collectType == '2') {
            $get_line_info = $this->getBoxDealerIdInfo($remain_good_code_info, 'line_code');
        } else {
            foreach ($remain_good_code_info as $v) {
                $get_line_info[$v] = $line_code;
            }
        }
        $collection_detail_data = [];

        //验证集货单是否存在
        $flag = true;

        if (empty($params['order_no']) || !isset($params['order_no'])) {
            $flag = true;
            $order_no = OrderNumber::getNextNumber('C');
        } else {
            $order_no = $params['order_no'];
            $goods = DB::table('t_goods_collection')->where('order_no', $order_no)->first();
            if (empty($goods)) {
                $flag = true;
            } else {
                $params['id'] = $goods->id;
                $flag = false;
            }
        }

        //判断是新增还是更新
        if ($flag) {
            //获取集货单编号
            $collection_data['order_no'] = $order_no;
            $collection_data['create_time'] = hDate();
            //明细 验证暂时off
            foreach ($remain_good_code_info as $k => $val) {
                //获取箱子所属配送区 并匹配
                if (
                    isset($get_box_dealer_info[$val])
                    &&
                    $get_box_dealer_info[$val] == $dealer_id
                    && $line_code == substr($get_line_info[$val], -3)
                ) {    //成功
                    $collection_detail_data[$k]['order_no'] = $collection_data['order_no'];
                    $collection_detail_data[$k]['org_id'] = $org_id;
                    $collection_detail_data[$k]['goods_code'] = $val;
                    $collection_detail_data[$k]['goods_type'] = 2;
                    $collection_detail_data[$k]['goods_typename'] = '箱';
                    $collection_detail_data[$k]['create_time'] = hDate();
                    $collection_detail_data[$k]['create_user'] = $userInfo->login_name;
                }
            }

            //开启事务
            DB::beginTransaction();

            try {
                $id = DB::table('t_goods_collection')->insertGetId($collection_data);
                if (!empty($collection_detail_data)) {
                    DB::table('t_goods_collection_detail')->insert($collection_detail_data);
                }
                //添加日志
                OperationLog::saveLog(
                    $userInfo->org_id,
                    $userInfo->login_name,
                    'insert',
                    '新建集货单'.$collection_data['order_no'],
                    2
                );
                DB::commit();
                $collection_data['id'] = $id;
                return $collection_data;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } else {  //更新
            $collection_data['id'] = $params['id'];
            $collection_data['order_no'] = $params['order_no'];

            foreach ($remain_good_code_info as $k => $val) {
                if (
                    isset($get_box_dealer_info[$val])
                    &&
                    $get_box_dealer_info[$val] == $dealer_id
                    && $line_code == substr($get_line_info[$val], -3)
                ) {
                    //成功
                    $collection_detail_data[$k]['order_no'] = $params['order_no'];
                    $collection_detail_data[$k]['org_id'] = $org_id;
                    $collection_detail_data[$k]['goods_code'] = $val;
                    $collection_detail_data[$k]['goods_type'] = 2;
                    $collection_detail_data[$k]['goods_typename'] = '箱';
                    $collection_detail_data[$k]['create_time'] = hDate();
                    $collection_detail_data[$k]['create_user'] = $userInfo->login_name;
                }
            }

            //启动事务
            DB::beginTransaction();

            try {
                DB::table('t_goods_collection')->where('id', $params['id'])->update($collection_data);
                if (!empty($collection_detail_data)) {
                    DB::table('t_goods_collection_detail')->insert($collection_detail_data);
                }

                //添加日志
                OperationLog::saveLog($userInfo->org_id, $userInfo->login_name, 'update', '修改了集货单'.$params['order_no'], 2);

                DB::commit();
                return $collection_data;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } 
    }

    //根据箱号获取配送区域编号
    public function getOrderBoxByDealerBoxNumber($dealer_id = null, $box_number = null)
    {
        if (empty($box_number) || empty($dealer_id)) {
            return [];
        }

        return DB::table('t_order_box as ob')
            ->join('t_order_line_info as oli', 'ob.sm_order', '=', 'oli.sm_order')
            ->where('ob.box_number', $box_number)
            ->where('oli.dealer_id', $dealer_id)
            ->value('oli.line_code');
    }

    //传入装箱号，获取dealerId
    public function getBoxDealerId($box_number = null)
    {
        if (empty($box_number)) {
            return [];
        }

        return DB::table('t_order_box as ob')
            ->join('t_order_line_info as oli', 'ob.sm_order', '=', 'oli.sm_order')
            ->where('ob.box_number', $box_number)
            ->value('oli.dealer_id');
    }


    //集货完成
    public function collectionFinish($params)
    {
        checkLogic(Role::checkUserRole($params['user_id'], 'WAREHOUSE_MANAGER'), '当前用户不是仓管用户');
        
        $order_no = $params['order_no'];

        $orderInfo = $this->getCollectionOneByOrderNo($order_no);
        $userInfo = User::getUserDetail($params['user_id']);
        checkLogic($orderInfo->collect_status < 3, '该集货单已经完成，请勿重复操作!');
        checkLogic(in_array($orderInfo->org_id, $userInfo->org_node), '您不是本仓人员，或者您不具备操作本仓权限');

        try {
            DB::table('t_goods_collection')->where('order_no', $order_no)->update(['collect_status'=>3]);
            OperationLog::saveLog(
                $userInfo->org_id,
                $userInfo->login_name,
                'update',
                '将集货单状态由：'.$orderInfo->collect_status.'改成 3',
                2
            );

            DB::commit();
            //添加日志
            $orderInfo->collect_status = 3;
            return $orderInfo;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    //总仓集货记录在途日志
    public function remark_log($box_number, $order_no, $user_id)
    {
        $userInfo = User::getUserDetail($user_id);
        $orderInfo = $this->getCollectionOneByOrderNo($order_no);
        checkLogic($orderInfo->collect_status < 3, '该集货单已经完成，请勿重复操作!');
        checkLogic(in_array($orderInfo->org_id, $userInfo->org_node), '您不是本仓人员，或者您不具备操作本仓权限');
        $roadData = [$box_number => $order_no];
        try {
            StockRemain::roadRecord('WARE_COLLECT', $orderInfo->warehouse_code, $userInfo->username, $roadData);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getCollectionItem($orderNo)
    {
        $items = DB::table('t_goods_collection_detail')
            ->where('order_no', $orderNo)
            ->get();
        return $items;
    }


    //修改集货状态
    public static function saveCollectionStatus($order_no, $status)
    {
        DB::table('t_goods_collection')->where('order_no', $order_no)->update(['collect_status'=>$status]);
    }

    //通过箱号获取一个集货信息
    public function getGoodsNo($goods_code)
    {
        return DB::table('t_goods_collection_detail')->where('goods_code', $goods_code)->first();
    }

    public function checkBoxCollectArea($collectCode, $boxNumber, $user_id)
    {
        $boxPrefix = substr($boxNumber, 0, 2);
        if($boxPrefix != 'RB'){
            return '未识别的箱号:'.$boxNumber;
        };

        $collection_code_arr = explode('-', $collectCode);
        if (count($collection_code_arr) != 3) {
            return '集货码格式不是:WH-****-***';
        }

        $scanDealerId = $collection_code_arr[1];

        $dealerId = $this->getBoxDealerId($boxNumber);

        if (empty($dealerId)) {
            return '箱号为：'.$boxNumber.'的箱子未找到';
        }
        
        if ($scanDealerId != $dealerId) {
            $warehouse_name = Warehouse::getWarehouseInfoDealerId($dealerId, 'warehouse_name');
            return '箱子不属于当前分仓,应属于[' .$warehouse_name. ']';
        }

        $box_info = $this->getGoodsNo($boxNumber);

        if (!is_null($box_info)) {
            return '集货单中已经有此箱号信息，请勿重复扫描！';
        }

        //集货配置
        $userInfo = User::getUserDetail($user_id);
        $collectType = WarehouseParameter::getWarehouseConfig('collect_type', $userInfo->warehouse_code);

        //仓库配置按线路集货
        if ($collectType == 2) {
            $lineInfo = DB::table('t_order_box as box')
                ->join('t_order_line_info as line', 'line.sm_order', '=', 'box.sm_order')
                ->where('box_number', $boxNumber)
                ->value('line_code');
            $lineCode = $collection_code_arr[2];
            if ($lineCode != substr($lineInfo, -3)) {
                return '箱子不属于当前线路，请核实！';
            }
        }
        return true;
    }

    //获取集货箱号已经集货的的箱号
    public function getGoodsNoInfo(array $goods_code_arr)
    {
        return DB::table('t_goods_collection_detail')->whereIn('goods_code', $goods_code_arr)->pluck('goods_code')->toArray();
    }

    //获取dealerId line_code
    public function getBoxDealerIdInfo(array $box_number, $field_name = 'dealer_id')
    {
        return DB::table('t_order_box as ob')
            ->join('t_order_line_info as oli', 'ob.sm_order', '=', 'oli.sm_order')
            ->whereIn('ob.box_number', $box_number)
            ->pluck($field_name, 'box_number')
            ->toArray();
    }

    //根据箱号获取集货单
    public static function getCollectionInfoByGoodsCode($goods_code){
        return DB::table('t_goods_collection_detail as gcd')
            ->select('gc.*','gcd.goods_code')
            ->join('t_goods_collection as gc', 'gc.order_no', '=', 'gcd.order_no')
            ->where('gcd.goods_code', $goods_code)
            ->first();
    }
}
