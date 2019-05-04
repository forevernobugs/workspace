<?php
!function_exists('html') && exit('ERR');
set_time_limit(0);
if($job=='list'&&$Apower[smallpic])
{
	hack_admin_tpl('list');
}
if ($action=='do'&&$Apower[smallpic]){
	if ($p<1){
		$p = 0;
	}
	$rows = $isCopy?5:30;
	$min = $p * $rows;
	$query = $db->query("SELECT A.content,B.aid,B.fid,B.picurl,B.uid FROM {$pre}article B LEFT JOIN {$pre}reply A ON A.aid = B.aid WHERE B.mid=0 LIMIT $min,$rows");
	while ($rs = $db->fetch_array($query)){
		if($rs[picurl]&&!$isCover){
			continue;
		}
		$str = En_TruePath($rs[content],0);
	    $array = get_content_attachment($str);
	    if ($array[0]){
	    	foreach ($array AS $val){
	    		if (strtolower(strrchr($val,'.'))=='.gif' || strtolower(strrchr($val,'.'))=='.jpg' || strtolower(strrchr($val,'.'))=='.png'){
	    			gdpic(ROOT_PATH."$webdb[updir]/$val", ROOT_PATH."$webdb[updir]/{$val}.jpg", 300, 225,$isFill?array('fix'=>1):'');
				    gdpic(ROOT_PATH."$webdb[updir]/$val", ROOT_PATH."$webdb[updir]/{$val}.jpg.jpg", 255, 300,$isFill?array('fix'=>1):'');				    
				    gdpic(ROOT_PATH."$webdb[updir]/$val", ROOT_PATH."$webdb[updir]/{$val}.jpg.jpg.jpg", 300, 300,$isFill?array('fix'=>1):'');
				    $picurl = "{$val}.jpg";
			    	break;
	    		}
	    	}
	    }else {
	    	preg_match_all("/http:\/\/([^ '\"<>]+)\.(gif|jpg|png)/is",$rs[content],$arr);
	    	if($arr[0][0]){
				$picurl = $arr[0][0];
				if($isCopy){
					if (!file_exists(ROOT_PATH."$webdb[updir]/article/$rs[fid]/")){
	    				makepath(ROOT_PATH."$webdb[updir]/article/$rs[fid]/");
					}
					$pic=ROOT_PATH."$webdb[updir]/article/$rs[fid]/{$rs[uid]}_".basename($arr[0][0]);
					copy($arr[0][0],$pic);
					$val = "article/$rs[fid]/{$rs[uid]}_".basename($arr[0][0]);
					gdpic($pic, ROOT_PATH."$webdb[updir]/{$val}.jpg", 300, 225,$isFill?array('fix'=>1):'');
				    gdpic($pic, ROOT_PATH."$webdb[updir]/{$val}.jpg.jpg", 255, 300,$isFill?array('fix'=>1):'');				    
				    gdpic($pic, ROOT_PATH."$webdb[updir]/{$val}.jpg.jpg.jpg", 300, 300,$isFill?array('fix'=>1):'');
					unlink($pic);
					$picurl = "{$val}.jpg";
				}				
	    	}
	    }
	    $i++;
	    $db->update("UPDATE {$pre}article SET picurl = '{$picurl}',ispic = 1 WHERE aid = {$rs[aid]}");
	}
	if (!$i){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />';
		echo "<A HREF='?lfj={$lfj}&job=$job'>缩略图生成完毕,请点击返回</A>";
		exit;
	}
	$p= $p+ 1;
	echo "请稍候,正在生成图片缩略图$p...<META HTTP-EQUIV=REFRESH CONTENT='0;URL=?lfj={$lfj}&action={$action}&isCover={$isCover}&isCopy={$isCopy}&isFill={$isFill}&p=$p'> ";
}
?>