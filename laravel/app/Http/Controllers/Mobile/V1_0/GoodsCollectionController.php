<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/5/3
 * Time: 19:32
 */

namespace App\Http\Controllers\Mobile\V1_0;

use App\Http\Controllers\MobileApiController;
use App\Models\BasicInfo\Warehouse;
use App\Models\Goods\GoodsCollection;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Common\OrderNumber;
use App\Models\BasicInfo\WarehouseParameter;

class GoodsCollectionController extends MobileApiController
{
    //获取集货列表
    public function getCollectionList(GoodsCollection $goods)
    {
        $info = $goods->getCollectionList($this->input)['list'];
        checkLogic(!empty($info), '无数据');
        return hSucceed('', $info);
    }

    //获取集货详情
    public function getCollectionDetail(GoodsCollection $goods)
    {
        //集货单编号
        $order_no = $this->getInput('order_no')->isString()->value();
        $info = $goods->getCollectionOneByOrderNo($order_no);
        checkLogic(!empty($info), '集货单未找到,请核实集货单');

        return hSucceed('', $info);
    }

    //新增集货单
    public function addCollection(GoodsCollection $goods)
    {
        $params = $this->input;

        //获取组织id
        $org_id = User::getUserInfo($params['user_id'])->org_id;
        //集货库位号
        $this->getInput('collection_code', '请传入集货码')->isString()->check();
        $this->getInput('detail', '请传入集货单明细')->isString()->check();

        //如果集货单已经被创建,集货单编号必传
        if (isset($params['id']) && !empty($params['id'])) {
            $order_no = $this->getInput('order_no')->isString()->value();
        }

        $params['detail'] = json_decode($params['detail'], 1);
        //详情
        checkLogic(isset($params['detail']) && !empty($params['detail']) && is_array($params['detail']) && isset($params['detail'][0]) && is_array($params['detail'][0]), '至少扫描一个商品');

        $detail_filed = ['goods_code'];

        $detail_keys = array_keys($params['detail'][0]);

        foreach ($detail_filed as $k => $val) {
            checkLogic(in_array($val, $detail_keys), '缺少'.$val);
        }

        $result = $goods->addCollection($params);

        if (is_array($result)) {
            return hSucceed('集货成功', $result);
        }
        return hError('集货失败'.$result);
    }


    //实时验证箱号
    public function checkBox(GoodsCollection $goods)
    {
        $params = $this->input;
        $collection_code = $this->getInput('collection_code')->isString()->value();
        $box_number = $this->getInput('box_number')->isString()->value();
        $order_no = $this->getInput('order_no')->isString()->value();
        $result = $goods->checkBoxCollectArea($collection_code, $box_number, $params['user_id']);
        if ($result === true) {
            //构造数据
            $params['detail'] = [
                ['goods_code'   =>  $box_number]
            ];
            $goodsInfo = $goods->addCollection($params);
            $remark_log = $goods->remark_log($box_number, $order_no, $params['user_id']);
            if (is_array($goodsInfo) && $remark_log) {
                return hSucceed('');
            } else {
                return hSucceed('', ['box_number' => $box_number,'msg' => '集货失败']);
            }
        } else {
            return hSucceed('', ['box_number' => $box_number,'msg' => $result]);
        }
    }

    //集货完成
    public function collectionFinish(GoodsCollection $goods)
    {
        $this->getInput('order_no')->isString()->check();
        $goods->collectionFinish($this->input);
        return hSucceed('集货完成');
    }

    public function getCollectionItem(GoodsCollection $goods)
    {
        $orderNo = $this->getInput('order_no')->isString()->value();
        $info = $goods->getCollectionItem($orderNo);
        return hSucceed('', $info);
    }

    //返回一个集货单编号
    public function getCollectionOrderOn()
    {
        //获取集货单编号
        $order_no = OrderNumber::getNextNumber('C');
        return hSucceed($order_no);
    }

    // 根据集货号获取集货线路
    public function getWarehouseNameByCollectionCode()
    {
        $collection_code = $this->getInput('collection_code')->isString()->value();
        $wh_code_arr = explode('-', $collection_code);
        checkLogic(count($wh_code_arr) == 3 && $wh_code_arr[0] == 'WH', '集货号的格式不是:WH-0000-000');

        $warehouse_info = Warehouse::getWarehouseInfoDealerId($wh_code_arr[1], ['warehouse_name', 'warehouse_code']);
        $collect_type = WarehouseParameter::getWarehouseConfig('collect_type', $warehouse_info->warehouse_code);

        $warehouse_name = substr($warehouse_info->warehouse_name, 0, 6);
        //仓库配置按线路集货
        if ($collect_type == 2) {
            return hSucceed($warehouse_name.$wh_code_arr[2]);
        }

        return hSucceed($warehouse_info->warehouse_name);
    }
}
