<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tangtanglove <dai_hang_love@126.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Db;
use think\Request;
use think\controller\Rest;

/**
 * 文章控制器
 * @author  tangtanglove <dai_hang_love@126.com>
 */
class Article extends Base
{
    /**
    * 文章列表控制器
    * @author  tangtanglove <dai_hang_love@126.com>
    */
    public function lists_get_json()
    {
        // 当前页码
        $page       = input('page',1);
        // 分页数量
        $num        = input('num',10);
        // 栏目缩略名
        $category   = input('category');
        // 条件
        $where['a.status'] = 'publish';
        $where['a.type']   = 'post';
        if (!empty($category)) {
            if (!is_numeric($category)) {
                // 读取数据
                $catid = Db::name('TermTaxonomy')
                ->alias('a')
                ->join('terms b','b.id= a.term_id','LEFT')
                ->where(['b.slug'=>$category])
                ->value('a.id');
                if (empty($catid)) {
                    return $this->error('分类不存在！');
                }
            } else {
                $catid = $category;
            }
            $children = model('Category')->getAllChildrenId($catid);
            if (!empty($children)) {
                //如果有子分类
                //分割分类
                $children = explode(',', $children);
                //将当前分类的文章和子分类的文章混合到一起
                $cates = $children;
                //合并分类
                array_push($cates, $catid);
                //组合条件
                $where['b.term_taxonomy_id'] = array('in',$cates);
            }else{
                $where['b.term_taxonomy_id'] = array('eq',$catid);
            }
        }
        $list = Db::name('Posts')
    	->alias('a')
    	->join('term_relationships b','b.object_id = a.id','LEFT')
        ->where($where)
        ->order('id desc')
        ->page($page,$num)
        ->field('id,uid,uuid,title,comment,updatetime,createtime')
        ->select();
        if(!empty($list)) {
            return $this->restSuccess('获取成功！',wap_list_adapter($list));
        } else {
            return $this->restError('无数据！');
        }
    }

    /**
    * 文章详情控制器
    * @author  tangtanglove <dai_hang_love@126.com>
    */
    public function detail()
    {
        $id = input('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        // 读取数据
        $postInfo = Db::name('Posts')->where(['id'=>$id])->find();
        if (empty($postInfo)) {
            return $this->restError('内容不存在！');
        }
        return $this->restSuccess('获取成功！',wap_detail_adapter($postInfo));
    }

}
