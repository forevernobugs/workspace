<?php
!function_exists('html') && exit('ERR');

if($action=='mod'){
	$sqlmin=intval($start_num)-1; $sqlmin<0 && $sqlmin=0;

	if($tplpart_2==''){
	//	$stype='t';
	}

	if(strstr($postdb[tplpart_1code],'$picurl')&&strstr($postdb[tplpart_1code],'$content')){
		$stype="cp";
	}elseif(strstr($postdb[tplpart_1code],'$content')){
		$stype="c";
	}elseif(strstr($postdb[tplpart_1code],'$picurl')){
		$stype="p";
	}

	//选择显示两列以上,这里选择Table,否则不一定能显示效果,选择table指外套一个TABLE,选择div指不套多余的代码
	if($colspan>1){
		$DivTpl=0;
	}else{
		$DivTpl=1;
	}

	$_url='$webdb[blog_url]/index.php?uid=$uid';
	if($tplpart_1)
	{
		$postdb[tplpart_1]=StripSlashes($tplpart_1);
		$postdb[tplpart_1code]=$postdb[tplpart_1];
		//$postdb[tplpart_1code]=read_file(ROOT_PATH.$tplpart_1);
		$postdb[tplpart_1code]=str_replace('{$url}',$_url,$postdb[tplpart_1code]);
		$postdb[tplpart_1code]=str_replace('$url',$_url,$postdb[tplpart_1code]);
		if(!$postdb[tplpart_1code]){
			//showmsg("模板一路径不对或者是其他原因,模板数据读取失败,请检查之");
		}
		//$rs1=$db->get_one("SELECT type FROM {$pre}template WHERE filepath='$tplpart_1' ");
	}
	if($tplpart_2)
	{
		$postdb[tplpart_2]=StripSlashes($tplpart_2);
		$postdb[tplpart_2code]=$postdb[tplpart_2];
		//$postdb[tplpart_2code]=read_file(ROOT_PATH.$tplpart_2);
		$postdb[tplpart_2code]=str_replace('{$url}',$_url,$postdb[tplpart_2code]);
		$postdb[tplpart_2code]=str_replace('$url',$_url,$postdb[tplpart_2code]);
		if(!$postdb[tplpart_2code]){
			//showmsg("模板二路径不对或者是其他原因,模板数据读取失败,请检查之");
		}
		//$rs2=$db->get_one("SELECT type FROM {$pre}template WHERE filepath='$tplpart_2' ");
	}

	//使用在线编辑器后,去掉多余的网址
	$weburl=preg_replace("/(.*)\/([^\/]+)/is","\\1/",$WEBURL);
	$postdb[tplpart_1code]=str_replace($weburl,"",$postdb[tplpart_1code]);
	$postdb[tplpart_2code]=str_replace($weburl,"",$postdb[tplpart_2code]);

	/*判断是否是显示图片类型*/
	if(strstr($postdb[tplpart_1code],'$picurl')||strstr($postdb[tplpart_2code],'$picurl'))
	{
		//$SQL=" WHERE D.icon!='' ";
		$SQL=" WHERE 1 ";
	}
	else
	{
		$SQL=" WHERE 1 ";
	}
	if($rowspan<1){
		$rowspan=1;
	}
	if($colspan<1){
		$colspan=1;
	}
	$rows=$rowspan*$colspan;
	if(is_numeric($yz)){
		$SQL.=" AND D.yz=$yz ";
	}
	
	if($group_1){
		$SQL.=" AND D.groupid=$group_1 ";
	}
	if($group_2){
		$SQL.=" AND D.groups LIKE '%,$group_2,%' ";
	}

	$SQL=" SELECT D.*,D.username AS title,D.icon AS picurl,D.introduce AS content FROM {$pre}memberdata D $SQL ORDER BY D.$order $asc LIMIT $sqlmin,$rows ";
	
	$postdb[group_1]=$group_1;
	$postdb[group_2]=$group_2;

	$postdb[RollStyleType]=$RollStyleType;
	
		//自己添加的
	$postdb[wrap]=$wrap;
	$postdb[mo]=$mo;
	$postdb[link_to]=$link_to;
	$postdb[textheight]=$textheight;
	
	$postdb[tplpath]=$tplpath;
	$postdb[DivTpl]=$DivTpl;
	$postdb[fiddb]=$fids;
	$postdb[stype]=$stype;
	$postdb[yz]=$yz;
	$postdb[timeformat]=$timeformat;
	$postdb[order]=$order;
	$postdb[asc]=$asc;
	$postdb[levels]=$levels;
	$postdb[rowspan]=$rowspan;
	$postdb[sql]=$SQL;
	$postdb[colspan]=$colspan;
	$postdb[titlenum]=$titlenum;
	$postdb[titleflood]=$titleflood; $postdb[start_num]=$start_num;
	
	$code=addslashes(serialize($postdb));
	$div_db[div_w]=$div_w;
	$div_db[div_h]=$div_h;
	$div_db[div_bgcolor]=$div_bgcolor;
	$div=addslashes(serialize($div_db));
	$typesystem=1;
	
	//插入或更新标签库
	do_post();

}else{

	$rsdb=get_label();
	$div=unserialize($rsdb[divcode]);
	@extract($div);
	$codedb=unserialize($rsdb[code]);
	@extract($codedb);
	if(!isset($yz)){
		$yz="all";
	}
	if(!isset($is_com)){
		$is_com="all";
	}
	if(!isset($order)){
		$order="posttime";
	}
	$titleflood=(int)$titleflood;
	$hide=(int)$rsdb[hide];
	if($rsdb[js_time]){
		$js_ck='checked';
	}

	/*默认值*/
	$yz || $yz='all';
	$asc || $asc='DESC';
	$titleflood!=1		&& $titleflood=0;
	$timeformat			|| $timeformat="Y-m-d H:i:s";
	$rowspan			|| $rowspan=5;
	$colspan			|| $colspan=1;
	$titlenum			|| $titlenum=20;
	$div_w				|| $div_w=50;
	$div_h				|| $div_h=30;
	$hide!=1			&& $hide=0;
	$DivTpl!=1			&& $DivTpl=0;
	$stype				|| $stype=4;

	$div_width && $div_w=$div_width;
	$div_height && $div_h=$div_height;

	$yzdb[$yz]="checked";
	$ascdb[$asc]="checked";
	$orderdb[$order]=" selected ";
	$levelsdb[$levels]=" selected ";
	$titleflooddb["$titleflood"]="checked"; 
	$start_num>0 || $start_num=1;
	$hidedb[$hide]="checked";
	$divtpldb[$DivTpl]="checked";
	$stypedb[$stype]=" checked ";
	$fiddb=explode(",",$codedb[fiddb]);
 	//$select_news=select_group("fiddb[]",$rsdb[groupid]);
	$select_group=select_group("group_1",$group_1);
	$select_group2=select_group("group_2",$group_2);
	
	$tplpart_1=str_replace("&nbsp;","&amp;nbsp;",$tplpart_1);
	$tplpart_2=str_replace("&nbsp;","&amp;nbsp;",$tplpart_2);

	$getLabelTpl=getLabelTpl($inc,array("common_title","common_pic"));

$wrap_s[intval($wrap)]='selected';
	$mo_s[intval($mo)]='selected ';
	$link_to_s[intval($link_to)]='selected ';
	$rolltime_s[intval($rolltime)]='selected ';

	
	//幻灯片样式
	$rollpicStyle="幻灯片种类: <select name='RollStyleType' id='RollStyleType' onChange='rollpictypes(this)'>";
	$dir=opendir(ROOT_PATH."images/default/rollpic/");
	while($file=readdir($dir)){
		if(eregi("\.js$",$file)){
			$rollpicStyle.="<option value=".str_replace(".js","",$file).">".str_replace(".js","",$file)."</option>";
		}
	}
	$rollpicStyle.="</select>
	 <br>
      图片宽 <input type=\"text\" name=\"width\" value=\"$width\" size=\"4\" id=\"width\" />
      图片高 <input type=\"text\" name=\"height\" value=\"$height\" size=\"4\" id=\"height\" />
      切换时间间隔  
	  切换时间间隔 
	       <select  name=\"rolltime\">
      <option value=\"2\" $rolltime_s[2]>2秒</option>
       <option value=\"4\" $rolltime_s[4]>4秒</option>
       <option value=\"5\" $rolltime_s[5]>5秒</option>
       <option value=\"6\" $rolltime_s[6]>6秒</option>
       <option value=\"8\" $rolltime_s[8]>8秒</option>
       <option value=\"10\" $rolltime_s[10]>10秒</option>
     </select>
	  文字层高度：<input type=\"text\" name=\"textheight\" size=\"3\" value=\"$textheight\" /><span class=\"black\">(0隐藏,留空为默认，有些风格只能为默认)</span><br>
       是否显示边框<span class=\"black\">(如果有的话)</span>
	   <select name=\"wrap\">
          <option value=\"1\" $wrap_s[1]>是</option>
          <option value=\"2\" $wrap_s[2]>否</option>
      	</select>
       链接形式：
	   <select name=\"link_to\">
      <option value=\"0\" $link_to_s[0]>新窗口</option>
       <option value=\"1\" $link_to_s[1]>本窗口</option>
     </select>
     
       手动切换方式：
      <select name=\"mo\">
      <option value=\"0\" $mo_s[0]>鼠标点击</option>
       <option value=\"1\" $mo_s[1]>鼠标划过</option>
     </select>";

	require("head.php");
	require("template/label/member.htm");
	require("foot.php");

}
?>