<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 10/05/2017
 * Time: 10:59
 */

namespace App\Models\Process;

use App\Models\BaseModel;
use App\User;
use Illuminate\Support\Facades\DB;

class ProcessFlow extends BaseModel
{
    protected $table = 't_process_flow';

    //审批状态
    const STATUS_UNTREATED = 1;  //未处理
    const STATUS_REVIEW = 2;  //审核中
    const STATUS_EXCEPT = -2;  //异常

    const PROCESS_TYPE = [
        '1' => '人员',
        '2' => '用户组',
        '3' => '组织架构',
    ];

    public static function getFlowsByBusiness($businessName)
    {
        $flows = DB::table('t_process_flow')
            ->where('process_name', $businessName)
            ->orderBy('flow_index', 'asc')
            ->get();
        return $flows;
    }

    /**
     * 获取审批流配置类型
     *
     * @param array $params
     */
    public function saveFlow($params=[])
    {
        checkLogic(!empty($params['user_id']), '请传入用户ID');
        checkLogic(!empty($params) && is_array($params['process_id']), '请传入数据');

        $loginName = User::getLoginName($params['user_id']);
        $data = [];
        foreach ($params['process_id'] as $key => $value) {
            $data[$key] = [
                'process_name' => $params['process_name'],
                'pass_type' => $params['process_type'][$key],
                'flow_index' => ++$key,
                'auditor' => $value,
                'is_enable' => !isset($params['enabled']) ? 0 : $params['enabled'],
                'createdDate' => hdate(),
                'createdUser' => $loginName,
                'modifiedDate' => hdate(),
                'modifiedUser' => $loginName,
            ];
        }
        DB::table($this->table)->insert($data);
    }




}