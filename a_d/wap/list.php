<?php
require_once(dirname(__FILE__)."/"."global.php");
$page<1 && $page=1;
if(!$fid){
	showerr("栏目FID不存在");
}

get_guide($fid);	//栏目导航

//栏目配置文件
$fidDB=$db->get_one("SELECT S.*,M.alias AS M_alias,M.config AS M_config,M.iftable FROM {$pre}sort S LEFT JOIN {$pre}article_module M ON S.fmid=M.id WHERE S.fid='$fid'");
if(!$fidDB){
	showerr("栏目ID有误");
}
$fidDB[M_alias] || $fidDB[M_alias]='文章';
$fidDB[M_config]=unserialize($fidDB[M_config]);
$fidDB[config]=unserialize($fidDB[config]);
$fidDB[descrip]=En_TruePath($fidDB[descrip],0);
if($fidDB[type]==2){
	$rsdb[content]=$fidDB[descrip];
}

$fupId=intval($fidDB[type]?$fid:$fidDB[fup]);
/**
*栏目配置文件检查
**/
check_fid($fidDB);
//显示子分类
$listdb_moresort=ListMoreSort();

//列表页多少篇文章,栏目设置的话.以栏目为标准,否则与系统为标准,系统不存在就默认20
$rows=$fidDB[maxperpage]?$fidDB[maxperpage]:($webdb[list_row]?$webdb[list_row]:20);	

$listdb=ListThisSort($rows,$webdb[ListLeng]?$webdb[ListLeng]:50);		//本栏目文章列表
$page_sql=$webdb[viewNoPassArticle]?'':' AND yz=1 ';
$erp=$fidDB[iftable]?$fidDB[iftable]:"";
$showpage=getpage("{$pre}article$erp","WHERE fid=$fid $page_sql","list.php?fid=$fid",$rows);	//文章列表分页


$thisrow=6;
$lengs=60;
$pages||$pages=1;
$sortdb=ListThisSorts($fid,$thisrow,$lengs,$pages);
$fids=$fid.GetAllSonFid($fid);
if($job=="show"){
	if(!$sortdb){
		echo "nodate";
	}else{
		foreach($sortdb AS $key=>$rs){
			$_rss=$db->get_one("SELECT content FROM {$pre}reply WHERE aid='$rs[aid]' LIMIT 1");
			$rs[content]=@preg_replace('/<([^<]*)>/is',"",$_rss[content]);	//把HTML代码过滤掉
			$rs[content]=@preg_replace('/ |&nbsp;/is',"",$rs[content]);	//把空格过滤掉
			$rs[content]=get_word($rs[content],120);
			$rs[showimg]=$rs[ispic]?"<img src=\"$rs[picurl]\" width='100' height='75'  onerror=\"this.src='$webdb[www_url]/images/default/nopic.jpg'\"/>":"";
			$rs[urls]=$rs[mid]?"$webdb[www_url]/bencandy.php?fid=$rs[fid]&id=$rs[aid]":"bencandy.php?fid=$rs[fid]&id=$rs[aid]";
			echo "<div class=\"List\"><a href=\"$rs[urls]\"><h3>$rs[title]</h3>{$rs[showimg]}<p>$rs[content]</p></a></div>";
		}
	}
	exit;
}

require_once(dirname(__FILE__)."/"."template/head.htm");
require_once(dirname(__FILE__)."/"."template/list.htm");
require_once(dirname(__FILE__)."/"."template/foot.htm");


/**
*栏目配置文件检查
**/
function check_fid($fidDB){
	global $web_admin,$groupdb,$fid;
	if(!$fidDB)
	{
		showerr("栏目不存在");
	}

	//跳转到外部地址
	if( $fidDB[jumpurl] )
	{
		header("location:$fidDB[jumpurl]");
		exit;
	}

	//栏目密码
	if( $fidDB[passwd] )
	{
		if( $_POST[password] )
		{
			if( $_POST[password] != $fidDB[passwd] )
			{
				echo "<A HREF=\"?fid=$fid\">密码不正确,点击返回</A>";
				exit;
			}
			else
			{
				setcookie("sort_passwd_$fid",$fidDB[passwd]);
				$_COOKIE["sort_passwd_$fid"]=$fidDB[passwd];
			}
		}
		if( $_COOKIE["sort_passwd_$fidDB[fid]"] != $fidDB[passwd] )
		{
			echo "<CENTER><form name=\"form1\" method=\"post\" action=\"\">请输入栏目密码:<input type=\"password\" 	name=\"password\"><input type=\"submit\" name=\"Submit\" value=\"提交\"></form></CENTER>";
			exit;
		}
	}

	if( $fidDB[allowviewtitle] || $fidDB[allowviewcontent] )
	{
		if(!$web_admin&&!in_array($groupdb[gid],explode(",","$fidDB[allowviewtitle],$fidDB[allowviewcontent]")))
		{
			showerr("你所在用户组不允许浏览标题");
		}
	}
}

?>