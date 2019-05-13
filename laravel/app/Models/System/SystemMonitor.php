<?php

namespace App\Models\System;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class SystemMonitor extends BaseModel
{
    /**
     * 登录日志列表
     */
    public function getLoginLogList($params = [])
    {
        $model = DB::table('t_user_login_log');
        $condition[] = $this->buildPara($params, 'login_name', 'like');
        $condition[] = $this->buildPara($params, 'ip_address', 'like');
        $condition[] = $this->buildPara($params, 'login_succeed', '=');
        $this->setWhereBetween($model, $params, 'login_time', 'login_time_s', 'login_time_e');

        return $this->getList($model, $condition, $params);
    }

    /**
     * 操作日志列表
     */
    public function getOperationLogList($params = [])
    {
        $subOrgId = $this->getSubOrgId($params['user_id'], 't_operation_log');        
        $model = DB::table('t_operation_log as ol')
            ->select('ol.*', 'to.ogname')
            ->leftJoin('t_organization as to', 'to.id', '=', 'ol.org_id')
            ->whereIn('ol.org_id', $subOrgId);
        
        $condition[] = $this->buildPara($params, 'ol.user_name', 'like');
        $condition[] = $this->buildPara($params, 'ol.operation', 'like');
        $condition[] = $this->buildPara($params, 'ol.operation_type', '=');
        $condition[] = $this->buildPara($params, 'ol.org_id', '=');
        $this->setWhereBetween($model, $params, 'opreation_time', 'operation_time_s', 'operation_time_e');

        $list = $this->getList($model, $condition, $params);
        $list['subOrgs'] = $this->getSubOrgs($params['user_id'], 't_operation_log');
        
        return $list;
    }
}