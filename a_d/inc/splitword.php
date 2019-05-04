<?php
defined('ROOT_PATH') or die();

function Load_Split_Word_array(){
	global $xingDB,$xing2DB,$CiYuDB;
	$xing="黄李陈郭赵苏习钱孙曹车侯周吴郑王冯褚卫蒋沈韩杨朱秦尤许何吕施张孔严华金魏陶姜戚谢邹喻柏水窦章云潘葛奚范彭郎鲁韦昌马苗凤花方俞任袁柳酆鲍史唐费廉岑薛雷贺倪汤滕殷罗毕郝邬安常乐于时傅皮卡齐阙东殴殳沃利蔚越夔隆师巩厍聂晁钮龚程嵇邢滑裴陆荣翁荀羊於惠甄魏加封芮羿储靳汲邴糜松井段富弓康伍余元卜顾孟平穆萧尹姚邵堪汪祁毛禹狄米贝明臧计伏成戴谈宋茅庞熊纪舒屈项祝董粱杜阮蓝闵席季麻强贾路娄危江童颜梅盛林刁钟徐邱骆高夏蔡田樊胡凌霍虞万支柯咎管卢莫经房裘缪干解应宗宣丁易慎戈廖庚终暨居衡步都耿满弘勾敖融冷訾辛阚那简贲邓郁单杭洪包诸左石崔吉匡国文寇广禄牧隗谷宓蓬全郗班仰秋仲伊宫宁仇栾暴甘钭厉戎祖武符刘姜詹束龙叶幸司韶郜黎蓟薄印宿白怀蒲台从鄂索咸籍赖卓蔺屠蒙池乔阴郁胥能苍双闻莘党翟谭贡劳逄姬申扶堵冉宰郦雍郤璩桑桂濮牛寿通边扈燕冀郏浦尚农温别庄晏柴翟阎充慕连茹宦艾鱼容向古饶空曾沙须丰巢关蒯相查后江游竺巫乌焦巴";
	for($i=0;$i<strlen($xing);$i++){
		$xingDB[$xing[$i].$xing[$i+1]] = 1;
  		$i++;
  	}
  	$xing2s = explode(",","欧阳,上官,端木,南宫,谯笪,轩辕,令狐,钟离,闾丘,长孙,鲜于,宇文,司徒,司空,公孙,西门,东门,左丘,东郭,呼延,慕容,司马,夏侯,诸葛,东方,赫连,皇甫,尉迟,申屠");
  	foreach($xing2s AS $name){
		$xing2DB[$name] = 1;
	}

  	$filename = ROOT_PATH."inc/ciyu.dat"; 
  	if($handle=fopen($filename,"rb")){
		flock($handle,LOCK_SH);
		$filedata=fread($handle,filesize($filename));
		fclose($handle);
		$array=explode("\r\n",$filedata);
		$ss="<?php ";
		foreach($array AS $key=>$value){
			$value && $CiYuDB[$value]=1;
		}
	}
}
  
