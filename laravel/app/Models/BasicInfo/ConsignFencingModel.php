<?php

namespace App\Models\BasicInfo;

use App\Models\BaseModel;
use App\Models\Logs\OperationLog;

/**
 * Created by subline3.
 * User: zhangdahao
 * Date: 2018/6/1
 * Time: 15:47
 */
class ConsignFencingModel extends BaseModel
{
    protected $table = 't_consign_fencing_info';

    /**
     * 新增线路信息
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
	            $id = $this->insertGetId($data);
	            //记录业务日志
	            OperationLog::saveLog(
	                0,
	                'api',
	                'insert',
	                '新增区域',
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
}
