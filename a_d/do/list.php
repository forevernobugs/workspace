<?php
require_once(dirname(__FILE__)."/"."global.php");

// ini_set('display_errors',1);            //������Ϣ
// ini_set('display_startup_errors',1);    //php����������Ϣ
// error_reporting(-1);      

$page<1 && $page=1;

if(!$fid&&$webdb[NewsMakeHtml]==2){
	//α��̬����
	Explain_HtmlUrl();
}

$Cache_FileName=ROOT_PATH."cache/list_cache/{$fid}_{$page}.php";
if(!$jobs&&$webdb[list_cache_time]&&(time()-filemtime($Cache_FileName))<($webdb[list_cache_time]*60)){
	echo read_file($Cache_FileName);
	exit;
}

get_guide($fid);	//��Ŀ����


if(!$fid){
	showerr("��ĿFID������");
}

//��Ŀ�����ļ�
$fidDB=$db->get_one("SELECT S.*,M.alias AS M_alias,M.config AS M_config,M.iftable FROM {$pre}sort S LEFT JOIN {$pre}article_module M ON S.fmid=M.id WHERE S.fid='$fid'");



if(!$fidDB){
	showerr("��ĿID����");
}
$fidDB[M_alias] || $fidDB[M_alias]='����';
$fidDB[M_config]=unserialize($fidDB[M_config]);
$fidDB[config]=unserialize($fidDB[config]);
$fidDB[descrip]=En_TruePath($fidDB[descrip],0);
if($fidDB[type]==2){
	$rsdb[content]=$fidDB[descrip];
}

//ɸѡtest

$selectlink="?fid={$fid}";

$pidanselect=$fidDB['M_config']['field_db'];
foreach($pidanselect as $k=>$val){
	if(strstr($val["field_name"],"select_")){
		$select_content[$k]["title"]=$val["title"];
		$selects=explode("\r\n",$val["form_set"]);
		$select_content[$k]["sets"]= $selects;
	 
	 	$select_get=addslashes($_GET[$k]);
		if($select_get!==false)
		{ 
			 
			if($selects[$select_get])
			{
				
				$select_content[$k]["selected"]= $selects[$select_get];
				$selectlink.="&{$k}={$select_get}";
				$select_where[$k]=$selects[$select_get];
			}
			else
			{
				$select_content[$k]["selected"]="all";
				$selectlink.="&{$k}=all";
			}
		}
		else
		{
				$select_content[$k]["selected"]="all";
				$selectlink.="&{$k}=all";
		}
	}
}



///����where���//
	if(is_array($select_where)){
		foreach($select_where as $k=>$v)
			$select_tmp_sql.=" and {$k}='".$v."'";

		$select_lists=$db->query("select aid from  {$pre}article_content_{$fidDB[fmid]} where fid={$fid} $select_tmp_sql");
 			while($rs = $db->fetch_array($select_lists)){
				$select_tmp_sql2[]=$rs[aid];
			}
		if($select_tmp_sql2)
		{ 
			$select_aids_sql=implode(",",$select_tmp_sql2);
			$select_wheresql=" and  A.aid in ($select_aids_sql) ";
			$select_wheresql2=" and  aid in ($select_aids_sql) ";
		}
		else
		{
			$select_wheresql=" and  0";
			$select_wheresql2=" and  0";
		}
 
	}

 
$fupId=intval($fidDB[type]?$fid:$fidDB[fup]);

//��ֹ���ʶ�̬ҳ
if($webdb[ForbidShowPhpPage]&&!$NeedCheck&&!$jobs){
	if($webdb[NewsMakeHtml]==2&&ereg("=[0-9]+$",$WEBURL)){		//α��̬
		eval("\$url=\"$webdb[list_filename2]\";");
		header("location:$webdb[www_url]/$url");
		exit;
	}elseif($webdb[NewsMakeHtml]==1){							//�澲̬	
		$detail=get_html_url();
		if(is_file(ROOT_PATH.$detail[_listurl])){
			header("location:$detail[listurl]");
			exit;
		}
	}
}

/**
*��Ŀ�����ļ����
**/
check_fid($fidDB);

//SEO
$titleDB[title]			= filtrate("$fidDB[name] - $webdb[webname]");
$titleDB[keywords]		= filtrate("$fidDB[metakeywords]  $webdb[metakeywords]");
$titleDB[description]	= filtrate("$fidDB[descrip]");

//����Ŀ���Ϊ��׼
$fidDB[style] && $STYLE=$fidDB[style];

/*ģ��*/
$FidTpl=unserialize($fidDB[template]);
$head_tpl=$FidTpl['head'];
$foot_tpl=$FidTpl['foot'];

/**
*Ϊ��ȡ��ǩ����
**/
$chdb[main_tpl]=html("list",$FidTpl['list']);

/**
*��ǩ
**/
$ch_fid	= intval($fidDB[config][label_list]);		//�Ƿ�������Ŀר�ñ�ǩ
$ch_pagetype = 2;									//2,Ϊlistҳ,3,Ϊbencandyҳ
$ch_module = 0;										//����ģ��,Ĭ��Ϊ0
$ch = 0;											//�������κ�ר��
require(ROOT_PATH."inc/label_module.php");

