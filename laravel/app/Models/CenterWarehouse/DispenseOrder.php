<?php

namespace App\Models\CenterWarehouse;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class DispenseOrder extends BaseModel
{
    protected $table = 't_dispense_order';
    public $timestamps = false;

    /**
     * 获取装车单列表
     */
    public function getDispenseList($params = [])
    {
        $subOrgId = $this->getSubOrgId($params['user_id'], $this->table);
        $condition[] = $this->buildPara($params, 'dis.order_number', 'like');
        $condition[] = $this->buildPara($params, 'dis.seal_number', 'like');
        $condition[] = $this->buildPara($params, 'dis.plate_number', 'like');
        $condition[] = $this->buildPara($params, 'dis.contact_name', 'like');
        $condition[] = $this->buildPara($params, 'dis.contact_tel', 'like');
        if(isset($params['from_warehouse_name']) && !empty($params['from_warehouse_name'])){
            $condition[] = ['from_ware.warehouse_name', 'like','%'.$params['from_warehouse_name'].'%'];
        }
        if(isset($params['to_warehouse_name']) && !empty($params['to_warehouse_name'])){
            $condition[] = ['to_ware.warehouse_name', 'like','%'.$params['to_warehouse_name'].'%'];
        }

        $model = DB::table($this->table . ' as dis')
            ->join(
                't_warehouse_info as from_ware',
                'dis.warehouse_code',
                '=',
                'from_ware.warehouse_code'
            )->join(
                't_warehouse_info as to_ware',
                'dis.destination_code',
                '=',
                'to_ware.warehouse_code'
            )
            ->leftJoin(
                't_dispense_order_detail as dis_it',
                'dis.order_number',
                '=',
                'dis_it.order_number'
            )
            ->whereIn('dis.org_id', $subOrgId)
            ->select(
                'dis.id',
                'dis.order_number',
                'dis.seal_number',
                'dis.plate_number',
                'dis.contact_name',
                'dis.contact_tel',
                'dis.create_time',
                'from_ware.warehouse_name as from_warehouse_name',
                'to_ware.warehouse_name as to_warehouse_name',
                DB::raw('COUNT(dis_it.order_number) as goods_num')
            )
            ->groupBy(
                'dis.id'
                // ,
                // 'dis.order_number',
                // 'from_ware.warehouse_name',
                // 'to_ware.warehouse_name',
                // 'dis.create_time',
                // 'dis.plate_number',
                // 'dis.order_status'
            )
            ->orderBy('dis.id', 'desc');

        $this->setWhereBetween($model, $params, 'dis.create_time', 'create_time_s', 'create_time_e');
        $list = $this->getList($model, $condition, $params);
        $list['subOrg'] = $this->getSubOrgs($params['user_id'], $this->table);
        
        return $list;
    }
}