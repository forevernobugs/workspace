<?php
// +----------------------------------------------------------------------
// | Minishop [ Easy to handle for Micro businesses ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.qasl.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tangtanglove <dai_hang_love@126.com> <http://www.ixiaoquan.com>
// +----------------------------------------------------------------------

use think\Db;

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

/**
 * 系统根目录
 */
function root_path()
{
    return __ROOT__;
}   

/**
 * 创建盐
 * @author tangtanglove <dai_hang_love@126.com>
 */
function create_salt($length=-6)
{
    return $salt = substr(uniqid(rand()), $length);
}

/**
 * 创建uuid,系统内唯一标识符
 * @author tangtanglove <dai_hang_love@126.com>
 */
function create_uuid()
{
    mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);// "-"
    $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
    return $uuid;
}

/**
 * 获取uuid,系统内唯一标识符
 * @author tangtanglove <dai_hang_love@126.com>
 */
function get_uuid($model,$map)
{
    return Db::name($model)->where($map)->value('uuid');
}

/**
 * minishop md5加密方法
 * @author tangtanglove <dai_hang_love@126.com>
 */
function minishop_md5($string,$salt)
{
	return md5(md5($string).$salt);
}

/**
 * 获取用户信息
 * @author  tangtanglove
 */
function get_userinfo($uid = '',$field = '')
{
    if (empty($uid)) {
        $uid = session('user_auth.uid');
    }
    // 查询用户信息
    $userInfo = Db::name('Users')->where(['id'=>$uid,'status'=>1])->cache(true)->find();
    if ($field) {
        return $userInfo[$field];
    } else {
        return $userInfo;
    }
}

/**
 * 获取分类信息
 * @author  tangtanglove
 */
function get_termsinfo($id = '',$field = 'name')
{
    return Db::name('Terms')->where(['id'=>$id])->cache(true)->value($field);
}

/**
 * 获取文章所属分类或tags
 * @author  tangtanglove
 */
