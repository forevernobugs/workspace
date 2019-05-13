<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 14/07/2017
 * Time: 10:06
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class PermissionMiddleware
{
    public function handle($request, Closure $next)
    {
        $para = $request->all();
        $verify_permission = $this->verifyPermission($request->path(), $para['user_id']);
        if (true !== $verify_permission) {
            return hError('权限不足', 403);
        }
        return $next($request);
    }

    /**
     * 验证用户权限
     * @param $path
     * @param $user_id
     * @return bool
     * @internal param type $request
     */
    public function verifyPermission($path, $user_id)
    {
        $routes = explode('/', $path);
        if (!isset($routes[1])) {
            return false;
        }
        $count = DB::table('erp_menus as m')
            ->join('erp_permission as p', 'p.permissionid', '=', 'm.id')
            ->join('erp_user_to_group as g', 'g.group_id', '=', 'p.groupid')
            ->where(['m.controller' => $routes[0], 'action'=>$routes[1], 'g.user_id' => $user_id])
            ->count();
        return $count > 0;
    }
}
