<?php
namespace App\Models\BasicInfo;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use App\Models\BasicInfo\Warehouse;
use App\Exceptions\KnownLogicException;

class WarehouseParameter extends BaseModel
{
    protected $table = 't_transport_parameter';

    const SUPPORTED_PARAMETERS = [
        'collect_type' => '分仓集货方式',
        'receive_type' => '分仓收货方式',
        'delivery_type' => '分仓发货方式'
    ];
    const SUPPORTED_PARAMETER_VALUES = [
        'collect_type' => [
            '1' => '按分仓集货',
            '2' => '按线路集货'
        ],
        'receive_type' => [
            '1' => '直接收货',
            '2' => '扫码收货'
        ],
        'delivery_type' => [
            '1' => '扫码出库',
            '2' => '点击出库'
        ]
    ];

    /**
     * 存储
     */
    public function do_save($params)
    {
        $this->checkExist($params['para_code'], $params['warehouse_code']);

        $data = $this->_filterField($params);
        return $this->insert($data);
    }

    /**
     * 构造数据
     */
    public function _filterField($params = [])
    {
        $data = $this->filterField($params, [
            'para_code', 'para_name', 'para_value',
            'warehouse_code', 'remark'
        ]);
        $data['operator'] = $params['user_id'];
        $data['modified'] = date("Y-m-d H:i:s");

        return $data;        
    }
    
    /**
     * 获取参数列表
     */
    public function getParameterList($params = [])
    {
        $model = DB::table($this->table . ' as tr')
            ->select('tr.*', 'wi.warehouse_name', 'tu.username')
            ->leftJoin('t_warehouse_info as wi', 'tr.warehouse_code', '=', 'wi.warehouse_code')
            ->leftJoin('t_user as tu', 'tu.id', '=', 'tr.operator');
        
        $condition[] = $this->buildPara($params, 'tr.para_code', 'like');
        $condition[] = $this->buildPara($params, 'tr.para_name', 'like');
        $condition[] = $this->buildPara($params, 'tr.para_value', 'like');
        $condition[] = $this->buildPara($params, 'tr.remark', 'like');
        $condition[] = $this->buildPara($params, 'tu.username', 'like');
        $condition[] = $this->buildPara($params, 'wi.id', '=');
        $this->setWhereBetween($model, $params, 'modified', 'modified_s', 'modified_e');

        $list =  $this->getList($model, $condition, $params);
        $warehouse = new Warehouse();
        $warehouses = $warehouse->getWarehouseAll();
        $list['warehouses'] = $warehouses;

        return $list;
    }

    /**
     * 获取一个参数
     */
    public function get_one($params = [])
    {
        if (!isset($params['id']) || true === empty($params['id'])) { 
            return '未传入id';
        }

        return $this->where('id', $params['id'])->first()->toArray();
    }

    /**
     * 更新
     */
    public function do_update($params = [])
    {
        $this->checkExist($params['para_code'], $params['warehouse_code'], $params['id']);        
        checkLogic((isset($params['id']) || is_numeric($params['id'])), '未传入id');

        $data = $this->_filterField($params);
        return $this->where('id', $params['id'])->update($data);
    }

    /**
     * 删除
     */
    public function do_delete($params = [])
    {
        checkLogic((isset($params['id']) || is_numeric($params['id'])), '未传入id');

        return $this->where('id', $params['id'])->delete();
    }

    /**
     * 通过仓库id获取参数列表
     */
    public function getListByWarehouse($warehouseCode)
    {
        return DB::table($this->table)
            ->where('warehouse_code', $warehouseCode)
            ->get();
    }

    /**
     * 验证参数是否存在
     */
    protected function checkExist($pareCode, $warehouseCode, $id = '')
    {
        $exist = DB::table($this->table)
            ->where([
                ['para_code', '=', $pareCode],
                ['warehouse_code', '=', $warehouseCode]
            ])->first();

        if($exist && ($id == '' || $id != $exist->id)){
            throw new KnownLogicException('仓库存在该参数，请前往仓库编辑');
        }
    }

    /**
     * 更新仓库具体参数的值
     */
    public function updateValues($params = [])
    {
        $ware = new Warehouse();
        $warehouseCode = $ware->warehouseDetail($params)->warehouse_code;
        $parameters = $this->getListByWarehouse($warehouseCode);
        
        foreach ($parameters as $parameter) {
            DB::table($this->table)->where([
                ['para_code', '=', $parameter->para_code],
                ['warehouse_code', '=', $warehouseCode]
            ])->update(['para_value' => $params[$parameter->para_code]]);
        }
    }

    /**
     * 获取仓库参数配置
     * @Author    Zhangdahao
     * @DateTime  2018-06-21
     * @param     [string]      $type           [参数编码]
     * @param     [string]      $warehouse_code [仓库编码]
     * @return    [type]                      [description]
     */
    public static function getWarehouseConfig($para_code, $warehouse_code)
    {
        // 参数判断
        checkLogic(array_key_exists($para_code, self::SUPPORTED_PARAMETERS), '参数编码，找不到请核实!');

        return DB::table('t_transport_parameter')
            ->where([['para_code', $para_code], ['warehouse_code', $warehouse_code]])
            ->value('para_value');
    }
}