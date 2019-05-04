<?php
!function_exists('html') && exit('ERR');
if($action=='mod'){

	unset($SQL);
	//$postdb[js]=comimg();
	$postdb[rolltype]=$rolltype;

	$postdb[width]=$width;
	$postdb[height]=$height;
	$postdb[picurl]=$picurl;
	$postdb[times]=$times;
	$postdb[piclink]=$piclink;
	$postdb[pictxt]=$pictxt;
	$postdb[textheight]=$textheight;
	$postdb[picalt]=$picalt;
	$postdb[wrap]=$wrap;
		$postdb[mo]=$mo;
	$postdb[link_to]=$link_to;
	foreach($postdb[picurl] AS $key=>$value){
		$postdb[picurl][$key]=En_TruePath($value);
	}
	foreach($postdb[piclink] AS $key=>$value){
		$postdb[piclink][$key]=En_TruePath($value);
	}
	$code=addslashes(serialize($postdb));
	$div_db[div_w]=$div_w;
	$div_db[div_h]=$div_h;
	$div_db[div_bgcolor]=$div_bgcolor;
	$div=addslashes(serialize($div_db));
	$typesystem=0;

	//插入或更新标签库
	do_post();

}else{

	$rsdb=get_label();
	$div=unserialize($rsdb[divcode]);
	@extract($div);
	$code=unserialize($rsdb[code]);
	@extract($code);
	if(!is_array($picurl)){
		$picurl=array(1=>"",2=>"");
	}
	$div_width && $div_w=$div_width;
	$div_height && $div_h=$div_height;

	if($rsdb[js_time]){
		$js_time='checked';
	}
	$hide=(int)$rsdb[hide];
	$hidedb["$hide"]="checked";

	foreach($picurl AS $key=>$value){
		$picurl[$key]=En_TruePath($value,0);
	}
	foreach($piclink AS $key=>$value){
		$piclink[$key]=En_TruePath($value,0);
	}
	
	
	//停留值
	$times_s[intval($times)]='selected ';
	$wrap_s[intval($wrap)]='selected ';
	$mo_s[intval($mo)]='selected ';
	$link_to_s[intval($link_to)]='selected ';
	
	
	
	//幻灯片样式
	$rollpicStyle="幻灯片种类: <select name='rolltype' id='index_rolltype' onChange='index_rollpictypes(this)'>";
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
	       <select  name=\"times\">
      <option value=\"2\" $times_s[2]>2秒</option>
       <option value=\"4\" $times_s[4]>4秒</option>
       <option value=\"5\" $times_s[5]>5秒</option>
       <option value=\"6\" $times_s[6]>6秒</option>
       <option value=\"8\" $times_s[8]>8秒</option>
       <option value=\"10\" $times_s[10]>10秒</option>
     </select>
	  
	    
	  文字层高度：<input type=\"text\" name=\"textheight\" size=\"3\" value=\"$textheight\" /><span class=\"black\">(0即隐藏文字层,留空或defalut即默认)</span><br>
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
	require("template/label/rollpic.htm");
	require("foot.php");

}
?>