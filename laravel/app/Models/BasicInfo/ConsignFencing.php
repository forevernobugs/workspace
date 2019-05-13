<?php

namespace App\Models\BasicInfo;

use App\Models\BaseModel;
use App\Models\Logs\OperationLog;
use Illuminate\Support\Facades\DB;


/**
 * Created by subline3.
 * User: zhangdahao
 * Date: 2018/6/1
 * Time: 15:47
 */
class ConsignFencing extends BaseModel
{
    protected $table = 't_consign_fencing_info';

    /**
     * 新增 / 修改  线路信息
     * @Author    zhangdahao
     * @DateTime  2018-06-01
     * @param     [array]      $params [线路信息]
     * @return  $id
     */
    public function saveConsignFencing($params)
    {
    	//判断是新增还是修改
    	if (isset($params['id']) && !empty($params['id'])) { //修改线路
    		unset($params['id']);
    		try {
	            DB::beginTransaction();
	            $result = $this->where('reg_code', $params['reg_code'])->update($params);
	            //记录业务日志
	            OperationLog::saveLog(
	                0,
	                'api',
	                'update',
	                '修改区域',
	                2
	            );
	            DB::commit();

	            return true;
	        } catch (\Exception $e) {
	            DB::rollback();
	            throw $e;
	        }
    	} else { //新增线路
    		try {
	            DB::beginTransaction();
	            $id = $this->insertGetId($params);
	            //记录业务日志
	            OperationLog::saveLog(
	                0,
	                'api',
	                'insert',
	                '新增区域ID为'.$id,
	                2
	            );
	            DB::commit();
	            return true;
	        } catch (\Exception $e) {
	            DB::rollback();
	            throw $e;
	        }  
    	}
    }

    /**
     * 删除线路
     * @Author    zhangdahao
     * @DateTime  2018-06-04
     * @param     [type]      $id [要删除的线路id]
     * @return  $id
     */
    public function deleteConsignFencting($reg_code)
    {
    	try {
		    DB::beginTransaction();
	        $res = $this->where(['reg_code' => $reg_code])->delete();
	        if ($res == 0) {
	        	throw new \Exception("删除失败", 1);
	        }
	        //记录业务日志
	        OperationLog::saveLog(
	            0,
	            'api',
	            'delete',
	            '删除区域reg_code为'.$reg_code,
	            3
	        );
	        DB::commit();
	        return true;
        } catch (\Exception $e) {
	        DB::rollback();
	        throw $e;
	    }
	}
	
	/**
     * 获取所有围栏线路
     */
    public function getFencingAll()
    {
        return DB::table($this->table)
            ->select('reg_code','id')
            ->get()->toArray();
	}
	
	/**
     * 获取围栏列表
     */
	public function getFencingList($params = [])
    {
		if(isset($params['star']) && !empty($params['star'])) {
			$params['star'] = strtotime($params['star']);
		}
		if(isset($params['end']) && !empty($params['end'])) {
			$params['end'] = strtotime($params['end']);
		}
        $condition[] = $this->buildPara($params, 'cfi.reg_code', 'like');
        $condition[] = $this->buildPara($params, 'cfi.created_by', 'like');
        $condition[] = $this->buildPara($params, 'wi.warehouse_name', 'like');
		if (isset($params['enabled']) && is_numeric($params['enabled'])) {
            $condition[] = ['cfi.enabled', '=', $params['enabled'] == 2 ? 0 : $params['enabled']];
		}

        $result = DB::table($this->table . ' as cfi')
            ->select('cfi.*', 'wi.warehouse_name')
            ->leftJoin('t_warehouse_info as wi', 'wi.dealer_id', 'cfi.dealer_id')->orderBy('cfi.id', 'desc');
        $this->setWhereBetween($result, $params, 'cfi.created', 'star', 'end');
        
        return $this->getList($result, $condition, $params);
    }
}