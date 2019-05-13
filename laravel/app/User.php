<?php

namespace App;

use App\Models\Logs\UserLoginLog;
use App\Models\System\Configuration;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use App\Models\Permission\Organization;
use App\Models\Permission\Role;
use App\Models\BasicInfo\Warehouse;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    public $timestamps = false;
    use Authenticatable, Authorizable;

    const ENABLED_MAP = [0 => '禁用', 1 => '正常'];
    const STATUS_MAP = [0 => '禁用', 1 => '正常'];
    const MENU_TYPE = ['menu' => '菜单', 'view' => '视图', 'menu_action' => '动作'];
    const ITEM_LEVEL = [1 => '一级菜单', 2 => '二级菜单', 3 => '三级菜单'];

    protected $table = 't_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_pass',
    ];

    /**
     * 检查用户是否能够登陆
     * @param $userName
     * @param $password
     * @return string
     */
    public static function checkUser($userName, $password)
    {
        $userInfo = User::where('login_name','=', $userName)->orWhere('mobile',$userName)->first();
        if ($userInfo === null) {
            return '用户未找到';
        }
        if ($userInfo->enabled !== 1) {
            return '用户已停用';
        }
        $userName = $userInfo->login_name;
        if (strtolower($userInfo->user_pass) != md5($userName . $password)) {
            return '密码错误';
        }

        return $userInfo;
    }

    /**
     * 更新用户Token
     */
    public function updateToken()
    {
        $updateToken = Configuration::getConfig('UpdateTokenWhenLogin');
        $token = $this->token;
        if ($updateToken === '1' || empty($token)) {
            $token = md5($this->id . uniqid());
            $this->token = $token;
            $this->save();
            UserLoginLog::saveLog($this->login_name, true, '登陆成功');
        }
        return $this->token;
    }

    /**
     * 获取loginName
     * @param $userId
     * @return mixed
     */
    public static function getLoginName($userId)
    {
        $loginName = DB::table('t_user')->where('id', $userId)->value('login_name');
        checkLogic(!empty($loginName), '未找到用户');
        return $loginName;
    }

    /**
     * 传入用户用户订单，返回用户常用信息
     * @param $userId
     * @return mixed
     */
    public static function getUserInfo($userId)
    {
        $userInfo = DB::table('t_user')
            ->where('id', $userId)
            ->first(['id', 'login_name', 'org_id', 'username', 'mobile', 'email','token']);
        checkLogic(!empty($userInfo), '未找到用户');
        return $userInfo;
    }

    /**
     * 获取用户更多信息
     *@param $userId
     *@return mixed
     */
    public static function getUserDetail($userId = null)
    {
        if (empty($userId)) {
            return [];
        }
        $userInfo = DB::table('t_user')->where('id', $userId)->first();
        $org = Organization::getOrg($userInfo->org_id);
        $userInfo->ogname = isset($org->ogname) ? $org->ogname : '';
        $warehouse = Warehouse::getWarehouseOne($userInfo->org_id);
        $userInfo->warehouse_code = isset($warehouse->warehouse_code) ? $warehouse->warehouse_code : '';
        $userInfo->dealer_id = isset($warehouse->dealer_id) ? $warehouse->dealer_id : '';
        $userInfo->warehouse_name = isset($warehouse->warehouse_name) ? $warehouse->warehouse_name : '';
        $role = Role::getUserRoleInfo($userId);
        $userInfo->user_id = $userId;
        $userInfo->role_id = $role[0]->role_id;
        $userInfo->role_name = $role[0]->role_name;
        $userInfo->role_desc = $role[0]->role_desc;
        $userInfo->role_code = $role[0]->role_code;
        $userInfo->role_info['role_id'] = array_column($role, 'role_id');
        $userInfo->role_info['role_name'] = array_column($role, 'role_name');
        $userInfo->role_info['role_desc'] = array_column($role, 'role_desc');
        $userInfo->role_info['role_code'] = array_column($role, 'role_code');
        $org_node = Organization::getOrgPath($userId);
        $userInfo->org_node = isset($org_node) ? $org_node : '' ;
        return $userInfo;
    }

    /**
     * 获取用户列表
     * @param type $params
     */
    public function geuUserlist($params = []) {
        $condition = [];
        $params['pagesize'] = isset($params['pagesize']) ? $params['pagesize'] : parent::PAGESIZE ;
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $condition['keyword']['keyword'] = $params['keyword'];
            $condition['keyword']['filed'] = [
                'login_name',
                'mobile',
                'username'
            ];
        }
        if (isset($params['org_id']) && !empty($params['org_id'])) {
            $condition['org_id'] = $params['org_id'];
        }
        if (isset($params['enabled']) && is_numeric($params['enabled'])) {
            $condition['enabled'] = $params['enabled'] == 2 ? 0 : $params['enabled'];
        }

        $data = $this->geuUserlistDo($condition, [], $params['pagesize']);
        if (!empty($data['list'])) {
            foreach ($data['list'] as &$v) { // 静态转换
                $v['status_word'] = hMapValue(self::ENABLED_MAP, $v['enabled']);
            }
        }
        return $data;
    }

    /**
     * 获取列表
     * @param type $where 条件
     * @param type $field 列
     * @param type $pagesize 页容，如果为null则不返回分页
     * @return type
     */
    private function geuUserlistDo($where = [], $field = [], $pagesize = null) {
        $t_user = DB::table('t_user');
        if (isset($where['keyword']) && !empty($where['keyword'])) {
            $keyword = $where['keyword']['keyword'];
            $filed = $where['keyword']['filed'];
            $t_user->where(function ($query) use ($keyword,$filed) {
                foreach ($filed as $key => $value) {
                    if ($key==0) {
                       $query->where($value, 'like', "%$keyword%");
                    }else{
                       $query->orWhere($value, 'like', "%$keyword%");
                    }
                }
            });
            unset($where['keyword']);
        }
        $organization = DB::table('t_organization')->orderBy('id')->get();
        if (null === $pagesize) {
            return $t_user->where($where)->orderBy('id')->get();
        }
        $pagesize = intval($pagesize);
        $pagesize <= 0 and ( $pagesize = parent::PAGESIZE);
        $result = json_decode($t_user->where($where)->orderBy('enabled','desc')->paginate($pagesize)->toJson(), true);
        $data = $result['data'];
        unset($result['data']);
        return ['list' => $data, 'page' => $result,'organization' => $organization];
    }

    /**
     * 获取单条
     * @param type $params
     * @return string
     */
    public function getUser($params = []) {
        //获取所有组织
        $parent_arr = DB::table('t_organization')->get()->toArray();
        //格式化组织
        $allStruct = $this->getOrganizationList($parent_arr);

        $role = DB::table('t_role')->orderBy('role_name')->get();

        if (!isset($params['id']) || empty($params['id'])) {
            return ['role' => $role,'structs'=>$allStruct];
        }

        $chk = DB::table('t_user')->where('id', $params['id'])->first();
        if (empty($chk)) {
            return ['role' => $role,'structs'=>$allStruct];
        } else {
            $userRole = DB::table('t_user_role')->where('user_id', $params['id'])->select(['role_id'])->get();
            return ['item' => $chk,'role' => $role,'structs'=>$allStruct, 'user_role'=>$userRole];
        }
    }

    /**
     * 格式化组织结构
     */
    public function getOrganizationList( $data , $parent_id = 0 , $level = 1 ){
        global $tree;
        foreach($data as $key => $val) {
            if($val->parent == $parent_id) {
                $flg = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",$level);
                $val->ogname = $flg.$val->ogname;
                $tree[] = $val;
                $this->getOrganizationList($data , $val->id ,$level+1);
             }
        }
        return $tree;
    }

        /**
     * 用户编辑
     * @param type $params
     * @return boolean|string
     */
    public function userSave($params = []) {

        DB::beginTransaction();
        try{

            $data = array(
                'username' => $params['username'],
                'mobile' => isset($params['mobile'])?$params['mobile']:'',
                'email' => isset($params['email'])?$params['email']:'',
                'org_id' => $params['org_id'],
                'enabled' => empty($params['enabled']) ? 0 : $params['enabled'],
              
            );
            if (!isset($params['id']) || empty($params['id']) || !is_numeric($params['id'])) {
                if(empty($params['user_pass'])){
                    return "user_pass";
                }
                $data['create_time'] = date('Y-m-d H:i:s',time());
                $data['last_login_time'] = date('Y-m-d H:i:s',time());
                $data['login_time'] = date('Y-m-d H:i:s',time());
                $data['login_count'] = 0;
                $data['login_name'] = $params['login_name'];
                $data['user_pass'] = md5($params['login_name'].$params['user_pass']);
                $data['token'] = md5(uniqid());

                $this->login_name = $data['login_name'];
                $this->username = $data['username'];
                $this->mobile = $data['mobile'];
                $this->email = $data['email'];
                $this->org_id = $data['org_id'];
                $this->enabled = $data['enabled'];
                $this->user_pass = $data['user_pass'];
                $this->save($data);

                $roles = [];
                foreach (explode(',', $params['role']) as $role)
                {
                    if(!empty($role))
                        $roles[] = ['user_id'=>$this->id, 'role_id'=>$role ];
                }
                DB::table('t_user_role')->insert($roles);
                DB::commit();
                return true;

            } else {
                if(!empty($params['user_pass'])){
                    $data['user_pass'] = md5($params['login_name'].$params['user_pass']);
                }

                //修改最后一次登录时间
                $data['last_login_time'] = date('Y-m-d H:i:s',time());

                //禁止修改现有用户的登录名
                if(isset($data['login_name']))
                    unset($data['login_name']);
                
                //更新用户信息
                $update = DB::table('t_user')->where('id', $params['id'])->update($data);

                //删除原有用角色信息
                DB::table('t_user_role')->where('user_id', $params['id'])->delete();

                //插入新用户角色信息
                $roles = [];
                foreach (explode(',', $params['role']) as $role)
                {
                    if(!empty($role))
                        $roles[] = ['user_id'=>$params['id'], 'role_id'=>$role ];
                }
                DB::table('t_user_role')->insert($roles);
                if ($update) {
                    DB::commit();
                    return true;
                }
                else{
                    DB::rollback();
                    return '保存失败';
                }
            }
        }
        catch (\Exception $e)
        {
            DB::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 个人中心 修改密码
     * @param type $params
     */
    public function updatePassword($params = []){
        $user_id = isset($params['user_id']) ? $params['user_id'] : '';
        $user_info = DB::table('t_user')->where('id', $user_id)->first();
        if(empty($user_info)){
            return '用户信息错误!';
        }
        //判断是否是重置密码
        if(false === isset($params['_type']) && @$params['_type'] != 'setpwd'){
            if (strtolower($user_info->user_pass) != md5($user_info->login_name . $params['user_pass'])) {
                return '原密码错误!';
            }
        }
        $update = DB::table('t_user')
                    ->where('id', $user_id)
                    ->update(['user_pass' => md5($user_info->login_name . $params['new_user_pass']), 'is_new' => 0]);
        if (empty($update)) {
            return '修改失败!';
        }
        return '修改成功!<br>新密码为:    '.$params['new_user_pass'];
    }

    public function getUserByLoginName($login_name)
    {
        return $userInfo = User::where('login_name','=', $login_name)->first();
    }
}