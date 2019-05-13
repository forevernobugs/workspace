<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 09/05/2017
 * Time: 19:57
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ProcessRequest extends BaseModel
{
    const CREATED_AT = 'createdDate';
    const UPDATED_AT = 'modifiedDate';

    //审批状态
    const STATUS_UNTREATED = 1;  //未处理
    const STATUS_REVIEW = 2;  //审核中
    const STATUS_EXCEPT = -2;  //异常

    protected $table = 't_process_request';
    public $timestamps = true;


    public function getList($params = [])
    {
        if (!isset($params['user_id']) || empty($params['user_id'])) {
            return ['list'=>[], 'page'=>[]];
        }

        $user = DB::table('erp_user')->where('id', $params['user_id'])->value('login_name');


        $condition = [];

        $condition['createdUser'] = $user;


        if (isset($params['title']) && !empty($params['title'])) {
            $condition['title'] = ['LIKE', "%{$params['title']}%"];
        }

        if (isset($params['message']) && !empty($params['message'])) {
            $condition['message'] = ['LIKE', "%{$params['message']}%"];
        }
        if (isset($params['process_result']) && is_numeric($params['process_result'])) {
            $condition['process_result'] = $params['process_result'];
        }
        if (isset($params['reason']) && !empty($params['reason'])) {
            $condition['reason'] = ['LIKE', "%{$params['reason']}%"];
        }

        $result = object_to_array(DB::table($this->table)
            ->where($condition)
            ->orderBy('id', 'desc')
            ->paginate($params['pagesize']));
        $data = $result['data'];
        unset($result['data']);
        return ['list' => $data, 'page' => $result];
    }


    public static function getNexAuthorizer($requestId)
    {
        $request = self::find($requestId);
        if (empty($request)) {
            return [];
        }

        $flow = DB::table('t_process_flow')
            ->where('process_name', $request->process_name)
            ->where('flow_index', $request->passed_flow + 1)
            ->first();

        if (!in_array($request->process_result, [1,2])) {
            return [];
        }

        if (empty($flow)) {
            return [];
        }

        $returnData = ['method'=>'','authorizers'=>''];

        switch ($flow->pass_type) {
            case '1':
                $returnData['method'] = '用户';
                break;
            case '2':
                $returnData['method'] = '用户组';
                break;
            case '3':
                $returnData['method'] = '部门';
                break;
            default:
                break;
        }

        $userInfo = self::getFlowAuthorInfo($flow);

        $msg = '';
        foreach ($userInfo as $u) {
            $msg .= $u->username . ' ';
        }
        $returnData['authorizers'] = $msg;

        return $returnData;
    }

    public static function getFlowAuthorInfo($flowInfo)
    {
        $authors = explode(',', $flowInfo->auditor);
        switch ($flowInfo->pass_type) {
            case '1':
                return self::getAuthorUsers($authors);
            case '2':
                return self::getAuthorUserGroup($authors);
            case '3':
                return self::getAuthorUserDep($authors);
            default:
                return [];
        }
    }

    /**
     * 获取
     * @param $authors
     * @return string
     */
    private static function getAuthorUsers($authors)
    {
        $users = DB::table('erp_user')
            ->whereIn('id', $authors)
            ->where('enabled', '1')
            ->select('username', 'login_name', 'email')
            ->get();

        return $users;
    }

    /**
     * @param $dep
     * @return string 获取
     */
    private static function getAuthorUserDep($dep)
    {
        $users = DB::table('erp_user')
            ->whereIn('position_id', $dep)
            ->where('enabled', '1')
            ->select('username', 'login_name', 'email')
            ->get();

        return $users;
    }

    /**
     *
     */
    private static function getAuthorUserGroup($groups)
    {
        $users = DB::table('erp_user as uu')
            ->join('erp_user_to_group as rr', 'rr.user_id', '=', 'uu.id')
            ->whereIn('rr.group_id', $groups)
            ->where('uu.enabled', '1')
            ->select('username', 'login_name', 'email')
            ->get();

        return $users;
    }
}
