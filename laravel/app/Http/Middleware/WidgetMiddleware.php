<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * 挂件中间件
 */
class WidgetMiddleware
{
    /**
     * 挂件中间件
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (!method_exists($response, 'getData')) {
            return $response;
        }
        $data = $response->getData();
        if (!isset($data->data) || empty($data->data)) {
            $data->data = (object) ['widget'=>[]];
            if (!isset($data->data->widget) || empty($data->data->widget)) {
                $data->data->widget = (object)[];
            }
        }
        $data->data = (object) $data->data;
        $data->data->widget = $this->get_widget($request);
        $response->setData($data);

        return $response;
    }

     /**
     * 根据当前请求返回对应的操作按钮挂件
     * @param type $request
     * @return type
     * 返回示例 text：按钮文言，class：按钮的class，menu_action：按钮调用的方法，view：跳转的页面，menu_action-：按钮前缀，view-：跳转按钮前缀，show_type：显示类型（正常/禁用，根据）
     *        return [
     *            'top_widget' => [// 上区，上去挂件很可能不会存在
     *                    ['text' => '批量删除', 'class' => 'menu_action-menu_delete_all', 'menu_action' => 'system/menu_delete_all', 'show_type' => 'normal/disabled'],
     *            ],
     *            'right_widget' => [// 右区，即每个列表的子项操作
     *                    ['text' => '编辑', 'class' => 'view-menu_edit', 'view' => 'system/menu_edit', 'show_type' => 'normal/disabled'],
     *                    ['text' => '删除', 'class' => 'menu_action-menu_delete', 'menu_action' => 'system/menu_delete', 'show_type' => 'normal/disabled'],
     *            ],
     *            'any_widget' => [// 任意区
     *                    ['text' => '编辑', 'class' => 'view-menu_edit', 'view' => 'system/menu_edit', 'show_type' => 'normal/disabled'],
     *                    ['text' => '删除', 'class' => 'menu_action-menu_delete', 'menu_action' => 'system/menu_delete', 'show_type' => 'normal/disabled'],
     *            ],
     *                // 其他等
     *        ];
     *
     */
    public function get_widget($request)
    {
        // 验证用户ID
        $params = $request->all();
        if (empty($params) || !isset($params['user_id']) || empty($params['user_id'])) {
            return [];
        }
        // 验证请求路径
        $path = $request->path();
        $paths = explode('/', $path);
        if (!isset($paths[1])) {
            return [];
        }
        // 获取验证当前菜单信息
        $menu_id = DB::table('t_menu')->where([
                    ['controller', strtolower($paths[0])],
                    ['menu_action', strtolower($paths[1])],
                    ['itemlevel', 3],
                    ['m_status', 1]
                ])->value('id');
        if (empty($menu_id)) {
            return [];
        }
        // 获取按钮组
        $buttons = DB::table('t_menu')->where([
                    ['parentid', $menu_id],
                    ['itemlevel', 4],
                    ['m_status', 1]
                ])->select('button_name', 'controller', 'menu_action', 'menutype', 'id', 'button_position', 'single_select')
            ->orderBy('orderid', 'ASC')->get();
        if (empty($buttons)) {
            return [];
        }
        // 该用户已拥有的权限
        $owned_menu_ids = [];
        $role_id = DB::table('t_user_role')->where('user_id', $params['user_id'])->pluck('role_id');
        if (!empty($role_id)) {
            $owned_menu_ids = DB::table('t_permission')->whereIn('role_id', $role_id)->where('is_enable', 1)->pluck('permission_id')->toArray();
        }
        $owned_menu_ids = $owned_menu_ids;
        // 获取挂件
        $widget = ['right_widget' => [], 'top_widget' => [], 'any_widget' => [],'view_widget'=>[]];
        foreach ($buttons as $button) {
            if (empty($button->controller) ||
                    empty($button->menu_action) ||
                    empty($button->menutype) ||
                    !in_array(strtolower($button->menutype), ['view', 'action'])) {
                continue;
            }
            $button->menutype = strtolower($button->menutype);
            $button->controller = strtolower($button->controller);
            $button->menu_action = strtolower($button->menu_action);
            if (1 == $button->button_position) {
                $widget['top_widget'][] = [
                    'text' => $button->button_name,
                    'class' => $button->menutype . '-' . $button->controller . '-' . $button->menu_action,
                    $button->menutype => $button->controller . '/' . $button->menu_action,
                    'show_type' => in_array($button->id, $owned_menu_ids) ? 'normal' : 'disabled'
                ];
            } elseif (2 == $button->button_position) {
                $widget['right_widget'][] = [
                    'text' => $button->button_name,
                    'class' => $button->menutype . '-' . $button->controller . '-' . $button->menu_action . ($button->single_select == 1 ? ' btn-single-select' : ''),
                    $button->menutype => $button->controller . '/' . $button->menu_action,
                    'show_type' => in_array($button->id, $owned_menu_ids) ? 'normal' : 'disabled'
                ];
            } elseif (3 == $button->button_position) {
                $widget['any_widget'][] = [
                    'text' => $button->button_name,
                    'class' => $button->menutype . '-' . $button->controller . '-' . $button->menu_action,
                    $button->menutype => $button->controller . '/' . $button->menu_action,
                    'show_type' => in_array($button->id, $owned_menu_ids) ? 'normal' : 'disabled'
                ];
            } elseif (4 == $button->button_position) {
                if (in_array($button->id, $owned_menu_ids)) {
                    $widget['view_widget'][] = [
                        'text' => $button->button_name,
                        $button->menutype => $button->controller . '/' . $button->menu_action
                    ];
                }
            }
        }
        return $widget;
    }
}
