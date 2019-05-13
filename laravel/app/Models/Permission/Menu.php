<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 16:45
 */

namespace App\Models\Permission;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class Menu extends BaseModel
{
    protected $table = 't_menus';

    //
    const ENABLED_MAP = [0 => '禁用', 1 => '正常'];
    const STATUS_MAP = [0 => '禁用', 1 => '正常'];
    const MENU_TYPE = ['menu' => '菜单', 'view' => '视图', 'menu_action' => '动作'];
    const ITEM_LEVEL = [1 => '一级菜单', 2 => '二级菜单', 3 => '三级菜单'];

    /**
     * 获取指定用户所有的菜单
     * @param $userId
     * @return \Illuminate\Support\Collection
     */
    public static function getUserMenus($userId)
    {
        $data = DB::table('t_user_role as roles')
            ->join('t_permission as ep', 'roles.role_id', '=', 'ep.role_id')
            ->join('t_menu as em', 'em.id', '=', 'ep.permission_id')
            ->where(['roles.user_id'=> $userId, 'ep.is_enable'=> 1, 'em.m_status'=>1])
            ->groupBy('em.id', 'em.menuname', 'em.controller', 'em.menu_action', 'em.parentid', 'em.itemlevel')
            ->orderBy('em.orderid', 'asc')
            ->get(['em.id', 'em.menuname', 'em.controller', 'em.menu_action', 'em.parentid', 'em.itemlevel']);
        return $data;
    }

    /**
     * 为用户构建菜单数所需的
     * @param $userId
     * @return array
     */
    public static function getUseMenuTree($userId)
    {
        $data = self::getUserMenus($userId);

        return self::formatData($data);
    }

    /**
     * 格式化数据
     *
     * @param $data array 菜单数据
     * @return array
     */
    private static function formatData(&$data)
    {
        $i = 0;
        $tree=[];
        if(count($data) > 0)
        {
            foreach ($data as $value) {
                if($value->itemlevel == 1 && $value->parentid == 0) {
                    $tree[$i]['name'] = $value->menuname;
                    $tree[$i]['id'] = $value->id;
                    if($value->controller != '' && $value->action){
                        $tree[$i]['selfurl'] = $value->controller.'/'.$value->action;
                    }

                    $j = 0;
                    foreach ($data as $oneValue) {
                        if($oneValue->itemlevel == 2 && $value->id == $oneValue->parentid) {
                            $tree[$i]['kids'][$j]['name'] = $oneValue->menuname;
                            $n = 0;
                            foreach ($data as $twoKey=>$twoValue) {
                                if($twoValue->itemlevel == 3 && $oneValue->id == $twoValue->parentid) {
                                    $tree[$i]['kids'][$j]['kids'][$n]['name'] = $twoValue->menuname;
                                    $tree[$i]['kids'][$j]['kids'][$n]['action'] = $twoValue->controller . '/' . $twoValue->menu_action;
                                    $tree[$i]['kids'][$j]['kids'][$n]['rootid'] = $value->id;
                                    //默认给一级，二级添加默认控制器/方法
                                    if(false === empty($value->controller) && false === empty($value->menu_action)) {
                                        $tree[$i]['action'] = $value->controller . '/' . $value->menu_action;
                                    } else {
                                        if ($j == 0 && $n == 0) {
                                            $tree[$i]['action'] = $twoValue->controller . '/' . $twoValue->menu_action;
                                        }
                                    }
                                    if ($n == 0) {
                                        $tree[$i]['kids'][$j]['action'] = $twoValue->controller . '/' . $twoValue->menu_action;
                                    }
                                    ++$n;
                                } else {
                                    continue;
                                }
                            }
                            ++$j;
                        } else {
                            continue;
                        }
                    }
                    ++$i;
                } else {
                    continue;
                }
            }
        }

        return $tree;
    }

    /**
     * 获取菜单列表
     */
    public function get_list($params = []) {
        $condition = [];
        $params['pagesize'] = isset($params['pagesize']) ? $params['pagesize'] : parent::PAGESIZE ;
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $condition['keyword'] =  $params['keyword'];
        }
        if (isset($params['itemlevel']) && !empty($params['itemlevel'])) {
            $condition['itemlevel'] = $params['itemlevel'];
        }
        if (isset($params['menutype']) && !empty($params['menutype'])) {
            $condition['menutype'] = $params['menutype'];
        }
        if (isset($params['m_status']) && is_numeric($params['m_status'])) {
            $condition['m_status'] = $params['m_status'];
        }

