<?php
/**
 * Created by PhpStorm.
 * User: xiangbohua
 * Date: 2018/4/19
 * Time: 20:19
 */

namespace App\Models\Permission;


use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use App\User;

class Organization extends BaseModel
{
    protected $table = 't_organization';
    public $timestamps = false;

    //获取值架构列表
	public function getStructureList($params){
		$model = DB::table('t_organization');
		//删选子组织
		if (isset($params['parent']) && !empty($params['parent'])) {
			$model->whereRaw('find_in_set("'.$params['parent'].'",org_path)');
		}
        $condition[] = $this->buildPara($params, 'ogname', 'like');
        return $this->getList($model,$condition,$params);
	}

	//获取组织名称
	public function getStructureName(){
		$model = DB::table('t_organization')->pluck('ogname','id')->all();
		return $model;
	}

	//获取拥有自己组织名称
	public function getParentValue(){
		$model = DB::table('t_organization')->pluck('parent')->all();
		//去重
		return array_unique($model);
	}

	
    /**
     * 获取单条
     * @param type $params
     * @return string
     */
	public function getStructure($params = []){
		//获取所有组织
        $parent_arr = DB::table('t_organization')->get()->toArray();
        //格式化组织
        $allStruct = $this->getOrganizationList($parent_arr);

        if (!isset($params['id']) || empty($params['id'])) {
            return ['structs'=>$allStruct];
        }

        $chk = DB::table('t_organization')->where('id', $params['id'])->first();
        if (empty($chk)) {
            return ['structs'=>$allStruct];
        } else {
            return ['item' => $chk,'structs'=>$allStruct];
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
     * 新增和编辑组织架构
     * @param type $params
     * @return boolean
     */
    public function structureSave($params = []){

        if (!isset($params['id']) || empty($params['id'])) {
	       $id = DB::table('t_organization')->select('id')->orderBy('id','desc')->first();
           $path = $id->id+1;
        }else{
            $path = $params['id'];
        }


        if ($params['parent'] != 0) {
            $org_path = DB::table('t_organization')->select('org_path')->where('id', $params['parent'])->first();
            $path = $org_path->org_path.','.$path;
        }
    	$data = [
    		'ogname'	=>		$params['ogname'],
    		'parent'	=>		$params['parent'],
    		'o_desc'	=>		$params['o_desc'],
    		'org_path'	=>		$path,
    		'create_time'	=>		date('Y-m-d H:i:s',time())
    	];

    	if (!isset($params['id']) || empty($params['id'])) {
            $add = DB::table('t_organization')->insert($data);
            if (empty($add)) {
                return false;
            }
        } else {
            $update = DB::table('t_organization')
                    ->where('id', $params['id'])
                    ->update($data);
            if (empty($update)) {
                return false;
            }
        }

    }


    //根据组织user_id获取旗下有组织id
    public static function getOrgPath($user_id = null){

        if (empty($user_id)) {
            return [];
        }

        //获取组织id
        $org_id = User::getUserInfo($user_id)->org_id;
        //获取组织组
        $path = DB::table('t_organization')->where('id', $org_id)->value('org_path');
        // 获取拥有自己组织名称
        $path_id = DB::table('t_organization')->where('org_path' , 'like' , "{$path}%")->pluck('id')->toArray();

        return $path_id;
    }

    //根据org_id 获取当前所属组织
    public static function getOrg($org_id = null){
        if (empty($org_id)) {
            return [];
        }
        
        //组织信息
        $org_info = DB::table('t_organization')->where('id', $org_id)->first();
        return $org_info;
    }
}