function splitword($str=""){
	if($str!=""){
		if(WEB_LANG=='utf-8'){
			require_once(ROOT_PATH."inc/class.chinese.php");
			$cnvert = new Chinese("UTF8","GB2312",$str,ROOT_PATH."./inc/gbkcode/");
			$str = $cnvert->ConvertIT();
		}elseif(WEB_LANG=='big5'){
			require_once(ROOT_PATH."inc/class.chinese.php");
			$cnvert = new Chinese("BIG5","GB2312",$str,ROOT_PATH."./inc/gbkcode/");
			$str = $cnvert->ConvertIT();
		}
		$Source_String = trim(Split_GetString(trim($str)));
	}
  	if($Source_String==""){
		return "";
	}
  	$Source_String = Split_GetString($Source_String);
  	$spwords = explode(" ",$Source_String);
  	$spLen = count($spwords);
  	$space = ' ';
  	for($i=($spLen-1);$i>=0;$i--){
  		if(trim($spwords[$i])==""){
			continue;
		}
  		if(ord($spwords[$i][0])<0x80){
  			if(ereg("[^0-9\.\+\-]",$spwords[$i])){
				$Result_String = $spwords[$i].$space.$Result_String;
			}else{
  				$nextword = "";
  				@$nextword = substr($Result_String,0,strpos($Result_String," "));
  				if(ereg("^年|月|日|时|分|秒|点|元|百|千|万|亿|位|辆|只|篇",$nextword)){
  					$Result_String = $spwords[$i].$Result_String;
  				}else{
  					$Result_String = $spwords[$i].$space.$Result_String;
  				}
  			}
  		}else{
			$c = $spwords[$i][0].$spwords[$i][1];
			$n = hexdec(bin2hex($c));
			if($c=="《"){
				$Result_String = $spwords[$i].$space.$Result_String;
			}elseif($n>0xA13F && $n < 0xAA40){ //标点符号
				$Result_String = $spwords[$i].$space.$Result_String;
			}else{ //正常短句
				if(strlen($spwords[$i]) <= 4){
					if(ereg("的|和|是$",$spwords[$i],$regs)){
						$spwords[$i] = ereg_replace($regs[0]."$","",$spwords[$i]).$space.$regs[0];
					}
  		  			if(!ereg("^年|月|日|时|分|秒|点|元|百|千|万|亿|位|辆|只|篇",$spwords[$i]) || $i==0){
  		  				$Result_String = $spwords[$i].$space.$Result_String;
  		  			}else{
  		  				$Result_String = $spwords[$i-1].$spwords[$i].$space.$Result_String; 
  		  				$i--;
  		  			}
  		  		}else{
  		  			$Result_String = Split_getwords($spwords[$i]).$space.$Result_String;
  		  		}
			}
		}
	}
	
	if(WEB_LANG=='utf-8'){
		require_once(ROOT_PATH."inc/class.chinese.php");
		$cnvert = new Chinese("GB2312","UTF8",$Result_String,ROOT_PATH."./inc/gbkcode/");
		$Result_String = $cnvert->ConvertIT();
	}elseif(WEB_LANG=='big5'){
		require_once(ROOT_PATH."inc/class.chinese.php");
		$cnvert = new Chinese("GB2312","BIG5",$Result_String,ROOT_PATH."./inc/gbkcode/");
		$Result_String = $cnvert->ConvertIT();
	}
	

	return $Result_String;
}

function Split_getwords($str){
	global $CiYuDB;
  	$space = ' ';
  	$spLen = strlen($str);
  	$WordArray = Array();
  	//这是逆向字典匹配
  	for($i=($spLen-1);$i>=0;){
  		//当i达到最小可能词的时候,要特别处理
		if($i<=3){
  			if($i==1){
				$WordArray[] = substr($str,0,2);
			}else{
				$w = substr($str,0,4);
				if(strlen($w)<13&&$CiYuDB[$w]){
					$WordArray[] = $w;   
  			   }else{
  				   $WordArray[] = substr($str,2,2);
  				   $WordArray[] = substr($str,0,2);
  			   }
			}
			$i = -1;
			break;
  		}
  		//分析在最小词以上时的情况
  		if($i>=13){
			$maxPos = 13;
		}else{
			$maxPos = $i;
		}
  		$isMatch = false;
  		for($j=$maxPos;$j>=0;$j=$j-2){
			$w = substr($str,$i-$j,$j+1);
			if(strlen($w)<13&&$CiYuDB[$w]){
				$WordArray[] = $w;
  			 	$i = $i-$j-1;
  			 	$isMatch = true;
  			 	break;
			}
		}
  		if(!$isMatch){
			if($i>1) {
				$WordArray[] = $str[$i-1].$str[$i];
  				$i = $i-2;
  			}
  		}
  	}
  	$rsStr = Split_duanluo($WordArray);
  	return $rsStr;
}

