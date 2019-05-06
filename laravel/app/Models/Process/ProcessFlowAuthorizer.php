<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 09/05/2017
 * Time: 21:14
 */

namespace App\Models\Process;

use App\User;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Class ProcessFlowAuthorizer  检查用户是否具有审核某项流程
 * @package App\Models
 */
final class ProcessFlowAuthorizer
{
    /**
     * 检查当前用户是否具有处理某项流程的权限
     * @param int $authType 审核方式1用户，2用户组，3组织架构
     * @param int $userId  操作用户ID
     * @param array $auditors  当前流程设置的id数组[1,2,...]
     * @return int|mixed 1可以审核 -1没有权限审核
     */
    public static function CheckAuthorization($authType, $userId, $auditors)
    {
        switch ($authType) {
            case 1:
                return self::authorizeByUser($userId, $auditors);
                break;
            case 2:
                return self::authorizeByUserGroup($userId, $auditors);
                break;
            case 3:
                return self::authorizeByDepartment($userId, $auditors);
                break;
            default:
                return -1;
        }
    }

    /**
     * @param int $userId 当前用户ID
     * @param array $auditors 当前流程配置的审核
     * @return int
     */
    private static function authorizeByUser($userId, $auditors)
    {
        if (in_array($userId, $auditors)) {
            return 1;
        }
        return -1;
    }

    /**
     * @param $userId 当前用户ID
     * @param $auditors 当前流程配置的审核
     * @return int
     */
    private static function authorizeByDepartment($userId, $auditors)
    {
        $user = User::find($userId);

        if (in_array($user['position_id'], $auditors)) {
            return 1;
        }
        return -1;
    }

    /**
     * @param $userId 当前用户ID
     * @param $auditors 当前流程配置的审核
     * @return int
     */
    private static function authorizeByUserGroup($userId, $auditors)
    {
        //获取用户所在的所有组
        $groups = DB::table('erp_user_to_group')->where('user_id', $userId)->get();
        $groupIds = [];
        foreach ($groups as $g) {
            $groupIds[] = $g->group_id;
        }
        //获取用户组与审批组的交集
        $groupIntersect = array_intersect($groupIds, $auditors);
        //如果存在交集则表示有权限审批
        if (count($groupIntersect) > 0) {
            return 1;
        }
        return -1;
    }
}