//��ʾ�ӷ���
$listdb_moresort=ListMoreSort();

//�б�ҳ����ƪ����,��Ŀ���õĻ�.����ĿΪ��׼,������ϵͳΪ��׼,ϵͳ�����ھ�Ĭ��20
$rows=$fidDB[maxperpage]?$fidDB[maxperpage]:($webdb[list_row]?$webdb[list_row]:20);	

$listdb=ListThisSort($rows,$webdb[ListLeng]?$webdb[ListLeng]:50,$select_wheresql);		//����Ŀ�����б�
$page_sql=$webdb[viewNoPassArticle]?'':' AND yz=1 ';
$erp=$fidDB[iftable]?$fidDB[iftable]:"";
$showpage=getpage("{$pre}article$erp","WHERE fid=$fid $page_sql $select_wheresql2","list.php?fid=$fid",$rows,null,strstr($selectlink,"&"));	//�����б��ҳ

 


//�����Ŀ����ģ��
if(is_file(html("$webdb[SideSortStyle]"))){
	$sortnameTPL=html("$webdb[SideSortStyle]");
}else{
	$sortnameTPL=html("side_sort/0");
}

//��Ŀ����ģ��
$aboutsortTPL=html("aboutsort_tpl/0");


//�������ʾ��ʽ
$fidDB[config][ListShowBigType] || $fidDB[config][ListShowBigType]=0;
unset($bigsortTPL);
if($fidDB[fmid]&&!$fidDB[config][ListShowBigType]){
	$bigsortTPL=html("bigsort_tpl/mod_$fidDB[fmid]");
}
if(!$bigsortTPL){
	$bigsortTPL=html("bigsort_tpl/0",ROOT_PATH."template/default/{$fidDB[config][ListShowBigType]}.htm");
}


//�����б���ʾ��ʽ.
$fidDB[config][ListShowType] || $fidDB[config][ListShowType]=0;
unset($listTPL);
if($fidDB[fmid]&&!$fidDB[config][ListShowType]){
	$listTPL=html("list_tpl/mod_$fidDB[fmid]");
}

if(!$listTPL){
	$listTPL=html("list_tpl/0",ROOT_PATH."template/default/{$fidDB[config][ListShowType]}.htm");
}

//��ģ����չ�ӿ�
@include(ROOT_PATH."inc/list_{$fidDB[fmid]}.php");



$product_detail_res = $db->get_one("SELECT `fup` FROM `qb_sort` WHERE fid=" . $fid);


if ($product_detail_res['fup'] == '43'||$product_detail_res['fup'] == '44'||$product_detail_res['fup'] == '45'||$product_detail_res['fup'] == '42') {
    require(ROOT_PATH . "inc/product_detail_head.php");
} else {
    require(ROOT_PATH . "inc/head.php");
}


require(html("list",$FidTpl['list']));
//echo(html("list",$FidTpl['list']));
if ($product_detail_res['fup'] != '43'&&$product_detail_res['fup'] != '44'&&$product_detail_res['fup'] !='45'&&$product_detail_res['fup'] !='42') {
    require(ROOT_PATH . "inc/foot.php");
}


//α��̬����
if($webdb[NewsMakeHtml]==2)
{
	$content=ob_get_contents();
	ob_end_clean();
	ob_start();
	$content=fake_html($content);
	echo "$content";
}

if(!$jobs&&$webdb[list_cache_time]&&(time()-filemtime($Cache_FileName))>($webdb[list_cache_time]*60)){
	
	if(!is_dir(dirname($Cache_FileName))){
		makepath(dirname($Cache_FileName));
	}
	write_file($Cache_FileName,$content);
}elseif($jobs=='show'){
	@unlink($Cache_FileName);
}


/**
*��Ŀ�����ļ����
**/
function check_fid($fidDB){
	global $web_admin,$groupdb,$fid;
	if(!$fidDB)
	{
		showerr("��Ŀ������");
	}

	//��ת���ⲿ��ַ
	if( $fidDB[jumpurl] )
	{
		header("location:$fidDB[jumpurl]");
		exit;
	}

	//��Ŀ����
	if( $fidDB[passwd] )
	{
		if( $_POST[password] )
		{
			if( $_POST[password] != $fidDB[passwd] )
			{
				echo "<A HREF=\"?fid=$fid\">���벻��ȷ,�������</A>";
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
			echo "<CENTER><form name=\"form1\" method=\"post\" action=\"\">��������Ŀ����:<input type=\"password\" 	name=\"password\"><input type=\"submit\" name=\"Submit\" value=\"�ύ\"></form></CENTER>";
			exit;
		}
	}

	if( $fidDB[allowviewtitle] || $fidDB[allowviewcontent] )
	{
		if(!$web_admin&&!in_array($groupdb[gid],explode(",","$fidDB[allowviewtitle],$fidDB[allowviewcontent]")))
		{
			showerr("�������û��鲻�����������");
		}
	}
}

?>