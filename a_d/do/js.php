<?php
error_reporting(0);
require(dirname(__FILE__)."/../data/config.php");
if(!eregi("^([0-9]+)$",$_GET['id'])){
	die("document.write('ID不存在');");
}
unset($_GET['FileName'],$_POST['FileName'],$_COOKIE['FileName'],$_FILES['FileName']);
$FileName=dirname(__FILE__)."/../cache/js/";

$FileName.="{$_GET[id]}.txt";
//默认缓存3分钟.
if(!$webdb["cache_time_js"]){
	$webdb["cache_time_js"]=3;
}
if( (time()-filemtime($FileName))<($webdb["cache_time_js"]*60) ){
	$show = file_get_contents($FileName);
	repalce_js_code($show);
	//$show=str_replace(array("\n","\r","'"),array("","","\'"),stripslashes($show));
	if($_GET['iframeID']){	//框架方式不会拖慢主页面打开速度,推荐
		if(!eregi("^([_a-z0-9]+)$",$_GET['id'])){
			die("document.write('iframeID有误！');");
		}
		//处理跨域问题
		if($webdb['cookieDomain']){
			echo "<SCRIPT LANGUAGE=\"JavaScript\">document.domain = \"$webdb[cookieDomain]\";</SCRIPT>";
		}
		echo "<SCRIPT LANGUAGE=\"JavaScript\">
		parent.document.getElementById('$_GET[iframeID]').innerHTML='$show';
		</SCRIPT>";
	}else{			//JS式会拖慢主页面打开速度,不推荐
		echo "document.write('$show');";
	}
	exit;
}

require(dirname(__FILE__)."/"."global.php");
require_once(ROOT_PATH."inc/label_funcation.php");


	$query=$db->query(" SELECT * FROM {$pre}label WHERE lid='$id' ");
	while( $rs=$db->fetch_array($query) ){
		//读数据库的标签
		if( $rs[typesystem] )
		{

			$_array=unserialize($rs[code]);

			if($_array[SYS]=='artcile'){
				$_array[sql]=preg_replace("/ORDER BY A.aid/is","ORDER BY A.list",$_array[sql]);
				//速度优化考虑,不使用缓存,就忽略速度
				//$webdb[label_cache_time]=='-1' || $_array[sql]=preg_replace("/AND R.topic=1/is","",$_array[sql]);				
			}

			$value=($rs[type]=='special')?Get_sp($_array):Get_Title($_array);
			if(strstr($value,"(/mv)")){
				$value=get_label_mv($value);
			}
			if($_array[c_rolltype])
			{
				$value="<marquee direction='$_array[c_rolltype]' scrolldelay='1' scrollamount='1' onmouseout='if(document.all!=null){this.start()}' onmouseover='if(document.all!=null){this.stop()}' height='$_array[roll_height]'>$value</marquee>";
			}
		}
		//代码标签
		elseif( $rs[type]=='code' )
		{
			$value=stripslashes($rs[code]);
			//纠正一下不完整的javascript代码,不必做权限判断,普通用户也能删除
			if(eregi("<SCRIPT",$value)&&!eregi("<\/SCRIPT",$value)){
				if($delerror){
					$db->query("UPDATE `{$pre}label` SET code='' WHERE lid='$rs[lid]'");
				}else{
					die("<A HREF='$WEBURL?&delerror=1'>此“{$rs[tag]}”标签有误,点击删除之!</A><br>$value");
				}			
			}
			//真实地址还原
			$value=En_TruePath($value,0);
		}
		//单张图片
		elseif( $rs[type]=='pic' )
		{	
			unset($width,$height);
			$picdb=unserialize($rs[code]);
			
			$picdb[width] && $width=" width='$picdb[width]'";
			$picdb[height] && $height=" height='$picdb[height]'";
			$picdb[imgurl]=En_TruePath($picdb[imgurl],0);
			$picdb[imglink]=En_TruePath($picdb[imglink],0);
			$picdb[imgurl]=tempdir($picdb[imgurl]);
			if($picdb['imglink'])
			{
				$value="<a href='$picdb[imglink]' target=_blank><img src='$picdb[imgurl]' $width $height border='0' /></a>";
			}
			else
			{
				$value="<img src='$picdb[imgurl]' $width $height  border='0' />";
			}
		}
		//单个FLASH
		elseif( $rs[type]=='swf' )
		{
			$flashdb=unserialize($rs[code]);
			$flashdb[flashurl]=En_TruePath($flashdb[flashurl],0);
			$flashdb[flashurl]=tempdir($flashdb[flashurl]);
			$flashdb[width] && $width=" width='$flashdb[width]'";
			$flashdb[height] && $height=" height='$flashdb[height]'";
			$value="<object type='application/x-shockwave-flash' data='$flashdb[flashurl]' $width $height wmode='transparent'><param name='movie' value='$flashdb[flashurl]' /><param name='wmode' value='transparent' /></object>";
		}
		//普通幻灯片
		elseif( $rs[type]=='rollpic' )
		{	
			$detail=unserialize($rs[code]);
			foreach($detail[picurl] AS $key=>$value){
				$detail[picurl][$key]=En_TruePath($value,0);
			}
			foreach($detail[piclink] AS $key=>$value){
				$detail[piclink][$key]=En_TruePath($value,0);
			}
			if($detail[rolltype]==2){	//自定义样式
				unset($_listdb);
				foreach($detail[picurl] AS $key=>$value){
					$_listdb[]=array(
						'picurl'=>tempdir($value),
						'link'=>$detail[piclink][$key],
						'url'=>$detail[piclink][$key],
						'title'=>$detail[picalt][$key],
					  );
				}
				$_listdb[0][tpl_1code]=En_TruePath($detail[tplpart_1code],0);
				$value=run_label_php($_listdb);
			}elseif($detail[picalt][1]==''){	//没有标题的情况
				$value=rollPic_no_title_js($detail);
			}else{
				if($detail[rolltype]==1){
					$value=rollPic_flash($detail);
				}else{
					$value=rollpic_2js($detail);
				}
			}
			
		}
		//其它形式的
		else
		{
			$value=stripslashes($rs[code]);
			//真实地址还原
			$value=En_TruePath($value,0);
		}
	}

$show=stripslashes($value);

if(!is_dir(dirname($FileName))){
	makepath(dirname($FileName));
}
if( (time()-filemtime($FileName))>($webdb["cache_time_js"]*60) ){
	if($webdb["cache_time_js"]!=-1){
		write_file($FileName,$show);
	}
}

repalce_js_code($show);

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

function repalce_js_code(&$show){
	//对javascript进行特别处理
	if(eregi("<SCRIPT",$show)){
		preg_match_all("/<SCRIPT([^>]*)>(.*?)<\/SCRIPT>/is",$show,$array);
		foreach($array[1] AS $key=>$value){
			//一般联盟广告会出现这种情况
			if(eregi("src=",$value)){
				$value=str_replace("'","\'",$value);
				echo "document.write('<SCRIPT$value><\/SCRIPT>');";
			}else{
				echo $array[2][$key];
			}
		}
		$show=preg_replace("/(.*?)<SCRIPT([^>]*)>(.*?)<\/SCRIPT>(.*?)/is","\\1\\4",$show);
	}
	
	$show=str_replace(array("\r","\n","'"),array("","","\'"),$show);
}

?>