function get_posts_terms($postid = '',$type = 'category')
{
    // 列表数据
    $list = Db::name('TermRelationships')
    ->alias('a')
    ->join('term_taxonomy b','b.id = a.term_taxonomy_id','LEFT')
    ->join('terms c','c.id = b.term_id','LEFT')
    ->where(['a.object_id'=>$postid,'b.taxonomy'=>$type])
    ->field('b.id,c.name')
    ->cache(true)
    ->select();
    $text = '';
    if (!empty($list)) {
        foreach ($list as $key => $value) {
            $text = $text.$value['name'].',';
        }
    } else {
        $text = '—';
    }
    return trim($text,',');
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

/**
 * 获取菜单分类信息
 * @author  tangtanglove
 */
function get_menusinfo($id = '',$field = 'title')
{
    return Db::name('Menu')->where(['id'=>$id])->cache(true)->value($field);
}

/**
 * 获取当前文章状态 'publish','pending','draft','trash'
 * @author  tangtanglove
 */
function get_posts_status($postStatus)
{
    switch ($postStatus) {
        case 'publish':
            $result = '已发布';
            break;
        case 'pending':
            $result = '待审';
            break;
        case 'draft':
            $result = '草稿';
            break;
        case 'trash':
            $result = '回收站';
            break;
        default:
            $result = '无';
            break;
    }
    return $result;
}

/**
 * 获取用户状态 'forbid','start','delete'
 * @author 完美°ぜ界丶
 */
function get_user_status($userStatus)
{
    switch ($userStatus) {
        case '2':
            $result = '禁用';
            break;
        case '1':
            $result = '正常';
            break;
        case '-1':
            $result = '删除';
            break;
        default:
            $result = '无';
            break;
    }
    return $result;
}

/**
 * 获取后台菜单选中状态
 * @author  tangtanglove
 */
function get_menu_navbar_active($menuid)
{
    // 查询信息
    $menuInfo = Db::name('Menu')->where(['id'=>$menuid])->cache(true)->find();
    if (empty($menuInfo['pid'])) {
        // 根节点
        $result = Db::name('Menu')->where(['pid'=>$menuid])->where('instr(\''.$_SERVER['REQUEST_URI'].'\',`url`)>0')->cache(true)->find();
        if ($result) {
            return 'active';
        }
    } else {
        if ($_SERVER['REQUEST_URI'] === url($menuInfo['url'])) {
            return 'active';
        }
    }
}

/**
 * 获取用户组的状态,1正常,-1禁用
 * @param  [int] $status [状态值]
 * @author vaey
 */
function get_group_status($status)
{
    if($status==1){
        return '<span class="label label-success">正常</span>';
    }elseif($status==-1){
        return '<span class="label label-danger">禁用</span>';
    }
}

/**
 * 获取文章单一封面
 * @author tangtanglove
 */
function get_posts_cover($uuid)
{
    // 查询数据
    $map['uuid'] = $uuid;
    $map['name'] = 'cover_path_1';
    $postsCover  = Db::name('KeyValue')->where($map)->value('value');
    if($postsCover){
        return __ROOT__.str_replace('./','/',$postsCover);
    }else{
        $path = config('theme_path').'/index/images/irc_defaut.png';
        return str_replace('./','/',$path);
    }
    
}

/**
 * 获取图片
 * @author 矢志bu渝 
 * $uuid key_value表中的唯一标识,定位图片
 */
function get_picture($uuid='') {

    if( empty($uuid) ) {
        return false;
    }
    $map['uuid'] = $uuid;
    $map['name'] = "cover_path_1";//只取name值为cover_path_1的图片，多图时取第一张

    $path        = Db::name('KeyValue')->where($map)->value('value');
    return $path;
}

/**
 * 权限判断，设置菜单对某个用户是否可见
 * @author vaey
 * @param  [type] $ruleId      [当前菜单id]
 * @param  [type] $rules       [权限id组]
 * @return [type]              [description]
 */
function check_menu_auth($ruleId,$rules)
{   
    //权限判断
    if(is_array($rules)){
        if(!in_array($ruleId,$rules)){
            return false;
        }else{
            return true;
        }
    }else{
        if($rules == 1){ //超级管理员，拥有所有权限
            return true;
        }else{
            return false;
        }
    }

}

/**
 * 获取当前用户权限，控制菜单对某个用户是否显示
 * @author vaey
 * @return [type] [description]
 */
function get_menu_auth()
{
    //查询当前登录用户的uuid
    $uuid           = Db::name('Users')->where('id',UID)->value('uuid');
    $keyValue       = Db::name('KeyValue')->where('uuid',$uuid)->find();
    if($keyValue && $keyValue['value']==1){
        //超级管理员，直接返回
        return 1; //拥有所有权限
    }
    //获取当前登录用户所在的用户组(可以是多组)
    $group          = Db::name('UserGroupAccess')->where('uid',UID)->select();
    if(!$group){
        return 2; //没有任何权限
    }
    //所有权限数组
    $rules_array = [];
    $arr = [];
    foreach ($group as  $v) {
        $rules = Db::name('UserGroup')->where('id',$v['group_id'])->where('status',1)->value('rules');
        if($rules){
            $arr = explode(',',$rules);
        }
        $rules_array = array_merge($rules_array, $arr);  
    }
    //去除重复值
    $rules_array = array_unique($rules_array);
    return $rules_array;
}    
	

/**
 * 手机列表页面适配器
 * @author tangtanglove
 */
function wap_list_adapter($data,$showType = 'one_cover')
{
    foreach ($data as $key => $value) {
        $data[$key]['updatetime'] = date('Y-m-d H:i:s',$value['updatetime']);
        $data[$key]['createtime'] = date('Y-m-d H:i:s',$value['createtime']);
        $data[$key]['nickname']   = get_userinfo($value['uid'],'nickname');
        $coverList = select_key_value('posts.cover',$value['uuid']);
        // 获取封面数量
        $coverNum = count($coverList);
        if($coverNum)
        {
            switch ($showType) {
                case 'one_cover':
                    // 单图模式
                    $data[$key]['cover_path_1'] = create_thumb('.'.$coverList['cover_path_1'],'',200,110,3);
                    $data[$key]['cover_path_1'] = str_replace('./', 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/', $data[$key]['cover_path_1']);
                    break;
                
                default:
                    // 默认程序自动判断
                    if(3<=$coverNum) {
                        $data[$key]['cover_path_1'] = create_thumb('.'.$coverList['cover_path_1'],'',200,110,3);
                        $data[$key]['cover_path_1'] = str_replace('./', 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/', $data[$key]['cover_path_1']);

                        $data[$key]['cover_path_2'] = create_thumb('.'.$coverList['cover_path_2'],'',200,110,3);
                        $data[$key]['cover_path_2'] = str_replace('./', 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/', $data[$key]['cover_path_2']);

                        $data[$key]['cover_path_3'] = create_thumb('.'.$coverList['cover_path_3'],'',200,110,3);
                        $data[$key]['cover_path_3'] = str_replace('./', 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/', $data[$key]['cover_path_3']);
                    } else {
                        $data[$key]['cover_path_1'] = create_thumb('.'.$coverList['cover_path_1'],'',200,110,3);
                        $data[$key]['cover_path_1'] = str_replace('./', 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/', $data[$key]['cover_path_1']);
                    }
                    break;
            }
        }
    }
    return $data;
}

/**
 * 手机详情页面适配器
 * @author tangtanglove
 */
function wap_detail_adapter($data)
{
    // $data['updatetime'] = date('Y-m-d H:i:s',$data['updatetime']);
    $data['createtime'] = date('Y-m-d H:i:s',$data['createtime']);
    // $data['nickname']   = get_userinfo($data['uid'],'nickname');
    //内容图片生成适应手机图片
    $data['content']    = create_mobile_thumb($data['content'],350,'');
    //内容图片加域名
    $data['content']    = replace_image_url($data['content'],'http://'.$_SERVER['HTTP_HOST'].__ROOT__);
    return $data;
}


/**
 * 生成缩略图
 * @author tangtanglove
 * @param string $image_path 图片路径
 * @param string $thumb_path 缩略图路径
 */
function create_thumb($image_path,$thumb_path,$width,$height,$thumb_type = 1)
{
    if (empty($image_path)) {
        $this->error('图片路径不能为空！');
    }

    if (empty($thumb_path)) {
        //如果不定义缩略图路径，则以thumb_+原图片名命名
        $list = explode('/', $image_path);
        $key = count($list)-1;
        //定义缩略图名称
        $thumb_name = 'thumb_'.$list[$key];
        $thumb_path = str_replace($list[$key],'',$image_path).$thumb_name;
    }

    if (is_file($image_path)) {
        //不存在缩略图则创建
        if (!is_file($thumb_path)) {
            $image = \think\Image::open($image_path);
            $image->thumb($width, $height,$thumb_type)->save($thumb_path);
        }
        return $thumb_path;
    }else{
        return $image_path;
    }

}

/**
 * 内容图片生成适应手机图片
 * @author tangtanglove
 * @param string $image_path 图片路径
 * @param string $thumb_path 缩略图路径
 */
function create_mobile_thumb($content,$width,$height)
{
    $preg_str = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
    preg_match_all($preg_str,$content,$match);
    foreach ($match[1] as $key => $value) {
        $content_mobile_thumb = create_thumb('.'.$value,'',$width,$height,1);
        $content = str_replace($value,trim($content_mobile_thumb,'.'),$content);
    }
    return $content;
}

/** 
 * 替换内容中的图片 添加域名 
 * @param  string $content 要替换的内容 
 * @param  string $strUrl 内容中图片要加的域名 
 * @return string   
 */  
function replace_image_url($content = null, $strUrl = null) {
    if ($strUrl) {  
        //提取图片路径的src的正则表达式 并把结果存入$matches中    
        preg_match_all("/<img(.*)src=\"([^\"]+)\"[^>]+>/isU",$content,$matches);  
        $img = "";    
        if(!empty($matches)) {    
        //注意，上面的正则表达式说明src的值是放在数组的第三个中    
        $img = $matches[2];    
        }else {    
            $img = "";    
        }  
        if (!empty($img)) {    
            $patterns= array();    
            $replacements = array();    
            foreach($img as $imgItem){
                $final_imgUrl = $strUrl.$imgItem;
                //如果不包含http://则认为为远程图片
                if (!(strpos($imgItem, 'http://') !== false)) {
                    $replacements[] = $final_imgUrl;
                    $img_new = "/".preg_replace("/\//i","\/",$imgItem)."/";
                    $patterns[] = $img_new;
                }
            }    

            //让数组按照key来排序
            ksort($patterns);    
            ksort($replacements);    

            //替换内容    
            $vote_content = preg_replace($patterns, $replacements, $content);  
        
            return $vote_content;  
        }else {  
            return $content;  
        }                     
    } else {  
        return $content;  
    }  
}

/**
 * 订单状态
 */
function get_order_status()
{
    $order_status = array(
            'nopaid'    =>'待付款',
            'paid'      =>'已付款',
            'shipped'   =>'已发货',
            'completed' =>'已完成',
            'cancel'    =>'已取消' 
    );
  
    return $order_status;
}

/**
 * 支付类型
 */
function get_pay_type()
{
    $pay_type = array(
            'alipay' =>'支付宝',
            'wxpay'  =>'微信',
    );
    return $pay_type;
}


/** 
 * 加载接口配置
 * @param  string $name   配置接口名称 
 */
 function load_config(){
    
    $list = Db::name('Apiconfig')->select();
         
    foreach ($list as $key => $value) {
        // config('配置参数','配置值');
        config($value['key'],$value['value']);
    }    
} 


/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 根据自己的分类pid，获取所有同级分类
 * @return [type] [description]
 */
function get_self_category($pid){
    $category = 
    Db::name('TermTaxonomy')
    ->alias('a')
    ->join('terms b','b.id= a.term_id','LEFT')
    ->where('pid',$pid)->select();
    return $category;
}

/**
 * 根据自己的分类pid，获取上级分类
 * @return [type] [description]
 */
function get_parent_category($pid,$item){
    $category = 
    Db::name('TermTaxonomy')
    ->alias('a')
    ->join('terms b','b.id= a.term_id','LEFT')
    ->where('a.id',$pid)->find();
    return $category[$item];
}

//获取上一篇
function get_pre($id,$cid){
    $map['term_taxonomy_id'] = $cid;
    $map['object_id'] = array('lt',$id);
    $data = Db::name('TermRelationships')->where($map)->order('object_id desc')->find();
    if($data){
        $info = Db::name('Posts')->where('id',$data['object_id'])->find();
        if($info){
            $url = url('Article/detail?id='.$info['id'].'&category='.$cid);
            return "<a href='".$url."'>".$info['title']."</a>";
        }else{
            return "没有了!";
        }
    }else{
        return "没有了!";
    }
}
//获取下一篇
function get_next($id,$cid){
    $map['term_taxonomy_id'] = $cid;
    $map['object_id'] = array('gt',$id);
    $data = Db::name('TermRelationships')->where($map)->order('object_id asc')->find();
    if($data){
        $info = Db::name('Posts')->where('id',$data['object_id'])->find();
        if($info){
            $url = url('Article/detail?id='.$info['id'].'&category='.$cid);
            return "<a href='".$url."'>".$info['title']."</a>";
        }else{
            return "没有了!";
        }
    }else{
        return "没有了!";
    }
}

/**
 * 根据自身父id ，获取同级所有page
 * @return [type] [description]
 */
function get_self_page($pid){
    $map['type']    = "page";
    $map['pid']     = $pid;
    $data = Db::name('Posts')->where($map)->order('level asc')->select();
    return $data;
}

/**
 * 获取收藏总数
 * @return [type] [description]
 */
function get_collection_count($id){
    if($id){
        $count = Db::name('GoodsCollection')->where(['goods_id'=>$id])->count();
        return $count;
    }else{
        return 0;
    }
    
}

/**
 * 或取当前用户是否收藏了该商品，返回图标
 * @return [type] [description]
 */
function get_collection_ico($id){
    if($id){
        $uid = session('index_user_auth.uid');
        if($uid){
            $info = Db::name('GoodsCollection')->where(['goods_id'=>$id,'uid'=>$uid])->find();
            if($info){
                return 'collection_big.png';
            }else{
                return "collectionone.png";
            }
        }
        return "collectionone.png";
    }else{
        return "collectionone.png";
    }
    
}