<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 20:45
 */

namespace App\Models\BasicInfo;

use App\User;
use App\Models\BaseModel;
use App\Models\LocationModel;
use Illuminate\Support\Facades\DB;

class Warehouse extends BaseModel
{
    protected $table = 't_warehouse_info';
    /*
    const RECEIVE_TYPE_MAP = [1=>'直接收货', 2=>'扫码收货']; //收货方式配置 1直接收货，2扫码收货
    const COLLECT_TYPE_MAP = [1=>'按分仓集货', 2=>'按线路集货']; //分仓集货方式 1按分仓集货，2按线路集货
    const DELIVERY_TYPE_MAP = [1=>'扫码出库', 2=>'点击出库']; //分仓发货方式配置: 1必须扫码出库，2可直接点击出库
    const WAREHOUSE_TYPE_MAP = [1=>'存储仓', 2=>'分拨仓']; //仓库类型: 1存储仓，2分拨仓
    */

    // 获取当前仓库编码
    public static function getWarehouseCode($org_id = null)
    {
        if (empty($org_id)) {
            return '';
        }
        //获取组织id
        return DB::table('t_warehouse_info')
            ->where('org_id', $org_id)
            ->value('warehouse_code');
    }

    /**
     * 传入集货码字符串，返回相关信息
     * 例如传入：WH-1510-001 （上海001线路的集货码）
     * 返回 ['dealer_id'=>'1510', 'line'=>'001']
     * @param $collectionCode
     * @return array
     */
    public static function parseCollectionCode($collectionCode)
    {
        $collection = explode('-', $collectionCode);

        checkLogic(count($collection) == 3, '当前传入的集货码无法满足要求');

        return ['dealer_id'=>$collection[1], 'line'=>$collection[2]];
    }

    /**
     * 传入
     * @param $warehouseCode
     * @return mixed
     */
    public static function getDealerId($warehouseCode)
    {
        return DB::table('t_warehouse_info')
            ->where('warehouse_code', $warehouseCode)
            ->value('dealer_id');
    }

    /**
     * 传入
     * @param $warehouseCode
     * @return mixed
     */
    public static function getWarehouseCodeByDealerId($dealer_id)
    {
        return DB::table('t_warehouse_info')
            ->where('dealer_id', $dealer_id)
            ->value('warehouse_code');
    }

    /**
     * 获取一条
     * @param $warehouseCode
     * @return mixed
     */
    public static function getWarehouseOne($org_id)
    {
        if (empty($org_id)) {
            return '';
        }
        //获取组织id
        return DB::table('t_warehouse_info')->where('org_id', $org_id)->first();
    }

    /**
     * 获取所有仓库
     */
    public function getWarehouseAll()
    {
        return DB::table('t_warehouse_info')
            ->select('warehouse_name', 'warehouse_code', 'id')
            ->groupBy('warehouse_code')
            ->where('in_using', 1)
            ->get()
            ->toArray();
    }


    /**
     * 获取所有仓库code name   code=>name
     */
    public static function getWarehouseCodeName()
    {
        return DB::table('t_warehouse_info')
            ->groupBy('warehouse_code')
            ->where('in_using', 1)
            ->pluck('warehouse_name', 'warehouse_code')
            ->toArray();
    }

    //根据仓库code 获取名称和org_id
    public static function getNameOrgByCode($code = null)
    {
        if (empty($code)) {
            return [];
        }
        return DB::table('t_warehouse_info as w')
                ->select('warehouse_name', 'username')
                ->join(
                    't_user as u',
                    'w.org_id',
                    '=',
                    'u.org_id'
                )
                ->where('warehouse_code', $code)
                ->groupBy('warehouse_code')
                ->first();
    }

    /**
     * 获取一组组织ID下附带的仓库
     * 返回值为仓库编码的数组
     * @param $ogrIds
     * @return array 仓库编码构成的数组
     */
    public static function getWarehouseByOrgId($ogrIds)
    {
        $warehouseCode = DB::table('t_warehouse_info')
            ->whereIn('org_id', $ogrIds)
            ->pluck('warehouse_Code')
            ->toArray();
        return $warehouseCode;
    }

