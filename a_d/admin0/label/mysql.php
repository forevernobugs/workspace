<?php
!function_exists('html') && exit('ERR');
if($action=='mod'){
	$sqlmin=intval($start_num)-1; $sqlmin<0 && $sqlmin=0;

	
	$postdb[tplpart_1code]=StripSlashes($tplpart_1);
	$postdb[tplpart_2code]=StripSlashes($tplpart_2);
	
	
	$SQL=StripSlashes($my_sql);
	if(eregi('delete',$SQL)||eregi('update',$SQL)||eregi('TRUNCATE',$SQL)||eregi('DROP',$SQL)||eregi('INSERT',$SQL)){
		showmsg('MYSQL����!');
	}
	$db->query($SQL);
	$msg=ob_get_contents();
	if(eregi('^���ݿ����ӳ���',$msg)){
		ob_end_clean();
		showmsg("Mysql�������,���󱨸�����:<br><font color=red>$msg</font>");
	}
	if(strstr($postdb[tplpart_1code],'="$url"')){
		showmsg("����Ҫ��\$url��������������,�����޷���������ҳ");
	}

	//ʹ�����߱༭����,ȥ���������ַ
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

	//ѡ����ʾ��������,����ѡ��Table,����һ������ʾЧ��,ѡ��tableָ����һ��TABLE,ѡ��divָ���׶���Ĵ���
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

	//�и���ģ��ʱ,Ҫ�Ӷ�һ��,������ͬ�ļ�¼
	if($postdb[tplpart_2code]){
		$rows++;
	}	


	//ָ����ʲôϵͳ,�����ǩ�����������ر���
	$postdb[SYS]='mysql';

	$postdb[RollStyleType]=$RollStyleType;

	//�Լ���ӵ�
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

	//�������±�ǩ��
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

	/*Ĭ��ֵ*/
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

	//ͣ��ֵ
	$mck[$amodule]=' selected ';
	$wrap_s[intval($wrap)]='selected';
	$mo_s[intval($mo)]='selected ';
	$link_to_s[intval($link_to)]='selected ';
	$rolltime_s[intval($rolltime)]='selected ';

	
	//�õ�Ƭ��ʽ
	$rollpicStyle="�õ�Ƭ����: <select name='RollStyleType' id='RollStyleType' onChange='rollpictypes(this)'>";
	$dir=opendir(ROOT_PATH."images/default/rollpic/");
	while($file=readdir($dir)){
		if(eregi("\.js$",$file)){
			$rollpicStyle.="<option value=".str_replace(".js","",$file).">".str_replace(".js","",$file)."</option>";
		}
	}
	$rollpicStyle.="</select>
	 <br>
      ͼƬ�� <input type=\"text\" name=\"width\" value=\"$width\" size=\"4\" id=\"width\" />
      ͼƬ�� <input type=\"text\" name=\"height\" value=\"$height\" size=\"4\" id=\"height\" />
      �л�ʱ����  
	  �л�ʱ���� 
	       <select  name=\"rolltime\">
      <option value=\"2\" $rolltime_s[2]>2��</option>
       <option value=\"4\" $rolltime_s[4]>4��</option>
       <option value=\"5\" $rolltime_s[5]>5��</option>
       <option value=\"6\" $rolltime_s[6]>6��</option>
       <option value=\"8\" $rolltime_s[8]>8��</option>
       <option value=\"10\" $rolltime_s[10]>10��</option>
     </select>
	  ���ֲ�߶ȣ�<input type=\"text\" name=\"textheight\" size=\"3\" value=\"$textheight\" /><span class=\"black\">(0����,����ΪĬ�ϣ���Щ���ֻ��ΪĬ��)</span><br>
       �Ƿ���ʾ�߿�<span class=\"black\">(����еĻ�)</span>
	   <select name=\"wrap\">
          <option value=\"1\" $wrap_s[1]>��</option>
          <option value=\"2\" $wrap_s[2]>��</option>
      	</select>
       ������ʽ��
	   <select name=\"link_to\">
      <option value=\"0\" $link_to_s[0]>�´���</option>
       <option value=\"1\" $link_to_s[1]>������</option>
     </select>
     
       �ֶ��л���ʽ��
      <select name=\"mo\">
      <option value=\"0\" $mo_s[0]>�����</option>
       <option value=\"1\" $mo_s[1]>��껮��</option>
     </select>";

	require("head.php");
	require("template/label/mysql.htm");
	require("foot.php");

}

?>