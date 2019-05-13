<?php
namespace App\Http\Controllers\System;


use App\Http\Controllers\LoginRequireController;
use App\Models\Permission\Menu;
use App\Models\Permission\Role;
use App\User;
use App\Models\Permission\Organization;

class SystemController extends LoginRequireController
{
	//菜单列表
    public function menu(Menu $model)
    {
        $list = $model->get_list($this->input);
        $title = [
            'menuname'  =>  '菜单名',
            'menutype'  =>  '菜单类型',
            'controller'  =>  '控制器',
            'menu_action'  =>  '方法',
            'parentid'  =>  '父级ID',
            'itemlevel'  =>  '层级'
        ];
		return $this->returnList('加载成功',$list,$title,'菜单信息');
    }

    /**
     * 渲染菜单新增/编辑页面
     */
    public function menuEdit(Menu $model)
    {
    	$list = $model->get_one($this->input);
    	return hSucceed('加载成功', $list);
    }

    // 新增或修改菜单
    public function menuSave(Menu $model)
    {
        $this->validate($this->currentRequest, [
            'menuname' => 'required',
            'menutype' => 'required',
            'itemlevel' => 'required',
                ], [
            'menuname.required' => '菜单不能为空',
            'menutype.required' => '类型不能为空',
            'itemlevel.required' => '层级不能为空',
        ]);
        $result = $model->do_save($this->input);
        if (true === $result) {
            return hSucceed('编辑成功');
        }
        return hError('编辑失败');
    }

    /**
     * 渲染用户组列表
     */
    public function userRole(Role $model)
    {
    	$list = $model->getRolelist($this->input);
        $title = [
            'role_name' => '角色名',
            'role_desc' => '描述',
            'create_time' => '创建时间',
        ];
        return $this->returnList('加载成功',$list,$title,'角色信息');
    }

    /**
     * 渲染到分配权限页面
     */
    public function assignMenu(Menu $model)
    {
        $this->validate($this->currentRequest, [
            'role_id' => 'required|numeric',
                ], [
            'role_id.required' => '用户组ID不能为空',
            'role_id.numeric' => '用户组ID必须为数字',
        ]);
        return hSucceed('加载成功', $model->get_list_for_group($this->input));
    }

    /**
     * 保存分配权限
     */
    public function assignMenuSave(Menu $model)
    {
        $this->validate($this->currentRequest, [
            'role_id' => 'required|numeric',
                ], [
            'role_id.required' => '用户组ID不能为空',
            'role_id.numeric' => '用户组ID必须为数字',
        ]);
        $result = $model->assign_menu_save($this->input);
        if (true === $result) {
            return hSucceed('保存成功');
        }
        return hError('保存失败');
    }

    /**
     * 渲染用户页面
     */
    public function userList(User $model)
    {
        $data = $model->geuUserlist($this->input);
        $title = [
            'login_name' => '登录名',
            'username' => '用户名',
            'mobile' => '手机号',
            'email' => '邮箱地址',
        ];
        return $this->returnList('加载成功',$data,$title,'用户信息');
    }

    /**
     * 渲染用户新增/编辑页面
     */
    public function userEdit(User $model)
    {
        return hSucceed('加载成功', $model->getUser($this->input));
    }

    /**
     * 新增或修改用户
     */
    public function userSave(User $model)
    {
        $this->validate($this->currentRequest, [
            'login_name' => 'required',
            'username' => 'required',
            'org_id' => 'required',
            'role' => 'required',
                ], [
            'login_name.required' => '登录名不能为空',
            'username.required' => '用户名不能为空',
            'org_id.required' => '职位不能为空',
            'role.required' => '角色不能为空',
        ]);

        //请求为修改时 过滤密码为空判断
        if (!isset($this->input['id']) || empty($this->input['id'])) { 
            $checkOne = $model->getUserByLoginName($this->input['login_name']);
            checkLogic(is_null($checkOne), '登录名不能重复!');
            if (empty($this->currentRequest->user_pass)) {
                return hError('密码不能为空');
            }
        }

        $result = $model->userSave($this->input);
        if (true === $result) {
            return hSucceed('编辑成功');
        }
        return hError($result);
    }

    /**
     * 个人修改密码
     */
    public function updatePassword(User $model)
    {
        if (isset($this->input['type'])) {
            $this->validate($this->currentRequest, [
            'user_pass' => 'required',
            'new_user_pass' => 'required',
            'check_user_pass' => 'required',
                ], [
            'user_pass.required' => '原密码不能为空',
            'new_user_pass.required' => '新密码不能为空',
            'check_user_pass.required' => '确认密码不能为空',
        ]);
            if ($this->input['new_user_pass'] != $this->input['check_user_pass']) {
                return hError('确认密码与新密码不一致');
            }
            $result = $model->updatePassword($this->input);
            return hSucceed($result);
        }
        return hSucceed('加载成功');
    }

    /**
     * 组织架构
     */
    public function structureList(Organization $model){
        $list = $model->getStructureList($this->input);
        //获取组织名称
        $structureName = $model->getStructureName();
        //获取拥有自己组织名称
        $parentValue = $model->getParentValue();

        // 格式化列表
        foreach ($list['list'] as $k => $v) {
            if ($list['list'][$k]['parent'] == 0) {
                $list['list'][$k]['parent'] = $v['ogname'];
            }else{
                $list['list'][$k]['parent'] = $structureName[$v['parent']];
            }
        }

        //处理父级组织
        $parentName = [];
        foreach ($parentValue as $k => $v) {
            if ($v == 0) {
                continue;
            }else{
                $parentName[$v] = $structureName[$v];
            }
        }

        $list['parentName'] = $parentName;

        $title = [
            'ogname'    =>      '组织名称',   
            'parent'    =>      '所属组织',   
            'o_desc'    =>      '描述',   
            'create_time'    =>      '创建时间'   
        ];
        return $this->returnList('加载成功',$list,$title,'组织信息');
    }

    /**
     * 渲染组织新增/编辑页面
     */
    public function structureEdit(Organization $model)
    {
        $list = $model->getStructure($this->input);
        return hSucceed('加载成功', $list);
    }

    public function structureSave(Organization $model){
        $this->validate($this->currentRequest, [
            'ogname' => 'required',
            'o_desc' => 'required',
                ], [
            'ogname.required' => '组织名称',
            'o_desc.required' => '组织描述',
        ]);

        $result = $model->structureSave($this->input);
        if (true === $result) {
            return hSucceed('编辑成功');
        }
        return hError($result);
    }
}