<?php
error_reporting(0);
require_once(dirname(__FILE__)."/../data/config.php");
$type = $_GET['type'];
$iframeID = $_GET['iframeID'];
if(!eregi("^(hot|com|new|lastview|like|pic)$",$type)){
	die("类型有误");
}
unset($_GET['FileName'],$_POST['FileName'],$_COOKIE['FileName'],$_FILES['FileName'],$FileName);
$fid=intval($_GET['fid']);
$aid=intval($_GET['aid']);
$id=intval($_GET['id']);
$id || $id=$aid;
$FileName=dirname(__FILE__)."/../cache/jsarticle_cache/";
if($type=='like'){
	$FileName.=floor($id/3000)."/";
}else{
	unset($id);
}

$FileName.="{$type}_{$fid}_{$id}.php";
//默认缓存3分钟.
if(!$webdb["cache_time_$type"]){
	$webdb["cache_time_$type"]=3;
}
if( (time()-filemtime($FileName))<($webdb["cache_time_$type"]*60) ){
	@include($FileName);
	$show=str_replace(array("\n","\r","'"),array("","","\'"),stripslashes($show));
	if($iframeID){	//框架方式不会拖慢主页面打开速度,推荐
		//处理跨域问题
		if($webdb[cookieDomain]){
			echo "<SCRIPT LANGUAGE=\"JavaScript\">document.domain = \"$webdb[cookieDomain]\";</SCRIPT>";
		}
		echo "<SCRIPT LANGUAGE=\"JavaScript\">
		parent.document.getElementById('$iframeID').innerHTML='$show';
		</SCRIPT>";
	}else{			//JS式会拖慢主页面打开速度,不推荐
		echo "document.write('$show');";
	}
	exit;
}

require_once(dirname(__FILE__)."/global.php");

//默认缓存3分钟.
if(!$webdb["cache_time_$type"]){
	$webdb["cache_time_$type"]=3;
}

$show = listpage_title($fid,$type,$rows,$leng,$id,$keyword);


//真静态
if($webdb[NewsMakeHtml]==1||$gethtmlurl){

	$show=make_html($show,$pagetype='N');

//伪静态
}elseif($webdb[NewsMakeHtml]==2){

	$show=fake_html($show);
}


if($webdb[RewriteUrl]==1){	//全站伪静态
	rewrite_url($show);
}

$show=str_replace(array("\n","\r","'"),array("","","\'"),$show);

if($webdb[www_url]=='/.'){
	$show=str_replace('/./','/',$show);
}

if(!is_dir(dirname($FileName))){
	makepath(dirname($FileName));
}
if( (time()-filemtime($FileName))>($webdb["cache_time_$type"]*60) ){
	write_file($FileName,"<?php \r\n\$show=stripslashes('".addslashes($show)."'); ?>");
}

if($iframeID){	//框架方式不会拖慢主页面打开速度,推荐
	//处理跨域问题
	if($webdb[cookieDomain]){
		echo "<SCRIPT LANGUAGE=\"JavaScript\">document.domain = \"$webdb[cookieDomain]\";</SCRIPT>";
	}
	echo "<SCRIPT LANGUAGE=\"JavaScript\">
	parent.document.getElementById('$iframeID').innerHTML='$show';
	</SCRIPT>";
}else{			//JS式会拖慢主页面打开速度,不推荐
	echo "document.write('$show');";
}

?>