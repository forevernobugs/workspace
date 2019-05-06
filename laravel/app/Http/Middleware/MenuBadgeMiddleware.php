<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 05/11/2017
 * Time: 16:37
 */

namespace App\Http\Middleware;

use App\Models\Users\UserModel;
use App\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuBadgeMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (!method_exists($response, 'getData')) {
            return $response;
        }
        $data = $response->getData();
        if (!isset($data->data) || empty($data->data)) {
            $data->data = (object) [];
        }
        $data->data->menu_badge = $this->getMenuBadge($request);
        $response->setData($data);
        return $response;
    }

    //此处返回所有的徽章
    private function getMenuBadge($request)
    {
        $menu_badge = [];
        $menu_badge['index/my_task'] = $this->getTaskCount($request);
        return $menu_badge;
    }


    //返回用户审批事项条数
    private function getTaskCount($request)
    {
        $params = $request->all();

        $flowModel = DB::table('ruigu_business_process_flow as rbpf')
            ->join('ruigu_business_process_request as rbpr', function ($join) {
                $join->on('rbpf.process_name', '=', 'rbpr.process_name')->on('rbpf.flow_index', '=', DB::raw('rbpr.passed_flow +1'));
            });

        $flowModel->where('rbpf.is_enable', '=', 1);
        $flowModel->whereIn('rbpr.process_result', [1, 2]);

        //拼接sql
        $sql = '(';
        $sql .= '(find_in_set(' . $params['user_id'] . ', rbpf.auditor) AND rbpf.pass_type=1)';
        //获取组织架构id
        $positionId = UserModel::getUserPosition($params['user_id']);
        if (!empty($positionId)) {
            $sql .= ' OR (find_in_set(' . $positionId . ',rbpf.auditor) AND rbpf.pass_type=2)';
        }
        //获取组id
        $groupIds = UserModel::getUserGroups($params['user_id']);
        if (!empty($groupIds)) {
            foreach ($groupIds as $key=>$value) {
                $sql .= ' OR (find_in_set(' . $value . ',rbpf.auditor) AND rbpf.pass_type=3)';
            }
        }
        $sql .= ')';

        $flowModel->whereRaw($sql);
        $task_count = $flowModel->select(DB::raw('count(*) as total'))->get();


        if ($task_count == null || count($task_count) == 0 || !isset($task_count[0]->total)) {
            return null;
        }
        $total = $task_count[0]->total;

        $result = $total < 100 ? ''.$total : '99+';

        return $result;
    }
}
