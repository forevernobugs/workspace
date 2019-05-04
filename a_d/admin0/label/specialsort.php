<?php
!function_exists('html') && exit('ERR');

if($action=='mod'){
	$sqlmin=intval($start_num)-1; $sqlmin<0 && $sqlmin=0;

	//选择显示两列以上,这里选择Table,否则不一定能显示效果,选择table指外套一个TABLE,选择div指不套多余的代码
	if($colspan>1){
		$DivTpl=0;
	}else{
		$DivTpl=1;
	}

	if(strstr($postdb[tplpart_1code],'$picurl')&&strstr($postdb[tplpart_1code],'$content')){
		$stype="cp";
	}elseif(strstr($postdb[tplpart_1code],'$content')){
		$stype="c";
	}elseif(strstr($postdb[tplpart_1code],'$picurl')){
		$stype="p";
	}
	
	$_url='$webdb[www_url]/do/showsp.php?fid=$fid&id=$id';
	$_listurl='$webdb[www_url]/do/listsp.php?fid=$fid';

	$postdb[tplpart_1]=StripSlashes($tplpart_1);
	$postdb[tplpart_1code]=$postdb[tplpart_1];

	$postdb[tplpart_1code]=str_replace('{$url}',$_url,$postdb[tplpart_1code]);
	$postdb[tplpart_1code]=str_replace('$url',$_url,$postdb[tplpart_1code]);
	$postdb[tplpart_1code]=str_replace('{$list_url}',$_listurl,$postdb[tplpart_1code]);
	$postdb[tplpart_1code]=str_replace('$list_url',$_listurl,$postdb[tplpart_1code]);


	//使用在线编辑器后,去掉多余的网址
	$weburl=preg_replace("/(.*)\/([^\/]+)/is","\\1/",$WEBURL);
	$postdb[tplpart_1code]=str_replace($weburl,"",$postdb[tplpart_1code]);
	$postdb[tplpart_2code]=str_replace($weburl,"",$postdb[tplpart_2code]);

	$SQL=" WHERE `ifbase`=0 AND yz=1 ";

	if($levels==1){
		$SQL.=" AND A.levels=1 ";
	}

	if($fid){
		$SQL.=" AND A.fid='$fid' ";
	}

	if($rowspan<1){
		$rowspan=1;
	}
	if($colspan<1){
		$colspan=1;
	}
	$rows=$rowspan*$colspan;

	/*判断是否是显示图片类型*/
	$SQLpic=$SQL2="";
	if($stype=="r"||strstr($postdb[tplpart_1code],'$picurl'))
	{
		$SQL.=" AND A.picurl!='' ";
	}	
	
	$_order=" ORDER BY A.$order $asc";
	
	$SQL=" SELECT A.* FROM {$pre}special A $SQL $_order LIMIT $sqlmin,$rows ";

	$postdb[SYS]='specialsort';
	$postdb[RollStyleType]=$RollStyleType;
	$postdb[roll_height]=$roll_height;
	$postdb[url]=$_url;
	$postdb[width]=$width;
	$postdb[height]=$height;

	//自己添加的
	$postdb[wrap]=$wrap;
	$postdb[mo]=$mo;
	$postdb[link_to]=$link_to;
	$postdb[textheight]=$textheight;
	
	
	
	$postdb[rolltype]=$rolltype;
	$postdb[rolltime]=$rolltime;
	$postdb[roll_height]=$roll_height;

	$postdb[fid]=$fid;
	
	$postdb[newhour]=$newhour;
	$postdb[hothits]=$hothits;
	$postdb[amodule]=$amodule;
	$postdb[tplpath]=$tplpath;
	$postdb[DivTpl]=$DivTpl;
	$postdb[fiddb]=$fids;
	$postdb[stype]=$stype;
	$postdb[yz]=$yz;
	$postdb[hidefid]=$hidefid;
	$postdb[timeformat]=$timeformat;
	$postdb[order]=$order;
	$postdb[asc]=$asc;
	$postdb[levels]=$levels;
	$postdb[rowspan]=$rowspan;
	$postdb[sql]=$SQL;
	$postdb[sql2]=$SQL2;
	$postdb[colspan]=$colspan;
	$postdb[content_num]=$content_num;
	$postdb[content_num2]=$content_num2;
	$postdb[titlenum]=$titlenum;
	$postdb[titlenum2]=$titlenum2;
	$postdb[titleflood]=$titleflood; $postdb[start_num]=$start_num;

	$postdb[c_rolltype]=$c_rolltype;
	
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
	
	if(!isset($levels)){
		$levels="all";
	}
	if(!isset($order)){
		$order="list";
	}
	$titleflood=(int)$titleflood;
	$hide=(int)$rsdb[hide];
	if($rsdb[js_time]){
		$js_ck='checked';
	}

	/*默认值*/
	$yz=='all' || $yz=1;
	$asc || $asc='DESC';
	$titleflood!=1		&& $titleflood=0;
	$timeformat			|| $timeformat="Y-m-d H:i:s";
	$rowspan			|| $rowspan=5;
	$colspan			|| $colspan=1;
	$titlenum			|| $titlenum=20;
	$content_num		|| $content_num=80;
	$div_w				|| $div_w=50;
	$div_h				|| $div_h=30;
	$hide!=1			&& $hide=0;
	$DivTpl!=1			&& $DivTpl=0;
	$stype				|| $stype=4;

	$width				|| $width=250;
	$height				|| $height=187;
	$roll_height		|| $roll_height=50;
	
	$div_width && $div_w=$div_width;
	$div_height && $div_h=$div_height;
	$yzdb[$yz]="checked";
	$ascdb[$asc]="checked";
	$orderdb[$order]=" selected ";
	$levelsdb[$levels]=" checked ";
	$titleflooddb["$titleflood"]="checked"; 
	$start_num>0 || $start_num=1;
	$hidedb[$hide]="checked";
	$divtpldb[$DivTpl]="checked";

	$_hidefid[intval($hidefid)]=" checked ";
	$fiddb=explode(",",$codedb[fiddb]);
	
	$c_rolltype || $c_rolltype=0;
	$newhour	|| $newhour=24;
	$hothits	|| $hothits=30;

	$rolltime			|| $rolltime=3;

	$_rolltype[$rolltype]=' selected ';

	$c_rolltypedb[$c_rolltype]=" checked ";

	
	$tplpart_1=str_replace("&nbsp;","&amp;nbsp;",$tplpart_1);
	$tplpart_2=str_replace("&nbsp;","&amp;nbsp;",$tplpart_2);

	$sort_fid=$Guidedb->Select("{$pre}spsort","fid",$fid);
 
	$getLabelTpl=getLabelTpl($inc,array("common_title","common_pic","common_content"));


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
	require("template/label/specialsort.htm");
	require("foot.php");
}
?>