function Split_duanluo($WordArray){
	global $xingDB,$xing2DB;
	$CnSgNum = "一|二|三|四|五|六|七|八|九|十|百|千|万|亿|数|两";
  	$wlen = count($WordArray)-1;
  	$space = ' ';
  	for($i=$wlen;$i>=0;$i--){
  		if(ereg($CnSgNum,$WordArray[$i])){
  			$rsStr .= $space.$WordArray[$i];
  			if($i>0 && ereg("^年|月|日|时|分|秒|点|元|百|千|万|亿|位|辆|只|篇",$WordArray[$i-1])){
				$rsStr .= $WordArray[$i-1];
				$i--;
			}else{
				while($i>0 && ereg($CnSgNum,$WordArray[$i-1])){
					$rsStr .= $WordArray[$i-1]; $i--;
				}
  			}
  			continue;
  		}
  		//双字姓
  		if(strlen($WordArray[$i])==4 && isset($xing2DB[$WordArray[$i]])){
  			$rsStr .= $space.$WordArray[$i];
  			if($i>0&&strlen($WordArray[$i-1])==2){
  				$rsStr .= $WordArray[$i-1];
				$i--;
  				if($i>0&&strlen($WordArray[$i-1])==2){
					$rsStr .= $WordArray[$i-1];
					$i--;
				}
  			}
  		//单字姓
  		}elseif(strlen($WordArray[$i])==2 && isset($xingDB[$WordArray[$i]])){
			$rsStr .= $space.$WordArray[$i];
  			if($i>0&&strlen($WordArray[$i-1])==2){
				$rsStr .= $WordArray[$i-1];
				$i--;
				if($i>0 && strlen($WordArray[$i-1])==2){
					$rsStr .= $WordArray[$i-1];
					$i--;
				}
  			}
  		//普通词汇
  		}else{
  			$rsStr .= $space.$WordArray[$i];
  		}
	}
  	//返回本段分词结果
  	$rsStr = preg_replace("/^".$space."/","",$rsStr);
  	return $rsStr;
}

function Split_GetString($str){
  	$space = ' ';
    $slen = strlen($str);
    if($slen==0){
		return '';
	}
    $prechar = 0;	// 0-空白 1-英文 2-中文 3-符号
    for($i=0;$i<$slen;$i++){
		if(ord($str[$i]) < 0x81){
			if(ord($str[$i]) < 33){	//英文的空白符号
				if($prechar!=0&&$str[$i]!="\r"&&$str[$i]!="\n"){
					$strings .= $space;
				}
				$prechar=0;
				continue; 
			}elseif(ereg("[^0-9a-zA-Z@\.%#:/\\&_-]",$str[$i])){
				if($prechar==0){
					$strings .= $str[$i]; $prechar=3;
				}else{
					$strings .= $space.$str[$i]; $prechar=3;}
				}else{
					if($prechar==2||$prechar==3){
						$strings .= $space.$str[$i];
						$prechar=1;
					}else{
						if(ereg("@#%:",$str[$i])){
							$strings .= $str[$i];
							$prechar=3;
						}else{
							$strings .= $str[$i];
							$prechar=1;
						}
					}
				}
			}else{
				//如果上一个字符为非中文和非空格，则加一个空格
				if($prechar!=0 && $prechar!=2){
					$strings .= $space;
				}
				//如果中文字符
				if(isset($str[$i+1])){
					$c = $str[$i].$str[$i+1];
					if(ereg("％|＋|－|０|１|２|３|４|５|６|７|８|９|．",$c)){
						$strings .= Split_ReplaceNum($c); 
						$prechar = 2; 
						$i++; 
						continue; 
					}
					$n = hexdec(bin2hex($c));
					if($n>0xA13F && $n < 0xAA40){
						if($c=="《"){
							if($prechar!=0){
								$strings .= $space." 《";
						}else{
							$strings .= " 《";
						}
						$prechar = 2;
					}elseif($c=="》"){
						$strings .= "》 ";
						$prechar = 3;
					}else{
						if($prechar!=0){
							$strings .= $space.$c;
						}else{
							$strings .= $c;
						}
						$prechar = 3; 
					}
				}else{
					$strings .= $c;
					$prechar = 2;
				}
				$i++;
			}
		}
	}
	return $strings;
}


function Split_ReplaceNum($fnum){
	$nums = array("０","１","２","３","４","５","６","７","８","９","＋","－","％","．");
	$fnums = "0123456789+-%.";
	for($i=0;$i<count($nums);$i++){
		if($nums[$i]==$fnum){
			return $fnums[$i];
		}
	}
	return $fnum;
}


Load_Split_Word_array();
?>