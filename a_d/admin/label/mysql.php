<?php
!function_exists('html') && exit('ERR');
if($action=='mod'){
	$sqlmin=intval($start_num)-1; $sqlmin<0 && $sqlmin=0;

	
	$postdb[tplpart_1code]=StripSlashes($tplpart_1);
	$postdb[tplpart_2code]=StripSlashes($tplpart_2);
	
	
	$SQL=StripSlashes($my_sql);
	if(eregi('delete',$SQL)||eregi('update',$SQL)||eregi('TRUNCATE',$SQL)||eregi('DROP',$SQL)||eregi('INSERT',$SQL)){
		showmsg('MYSQL有误!');
	}
	$db->query($SQL);
	$msg=ob_get_contents();
	if(eregi('^数据库连接出错',$msg)){
		ob_end_clean();
		showmsg("Mysql语句有误,错误报告如下:<br><font color=red>$msg</font>");
	}
	if(strstr($postdb[tplpart_1code],'="$url"')){
		showmsg("你需要把\$url变量换成其它的,否则无法访问内容页");
	}

	//使用在线编辑器后,去掉多余的网址
	$weburl=preg_replace("/(.*)\/([^\/]+)/is","\\1/",$WEBURL);
	$postdb[tplpart_1code]=str_replace($weburl,"",$postdb[tplpart_1code]);
	$postdb[tplpart_2code]=str_replace($weburl,"",$postdb[tplpart_2code]);

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

	if($rowspan<1){
		$rowspan=1;
	}
	if($colspan<1){
		$colspan=1;
	}
	$rows=$rowspan*$colspan;

	//有辅助模板时,要加多一条,过滤雷同的记录
	if($postdb[tplpart_2code]){
		$rows++;
	}	


	//指定是什么系统,方便标签函数那里做特别处理
	$postdb[SYS]='mysql';

	$postdb[RollStyleType]=$RollStyleType;

	//自己添加的
	$postdb[wrap]=$wrap;
	$postdb[mo]=$mo;
	$postdb[link_to]=$link_to;
	$postdb[textheight]=$textheight;


	$postdb[newhour]=$newhour;
	$postdb[hothits]=$hothits;
	$postdb[tplpath]=$tplpath;
	$postdb[DivTpl]=$DivTpl;
	$postdb[stype]=$stype;
	$postdb[rowspan]=$rowspan;
	$postdb[sql]=$SQL;
	$postdb[colspan]=$colspan;
	$postdb[titlenum]=$titlenum;
	$postdb[titlenum2]=$titlenum2;
	$postdb[titleflood]=$titleflood; $postdb[start_num]=$start_num;
	$postdb[width]=$width;
	$postdb[height]=$height;
	$postdb[content_num]=$content_num;
	$postdb[content_num2]=$content_num2;

	$postdb[rolltype]=$rolltype;
	$postdb[rolltime]=$rolltime;
	$postdb[roll_height]=$roll_height;

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
	$titleflood=(int)$titleflood;
	$hide=(int)$rsdb[hide];
	if($rsdb[js_time]){
		$js_ck='checked';
	}

	/*默认值*/
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
	$newhour	|| $newhour=24;
	$hothits	|| $hothits=100;

	$width				|| $width=250;
	$height				|| $height=187;
	$roll_height		|| $roll_height=50;

	$content_num		|| $content_num=80;
	$titlenum2			|| $titlenum2=40;
	$content_num2		|| $content_num2=120;

	$rolltime			|| $rolltime=3;

	$_rolltype[$rolltype]=' selected ';
	
	$div_width && $div_w=$div_width;
	$div_height && $div_h=$div_height;

	$titleflooddb["$titleflood"]="checked"; 
	$start_num>0 || $start_num=1;
	$hidedb[$hide]="checked";
	$divtpldb[$DivTpl]="checked";
	$stypedb[$stype]=" checked ";
	$fiddb=explode(",",$codedb[fiddb]);

	$getLabelTpl=getLabelTpl($inc,array("common_title","common_pic","common_content","common_fname","common_zh_title","common_zh_pic","common_zh_content"));

	//停留值
	$mck[$amodule]=' selected ';
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
	require("template/label/mysql.htm");
	require("foot.php");

}

?>