<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\BasicInfo\Warehouse;
use App\Models\BasicInfo\WarehouseParameter;
use App\Http\Controllers\LoginRequireController;

class WarehouseParameterController extends LoginRequireController
{
    /**
     * 仓库参数编辑初始化
     */
    public function parameterEdit(WarehouseParameter $model)
    {
        $parameter = $model->get_one($this->input);
        $paraValues = [];
        if(is_array($parameter)){
            $paraValues = $model::SUPPORTED_PARAMETER_VALUES[$parameter['para_code']];
        }
        $warehouse = new Warehouse();
        $warehouses = $warehouse->getWarehouseAll();

        return hSucceed('加载成功', [
            'warehouses' => $warehouses,
            'parameter' => $parameter,
            'paraNames' => $model::SUPPORTED_PARAMETERS,
            'paraValues' => $paraValues
        ]);
    }

    /**
     * 根据参数code获取具体参数的值
     */
    public function getParameterValue()
    {
        $values = WarehouseParameter::SUPPORTED_PARAMETER_VALUES[$this->input['paraCode']];
        return hSucceed('', ['list' => $values]);
    }

    /**
     * 仓库参数存储
     */
    public function parameterSave(WarehouseParameter $model)
    {
        $result = $model->do_save($this->input);
        
        if ($result) {
            return hSucceed('操作成功');
        }
        return hError('操作失败');
    }

    /**
     * 参数更新
     */
    public function parameterUpdate(WarehouseParameter $model)
    {
        $result = $model->do_update($this->input);
        
        if ($result) {
            return hSucceed('操作成功');
        }
        return hError('操作失败');
    }

    /**
     * 参数列表
     */
    public function parameterList(WarehouseParameter $model)
    {
        $parameterList = $model->getParameterList($this->input);

        foreach ($parameterList['list'] as &$parameter) {
            $parameterValues = $model::SUPPORTED_PARAMETER_VALUES;
            $para_value = $parameterValues[$parameter['para_code']][$parameter['para_value']];
            $parameter['para_value'] = $para_value;
        }

        $title = [
            'id' => 'ID',
            'para_code' => '参数编码',
            'para_name' => '参数名称',
            'para_value' => '参数值',
            'warehouse_name' => '对应仓库',
            'username' => '操作人',
            'remark' => '备注',
            'modified' => '更新时间'
        ];

        return $this->returnList('加载成功', $parameterList, $title, '仓库参数列表');
    }

    /**
     * 参数删除
     */
    public function parameterDelete(WarehouseParameter $model)
    {
        $result = $model->do_delete($this->input);
        
        if ($result) {
            return hSucceed('操作成功');
        }
        return hError('操作失败');
    }
}