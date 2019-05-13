<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\DirectVerifyController;
use App\Models\BaseModel;
use App\Models\Logs\OperationLog;
use Faker\Provider\Base;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * User: zhangdahao
 * Date: 2018/12/1
 * Time: 15:35
 */
class WarehouseController extends DirectVerifyController
{
	// 添加仓库接口
    public function saveWarehouseInfo(){

        $area = $this->getInput('area')->isString()->value('centre');
        $o_desc = $this->getInput('o_desc')->isString()->value();

        $warehouse_data['short_name'] = $this->getInput('short_name')->isString()->value();
        $warehouse_data['warehouse_code'] = $this->getInput('warehouse_code')->isString()->value();
        $warehouse_data['warehouse_name'] = $this->getInput('warehouse_name')->isString()->value();
        $warehouse_data['province'] = $this->getInput('province')->isString()->value();
        $warehouse_data['region'] = $this->getInput('region')->isString()->value();
        $warehouse_data['city'] = $this->getInput('city')->isString()->value();
        $warehouse_data['is_physical'] = $this->getInput('is_physical')->isString()->value();
        $warehouse_data['releted_code'] = $this->getInput('releted_code')->isString()->value();
        $warehouse_data['remark'] = $this->getInput('remark')->isString()->value();
        $warehouse_data['in_using'] = $this->getInput('in_using')->isString()->value();
        $warehouse_data['incharge'] = $this->getInput('incharge')->isString()->value();
        $warehouse_data['longitude'] = $this->getInput('longitude')->isString()->value();
        $warehouse_data['latitude'] = $this->getInput('latitude')->isString()->value();
        $warehouse_data['dealer_id'] = $this->getInput('dealer_id')->isString()->value();

        // 区域
        $area_array = [
           'centre'     =>   6,    //华中区域
           'southern'   =>   5,    //华南区域
           'east'       =>   4     //华东区域
        ];

        checkLogic(array_key_exists($area, $area_array), '所属区域选择不正确');
        // 构造数据
        $parent_org_path = DB::table('t_organization')->where('id', $area_array[$area])->value('org_path');

        $data = [
            'ogname'    =>      $warehouse_data['warehouse_name'],
            'parent'    =>      $area_array[$area],
            'o_desc'    =>      $o_desc,
            'create_time'   =>      date('Y-m-d H:i:s',time())
        ];
        
        // 获取组织id
        DB::beginTransaction();

        try {
            $last_id = DB::table('t_organization')->insertGetId($data);
            DB::table('t_organization')->where('id', $last_id)->update([
                'org_path'  =>  $parent_org_path.','.$last_id
            ]);
            $warehouse_data['org_id'] = $last_id;
            DB::table('t_warehouse_info')->insert($warehouse_data);
        	DB::commit();
        	return hSucceed('done warehouse');
        } catch (\Exception $e) {
        	DB::rollBack();
            throw $e;
        }
    }


    /**
     * 获取分仓集货获取
     */
    public function getWarehouseCollectionAreaBydealerId(){
        $dealer_city = $this->getInput('dealer_city')->isString()->value();
        $dealer_city = json_decode($dealer_city, 1);
        checkLogic(is_array($dealer_city), '参数传递格式有误！');

        $warehouse_info = [];
        foreach ($dealer_city as $key => $item) {
            $send = explode('_', $item);
            checkLogic(count($send) == 2, '参数明细传递格式有误！');

            $collect_area = DB::table('t_collection_area')->where([
                'dealer_id' =>  $send[0],
                'city_code' =>  $send[1]
            ])->first();

            if (is_null($collect_area)) {
                $warehouse_info[$item] = [
                    'collect_area'  =>  '',
                    'send_type'     =>  'EXPRESS',
                    'carriers'  =>  'KUAYUE'
                ];
            }else{
               $warehouse_info[$item] = [
                    'collect_area'  =>  $collect_area->collection_code,
                    'send_type'     =>  'AUTOGAMY',
                    'carriers'  =>  'RUIGU'
                ];
            }
        }

        return hSucceed('', $warehouse_info);

    }
}