        $field = ['id', 'menuname', 'menutype', 'controller', 'menu_action', 'parentid', 'itemlevel', 'm_status'];
        $data = $this->get_menu_list($condition, $field,  $params['pagesize']);
        if (!empty($data['list'])) {
            foreach ($data['list'] as &$v) { // 静态转换
                $v['status_word'] = hMapValue(self::STATUS_MAP, $v['m_status']);
            }
        }
        return $data;
    }

     /**
     * 获取列表
     * @param type $condition 自定义条件
     * @param type $field 列
     * @param type $pagesize 页容，如果为null则不返回分页
     * @return type
     */
    private function get_menu_list($condition = [], $field = [], $pagesize = null) {
        $t_menu = DB::table('t_menu');
         if (isset($condition['keyword']) || !empty($condition['keyword'])) {
                $keyword = $condition['keyword'];
                $t_menu->where(function ($query) use ($keyword) {
                    $query->where('menuname', 'like', "%$keyword%")->orWhere('controller', 'like', "%$keyword%")->orWhere('menu_action','like', "%$keyword%");
                });
            unset($condition['keyword']);
        }

        //between
        if (isset($condition['between']) && !empty($condition['between'])) {
            $t_menu->whereBetween($condition['between'][0],$condition['between'][1]);
            unset($condition['between']);
        }
        $field = array_unique(array_filter($field));
        $select = empty($field) ? ['*'] : $field;
        if (null === $pagesize) {
            return $t_menu->where($condition)->select($select)->orderBy('id')->get();
        }
        $pagesize = intval($pagesize);
        $pagesize <= 0 and ( $pagesize = parent::PAGESIZE);
        $result = json_decode($t_menu->where($condition)->orderBy('id')->paginate($pagesize)->toJson(), true);
        $data = $result['data'];
        unset($result['data']);
        return ['list' => $data, 'page' => $result];
    }


    /**
     * 获取单条
     * @param type $params
     * @return string
     */
    public function get_one($params = []) {
        $data = DB::table('t_menu')->where([['itemlevel', '<>', 4]])->orderBy('itemlevel')->get();
        $menu_list = array();

        foreach ($data as $v) {
            //添加上级菜单名称
            foreach($data as $keyp=>$valuep){
                if($valuep->id == $v->parentid){
                    $v->parentName = $valuep->menuname;
                }
            }
            if(!isset($v->parentName))
                $v->parentName = '# ';

            $menu_list[$v->itemlevel][] = $v;
        }
        if (!isset($params['id']) || empty($params['id'])) {
            return ['item' => [], 'menu_list' => $menu_list];
        }
        $chk = DB::table('t_menu')->where('id', $params['id'])->first();
        if (empty($chk)) {
            return ['item' => [], 'menu_list' => $menu_list];
        } else {
            return ['item' => $chk, 'menu_list' => $menu_list];
        }
    }

    /**
     * 新增和编辑菜单
     * @param type $params
     * @return boolean
     */
    public function do_save($params = []) {
        if ('pro' == _ENV_FILE_PATH_) { // 线上禁止编辑菜单
            return false;
        }
        $data = array(
            'menuname' => $params['menuname'],
            'menutype' => $params['menutype'],
            'controller' => $params['controller'] ?? '',
            'menu_action' => $params['menu_action']  ?? '',
            'itemlevel' => $params['itemlevel'],
            'parentid' => $params['parentid'] ?? 0,
            'm_status' => empty($params['m_status']) ? 0 : $params['m_status'],
            'orderid' => empty($params['orderid']) ? 0 : $params['orderid'],
            'modifiedOn' => time(),
            'modifiedBy' => isset($params['user_id']) ? $params['user_id'] : '',
            'single_select' => isset($params['single_select']) && is_numeric($params['single_select']) ? ($params['single_select'] > 0 ? 1 : 0) : 0,
            'button_position' => empty($params['button_position']) ? 0 : $params['button_position'],
            'button_name' => empty($params['button_name']) ? '' : $params['button_name']
        );
        if (!isset($params['id']) || empty($params['id'])) {
            $data['createdOn'] = time();
            $data['createdBy'] = isset($params['user_id']) ? $params['user_id'] : '';
            $add = DB::table('t_menu')->insert($data);
            if (empty($add)) {
                return false;
            }
        } else {
            $update = DB::table('t_menu')
                    ->where('id', $params['id'])
                    ->update($data);
            if (empty($update)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 根据组获取对应权限的选中情况
     * @param array $params
     * @return type
     */
    public function get_list_for_group($params = []) {
        $params['role_id'] = $params['role_id'];
        $menu_ids = [];
        if (isset($params['role_id']) && !empty($params['role_id'])) {
            $menu_ids = DB::table('t_permission')->select('permission_id')->where('role_id', $params['role_id'])->pluck('permission_id')->toArray();
        }
        $menu_ids = (array) $menu_ids;
        $condition = ['m_status' => 1, 'between' => ['itemlevel',[1, 4]]];
        $field = ['id', 'menuname AS name', 'parentid AS pId','menutype','itemlevel'];
        $list_all = $this->get_menu_list($condition, $field);
        foreach ($list_all as $k => $v) {
            if ($v->menutype == 'view' && $v->itemlevel=='3') {
                 $list_add = (object) [
                    'id'    =>  -$v->id,
                    'name'    =>  $v->name.'-页面',
                    'pId'    =>  $v->id,
                    'menutype'    =>  $v->menutype,
                    'itemlevel'    =>  4
                 ];
                 $list_all[] = $list_add;
            }
            
            if (in_array($v->id, $menu_ids)) {
                $v->checked = 1;
                $v->open = 1;
            }
        }
        return ['list' => json_encode($list_all)];
    }

    /**
     * 分配权限
     * @param type $params
     * @return bool
     */
    public function assign_menu_save($params = []) {
        $menu_ids = isset($params['menu_ids']) ? $params['menu_ids'] : '';
        $menus = explode(',', $menu_ids);
        checkLogic(!empty($menus), '未传入权限ID');

        $data = [];
        foreach ($menus as $mid) {
            if ($mid > 0) {
                $data[] = [
                    'role_id' => $params['role_id'],
                    'permission_id' => $mid,
                    'is_enable' => 1
                ];
            }
        }
        DB::beginTransaction();
        try {
            $group = DB::table('t_permission')->where('role_id', $params['role_id'])->first();
            if (!empty($group)) {
                DB::table('t_permission')->where('role_id', $params['role_id'])->delete();
            }
            DB::table('t_permission')->insert($data);

            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollBack();
            hFormatException($ex);
            return false;
        }
    }
}