    /**
     * 获取一组组织ID下附带的仓库dealer_id
     * 返回值为仓库dealer_id的数组
     * @param $ogrIds
     * @return array 仓库编码构成的数组
     */
    public static function getDealerByOrgId($ogrIds)
    {
        $warehouseCode = DB::table('t_warehouse_info')
            ->whereIn('org_id', $ogrIds)
            ->pluck('dealer_id')
            ->toArray();
        return $warehouseCode;
    }

    public function getTransportInfo($params)
    {
        $warehouseCode = $params['warehouse_code'];
        $data = DB::table('t_warehouse_info as ware')
            ->join('t_car as car', 'car.id', '=', 'ware.duty_car')
            ->join('t_transport_provider as trop', 'trop.id', '=', 'car.crop_id')
            ->where('warehouse_code', $warehouseCode)
            ->first([
                'car.car_name',
                'plate_number',
                'driver_name',
                'driver_contact',
                'provider_code',
                'provider_name',
                DB::raw('trop.id as provider_id')
            ]);
        return $data;
    }

    /**
     * 传入仓库编码，获取仓库相关信息
     * 传入数组时返回object，传入string时返回string
     * @param $warehouseCode
     * @param $columnName
     * @return string|array
     */
    public static function getWarehouseInfo($warehouseCode, $columnName)
    {
        if (is_array($columnName)) {
            return DB::table('t_warehouse_info')
                ->where('warehouse_code', $warehouseCode)
                ->first($columnName);
        } else {
            return DB::table('t_warehouse_info')
                ->where('warehouse_code', $warehouseCode)
                ->value($columnName);
        }
    }

    /**
     * 传入仓库dealerId，获取仓库相关信息
     * 传入数组时返回object，传入string时返回string
     * @param $dealerId
     * @param $columnName
     * @return string|array
     */
    public static function getWarehouseInfoDealerId($dealerId, $columnName)
    {
        if (is_array($columnName)) {
            return DB::table('t_warehouse_info')
                ->where('dealer_id', $dealerId)
                ->first($columnName);
        } else {
            return DB::table('t_warehouse_info')
                ->where('dealer_id', $dealerId)
                ->value($columnName);
        }
    }

    /**
     * 获取仓库列表
     */
    public function warehouseList($params = [])
    {
        $subOrgId = $this->getSubOrgId($params['user_id'], $this->table);        
        $conditions = $this->getConditions($params);

        $result = DB::table($this->table . ' as twi')
            ->select('twi.id', 'twi.warehouse_code', 'twi.warehouse_name',
                'twi.is_physical', 'twi.in_using', 'twi.longitude', 
                'twi.latitude', 'twi.address','twi.province', 'twi.region', 'twi.city',
                'tu.username as charger', 'tto.ogname')
            ->leftJoin('t_user as tu', 'twi.incharge', '=', 'tu.id')
            ->leftJoin('t_organization as tto', 'twi.org_id', '=', 'tto.id')
            ->where($conditions)
            ->paginate($params['pagesize'])
            ->toArray();

        $subOrgs = $this->getSubOrgs($params['user_id'], $this->table);
        
        $warehouses = $result['data'];
        
        $location = new LocationModel();
        foreach ($warehouses as $key => $warehouse) {
            $province = $location->getProvinceNameByCode($warehouse->province);
            $region = $location->getRegionNameByCode($warehouse->region);
            $city = $location->getCityNameByCode($warehouse->city);
            $warehouse->address = $province . $region . $city . ': ' . $warehouse->address;
        }

        unset($result['data']);
        return ['list' => $warehouses, 'page' => $result, 'subOrgs' => $subOrgs];
    }

