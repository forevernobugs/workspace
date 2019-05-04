<?php
require_once(dirname(__FILE__)."/"."global.php");
$page||$page=1;
$rows=10;
$min=($page-1)*$rows;
$query = $db->query("SELECT A.*,B.content FROM {$pre}article A LEFT JOIN {$pre}reply B ON A.aid=B.aid WHERE A.title LIKE '%$keyword%' AND A.mid=0 ORDER BY A.aid DESC LIMIT $min,$rows");
while($rs = $db->fetch_array($query)){
	$rs[content]=@preg_replace('/<([^<]*)>/is',"",$rs[content]);	//把HTML代码过滤掉
	$rs[content]=@preg_replace('/ |&nbsp;/is',"",$rs[content]);	//把空格过滤掉
	$rs[content]=get_word($rs[content],120);
	if($rs[picurl]){
		$rs[picurl]=filtrate($rs[picurl]);
		$rs[picurl]=tempdir($rs[picurl]);
	}
	$rs[urls]=$rs[mid]?"$webdb[www_url]/bencandy.php?fid=$rs[fid]&id=$rs[aid]":"bencandy.php?fid=$rs[fid]&id=$rs[aid]";
	$listdb[]=$rs;
}
if($job=="show"){
	if(!$listdb){
		echo "nodate";
	}else{
		foreach($listdb AS $key=>$rs){
			$rs[showimg]=$rs[ispic]?"<img src=\"$rs[picurl]\" width='100' height='75'  onerror=\"this.src='$webdb[www_url]/images/default/nopic.jpg'\"/>":"";
			echo "<div class=\"List\"><a href=\"$rs[urls]\"><h3>$rs[title]</h3>{$rs[showimg]}<p>$rs[content]</p></a></div>";
		}
	}
	exit;
}
require_once(dirname(__FILE__)."/"."template/head.htm");
require_once(dirname(__FILE__)."/"."template/search.htm");
require_once(dirname(__FILE__)."/"."template/foot.htm");
?>