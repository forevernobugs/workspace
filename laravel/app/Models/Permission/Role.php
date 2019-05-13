<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 16:48
 */

namespace App\Models\Permission;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class Role extends BaseModel
{
    protected $table = 't_role';

    /**
     * 传入用户ID获取角色ID
     * @param $userId
     * @return \Illuminate\Support\Collection
     */
    public static function getUserRoles($userId)
    {
        $userGroupInfo = DB::table('t_user as u')
            ->join('t_user_role as r', 'r.user_id', '=', 'u.id')
            ->where('u.id', $userId)
            ->pluck('role_id');
        return $userGroupInfo;
    }
    
    /**
     * 获取用户组列表
     */
    public function getRoleList($params = [])
    {
        $model = DB::table('t_role');
        $condition[] = $this->buildPara($params, 'keyword', 'like');
        return $this->getList($model, $condition, $params);
    }

    //获取一条
    public static function getUserRoleInfo($userId)
    {
        $userGroupInfo = DB::table('t_user as u')
            ->join('t_user_role as r', 'r.user_id', '=', 'u.id')
            ->join('t_role as ro', 'ro.id', '=', 'r.role_id')
            ->where('u.id', $userId)
            ->get()
            ->toArray();
        return $userGroupInfo;
    }

    /**
     * 判断某一用户是具有特定用户组
     * @param $userId
     * @param $roleCode
     * @return bool
     */
    public static function checkUserRole($userId, $roleCode)
    {
        $count = DB::table('t_role as r')
            ->join('t_user_role as ur', 'ur.role_id', '=', 'r.id')
            ->where('ur.user_id', $userId)
            ->where('role_code', $roleCode)
            ->count();

        return $count > 0;
    }
}