    /**
     * 获取仓库详情
     */
    public function warehouseDetail($params = [])
    {
        $warehouse = DB::table($this->table . ' as twi')
            ->select('twi.*', 'tc.car_name',
                'tu.username as charger', 'tto.ogname')
            ->leftJoin('t_car as tc', 'twi.duty_car', '=', 'tc.id')
            ->leftJoin('t_user as tu', 'twi.incharge', '=', 'tu.id')
            ->leftJoin('t_organization as tto', 'twi.org_id', '=', 'tto.id')
            ->where('twi.id', $params['id'])
            ->first();
        $location = new LocationModel();
        $warehouse->province = $location->getProvinceNameByCode($warehouse->province);
        $warehouse->region = $location->getRegionNameByCode($warehouse->region);
        $warehouse->city = $location->getCityNameByCode($warehouse->city);
    
        return $warehouse;
    }

    /**
     * 构造查询条件
     */
    protected function getConditions($params = [])
    {
        $conditions = [];
        if(isset($params['org']) && !empty($params['org'])){
            $conditions[] = ['twi.org_id', '=', $params['org']];
        }

        if(isset($params['charger']) && !empty($params['charger'])){
            $conditions[] = ['tu.username', 'like', '%'.$params['charger'].'%'];
        }

        if(isset($params['warehouse_name']) && !empty($params['warehouse_name'])){
            $conditions[] = ['twi.warehouse_name', 'like','%'.$params['warehouse_name'].'%'];
        }

        if(isset($params['address']) && !empty($params['address'])){
            $conditions[] = ['twi.address', 'like','%'.$params['address'].'%'];
        }

        if (isset($params['is_physical']) && is_numeric($params['is_physical'])) {
            $conditions[] = ['twi.is_physical', '=', $params['is_physical'] == 2 ? 0 : $params['is_physical']];
        }

        if (isset($params['in_using']) && is_numeric($params['in_using'])) {
            $conditions[] = ['twi.in_using', '=', $params['in_using'] == 2 ? 0 : $params['in_using']];
        }
        
        return $conditions;
    }

    /**
     * 更新
     */
    public function do_update($params = [])
    {
        DB::beginTransaction();
        try{
            if(isset($params['parameter']) && $params['parameter'] == 'yes'){
                $parameter = new WarehouseParameter();
                $parameter->updateValues($params);
            }

            $data = $this->_filterField($params);
            $this->where('id', $params['id'])->update($data);

            DB::commit();
            return 'ok';
        } catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    /**
     * 存储
     */
    public function do_save($params)
    {
        $data = $this->_filterField($params);

        return $this->insert($data);
    }

    /**
     * 构造数据
     */
    public function _filterField($params = [])
    {
        $data = $this->filterField($params, [
            'short_name', 'warehouse_code', 'warehouse_name',
            'dealer_id', 'releted_code', 'city', 'address', 'longitude',
            'latitude', 'incharge','warehouse_type', 'org_id', 'duty_car', 'remark'
        ]);

        if (isset($params['provinces']) && false === empty($params['provinces'])) { 
            $data['province'] = $params['provinces']; 
        } 
        if (isset($params['regions']) && false === empty($params['regions'])) { 
            $data['region'] = $params['regions']; 
        }
        $data['in_using'] = (isset($params['in_using']) && $params['in_using'] >= 1) || false === empty($params['in_using']) ? 1 : 0;
        $data['is_physical'] = (isset($params['is_physical']) && $params['is_physical'] >= 1) || false === empty($params['is_physical']) ? 1 : 0;

        return $data;
    }

    /**
     * 获取仓库的省市区
     */
    public static function getWarehouseLocation($field , $condition){
        return DB::table('t_warehouse_info as wi')
            ->select(
                'lp.provience_name',
                'lc.city_name as region_name',
                'lr.region_name as city_name',
                'wi.address'
            )
            ->where($field, $condition)
            ->leftJoin('t_location_proviences as lp', 'lp.provience_code', '=', 'wi.province')
            ->leftJoin('t_location_regions as lr', 'lr.region_code', '=', 'wi.region')
            ->leftJoin('t_location_city as lc', 'lc.city_code', '=', 'wi.city')
            ->first();
    }
}
