<?php
require_once(dirname(__FILE__)."/"."../inc/common.inc.php");	//核心文件
require_once(ROOT_PATH."inc/artic_function.php");		//涉及到文章方面的函数
@include_once(ROOT_PATH."data/ad_cache.php");		//广告变量缓存文件
@include_once(ROOT_PATH."data/label_hf.php");		//标签头部与底部变量缓存文件
@include_once(ROOT_PATH."data/all_fid.php");		//全部栏目配置文件
@include_once(ROOT_PATH."data/article_module.php");	//文章系统创建出来的模型

if(!$webdb[web_open])
{
	$webdb[close_why] = str_replace("\n","<br>",$webdb[close_why]);
	showerr("网站暂时关闭:$webdb[close_why]");
}

/**
*允许哪些IP访问
**/
$IS_BIZ && Limt_IP('AllowVisitIp');


$ch=intval($ch);
unset($listdb,$rs);

//加载JS时的提示语,你可以换成图片,'号要加\
$Load_Msg="<img alt=\"内容加载中,请稍候...\" src=\"$webdb[www_url]/images/default/ico_loading3.gif\">";

$userinfo = $lfjid ? "欢迎回来<span>$lfjid</span> 级别: <span>{$ltitle[$lfjdb[groupid]]}</span> <a href=\"$webdb[www_url]/do/login.php?action=quit\">安全退出</a>" : "<form class=\"login_form\" name=\"form_login\" method=\"post\" action=\"$webdb[www_url]/do/login.php\">帐号：<input name=\"username\" type=\"text\"\ size=\"8\" class=\"input\"/> 密码：<input name=\"password\" type=\"password\"\ size=\"8\" class=\"input\"/> <input value=\"登录\" type=\"submit\" /><input type=\"hidden\" name=\"step\" value=\"2\"><input class=\"radio\" type=\"hidden\" name=\"cookietime\" value=\"86400\" ></form>";

foreach($Fid_db[0] AS $key=>$value){
	if(getsortmid($key)){
		$listsor .= "<div>\r\n<a href=\"list.php?fid=$key\">$value</a> | ";
		$i=0;
		foreach($Fid_db[$key] AS $keys=>$rs){
			$i++;
			if(getsortmid($keys)){
				$listsor .= " <a href=\"list.php?fid=$keys\">$rs</a> ";
			}
			if($i>=3){
				break;
			}
		}
		$listsor .= " <a href=\"list.php?fid=$key\" class=\"m\">更多</a>\r\n</div>\r\n";
	}
}

//列出最热,最新,推荐,及相关文章 
function showartic($type,$rows,$leng){
	global $pre,$webdb,$db,$Fid_db;
	if( !ereg("^(hot|com|new|lastview|like|pic)$",$type) ){
		return ;
	}
	$rows>0 || $rows=7;
	$leng>0 || $leng=60;
	$SQL=" 1 ";
	if($type=='com')
	{
		$SQL.=" AND levels=1 ";
		$ORDER=' list ';
	}
	elseif($type=='pic')
	{
		$SQL.=" AND ispic=1 ";
		$ORDER=' list ';
	}
	elseif($type=='hot')
	{
		$ORDER=' hits ';
	}
	elseif($type=='new')
	{
		$ORDER=' list ';
	}
	elseif($type=='lastview')
	{
		$ORDER=' lastview ';
	}

	if(!$webdb[viewNoPassArticle]){
		$SQL.=' AND yz=1 ';
	}
	
	$SQL=" WHERE $SQL AND mid=0 ORDER BY $ORDER DESC LIMIT $rows";
	$which='*';
	$listdb=list_article($SQL,$which,$leng,$erp);
	return $listdb;
}

function getsortmid($fid){
	global $db,$pre;
	@extract($db->get_one("SELECT fmid FROM {$pre}sort WHERE fid='$fid'"));
	if($fmid=="0"){
		return	true ;
	}else{
		return	false ;
	}
}

function GetAllSonFid($fid){
	global $Fid_db;
	$fid=intval($fid);
	foreach($Fid_db[$fid] AS $key=>$value){
		$show.=",$key";
		$show.=GetAllSonFid($key);
	}
	return $show;
}

function ListThisSorts($fid,$rows,$leng,$page){
	global $pre,$webdb,$db,$Fid_db;
	$page||$page=1;
	$min=($page-1)*$rows;
	$fids=$fid.GetAllSonFid($fid);
	$SQL=" WHERE fid IN ($fids) ORDER BY aid DESC LIMIT $min, $rows";
	$which='*';
	$listdb=list_article($SQL,$which,$leng,$erp);
	return $listdb;
}

function TheWapTopSort(){
	global $pre,$db;
	$query = $db->query("SELECT * FROM {$pre}sort WHERE fup=0 AND fmid=0");
	while($rs = $db->fetch_array($query)){
		$listdb[]=$rs;
	}
	return $listdb;
}

function formatShow($fid,$rows,$leng,$page){
	global $db,$pre,$webdb;
	$listdb=ListThisSorts($fid,$rows,$leng,$page);
	foreach($listdb AS $key=>$rs){
		if($rs[ispic]){
			$_rss=$db->get_one("SELECT content FROM {$pre}reply WHERE aid='$rs[aid]' LIMIT 1");
			$rs[content]=@preg_replace('/<([^<]*)>/is',"",$_rss[content]);	//把HTML代码过滤掉
			$rs[content]=@preg_replace('/ |&nbsp;/is',"",$rs[content]);	//把空格过滤掉
			$rs[content]=get_word($rs[content],120);
			$show="<div>\n<a href=\"bencandy.php?fid=$rs[fid]&id=$rs[aid]\">\n<h3>$rs[title]</h3>\n<img src=\"$rs[picurl]\" width='100' height='75'  onerror=\"this.src='$webdb[www_url]/images/default/nopic.jpg'\"/>\n<p>$rs[content]</p>\n</a>\n</div>\n";
			$pickey=$key;
			break;
		}
	}
	$show.="<ul>\n";
	foreach($listdb AS $key=>$rs){
		if($pickey==$key) continue;
		$show.="<li><a href=\"bencandy.php?fid=$rs[fid]&id=$rs[aid]\">$rs[title]</a></li>\n";
	}
	$show.="</ul>\n";
	return $show;
}
?>