<?php

namespace App\Models\Logs;

use App\Models\BaseModel;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Created by sublime3.
 * Index: zhangdahao
 * Date: 2018/6/6
 * Time: 15:31
 */
class AbNormalLog extends BaseModel
{
    protected $table = 't_box_abnormal';

    //异常记录类型
    const SCAN_TYPE = [
        1 => 'COLLECT_LEAKAGE', //总仓漏扫
        2 => 'TRANSREGIONAL', //串货
        3 => 'REPEAT_OUTBOUND', //重复出库
        4 => 'NOT_LINE', //未排车
    ];

    /**
     * 保存获取异常日志
     * @param $loginName
     * @param $remark
     * @throws \App\Exceptions\KnownLogicException
     */
    public static function saveLog($box_number, $warehouse_code, $related_order, $scan_user, $scan_type)
    {
        checkLogic(key_exists($scan_type, self::SCAN_TYPE), '异常类型不存在');
        $result = DB::table('t_box_abnormal')
            ->where([['box_number', $box_number], ['related_order', $related_order], ['scan_type', self::SCAN_TYPE[$scan_type]]])
            ->count();
        if ($result > 0) {
            return;
        }

        DB::table('t_box_abnormal')->insert([
            'box_number' => $box_number,
            'warehouse_code' => $warehouse_code,
            'scan_user' => $scan_user,
            'scan_time' => hDate(),
            'related_order' => $related_order,
            'scan_type' => self::SCAN_TYPE[$scan_type],
        ]);
    }

    /**
     * 获取异常日志
     * @param $para
     * @return
     * @internal param $loginName
     */
    public static function getAbNormalLog($para)
    {
        return AbNormalLog::where($para)->get()->toArray();
    }

    /**
     * 获取异常日志列表
     * @param $param
     * @return
     */
    public function getAbNormalList($params)
    {
        $userInfo = User::getUserDetail($params['user_id']);

        $model = DB::table('t_box_abnormal as ab')
            ->select('wi.warehouse_name', 'ab.box_number', 'ab.scan_user', 'ab.scan_time', 'ab.related_order', 'ab.scan_type')
            ->leftJoin('t_warehouse_info as wi', 'ab.warehouse_code', '=', 'wi.warehouse_code')
            ->orderBy('ab.scan_time', 'desc')
            ->whereIn('org_id', $userInfo->org_node);
        //setWhere
        $condition[] = $this->buildPara($params, 'ab.box_number', 'like');
        $condition[] = $this->buildPara($params, 'ab.scan_type', '=');
        $condition[] = $this->buildPara($params, 'wi.warehouse_code', '=');
        $this->setWhereBetween($model, $params, 'scan_time', 'create_time_s', 'create_time_e');
        return $this->getList($model, $condition, $params);
    }
}
