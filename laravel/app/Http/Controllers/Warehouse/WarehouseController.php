<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\BasicInfo\Warehouse;
use App\Http\Controllers\LoginRequireController;
use App\User;
use App\Models\BasicInfo\CarInfo;
use App\Models\BasicInfo\WarehouseParameter;

class WarehouseController extends LoginRequireController
{
    /**
     * 仓库列表
     */
    public function warehouseList(Warehouse $model)
    {
        $list = $model->warehouseList($this->input);
        foreach ($list['list'] as &$warehouse) {
            $warehouse->is_physical = $warehouse->is_physical == 1 ? '是' : '否';
            $warehouse->in_using = $warehouse->in_using == 1 ? '是' : '否';
            $warehouse->province = '经度:' . $warehouse->longitude . PHP_EOL . '纬度:' . $warehouse->latitude;
        }

        $list['title'] = [
            'id' => '集货单ID',
            'warehouse_code' => '仓库编码',
            'warehouse_name' => '仓库名称',
            'charger' => '管理员',
            'ogname' => '所属组织',
            'is_physical' => '物理仓',
            'in_using' => '启用',
            'province' => '坐标',
            'address' => '地址'
        ];

        return $this->returnList('加载成功', $list, $list['title'], '集货单列表');
    }

    /**
     * 仓库详情
     */
    public function warehouseDetail(Warehouse $model)
    {
        if (!isset($this->input['id']) || true === empty($this->input['id'])) {
           return hError('未传入id');
        }

        $warehouse = $model->warehouseDetail($this->input);

        $parameter = new WarehouseParameter();
        $parameters = $parameter->getListByWarehouse($warehouse->warehouse_code);
        foreach ($parameters as &$para) {
            $paras = WarehouseParameter::SUPPORTED_PARAMETER_VALUES[$para->para_code];
            $para->para_value = $paras[$para->para_value];
        }

        return hSucceed('加载成功', [
            'warehouse' => $warehouse,
            'parameters' => $parameters
        ]);
    }

    /**
     * 仓库编辑初始化
     */
    public function warehouseEdit(Warehouse $model)
    {
        $parameters = [];$warehouse = [];
        if(isset($this->input['id'])){
            $warehouse = $model->warehouseDetail($this->input);
            $parameters = $this->getParameters($warehouse->warehouse_code);
        }
        $subOrg = $model->getSubOrgs($this->input['user_id'], 't_warehouse_info');
        
        $userModel = new User();
        $users = $userModel->geuUserlist($this->input)['list'];

        $carModel = new CarInfo();
        $cars = $carModel->carListAll($this->input);
        
        return hSucceed('加载成功', [
            'warehouse' => $warehouse,
            'subOrg' => $subOrg,
            'users' => $users,
            'cars' => $cars,
            'parameters' => $parameters
        ]);
    }

    /**
     * 仓库更新
     */
    public function warehouseUpdate(Warehouse $model)
    {
        if (!isset($this->input['id']) || true === empty($this->input['id'])) {
            return hError('未传入id');
        }
        
        $result = $model->do_update($this->input);
        
        if ($result) {
            return hSucceed('操作成功');
        }
        return hError('操作失败');
    }

    /**
     * 仓库存储
     */
    public function warehouseSave(Warehouse $model)
    {
        $result = $model->do_save($this->input);
        
        if ($result) {
            return hSucceed('操作成功');
        }
        return hError('操作失败');
    }

    protected function getParameters($warehouseCode)
    {
        $parameter = new WarehouseParameter();
        $parameters = $parameter->getListByWarehouse($warehouseCode);

        foreach ($parameters as $parameter) {
            $parameter->paraValues = WarehouseParameter::SUPPORTED_PARAMETER_VALUES[$parameter->para_code];
        }
        return $parameters;
    }
}