<?php
!function_exists('html') && exit('ERR');

if($job=="list"&&$Apower[copyfrom_list])
{
	if(!$page){
		$page=1;
	}
	$rows=50;
	$min=($page-1)*$rows;
	$showpage=getpage("{$pre}copyfrom","","?lfj=$lfj&job=$job",$rows);
	$query = $db->query("SELECT * FROM {$pre}copyfrom ORDER BY list DESC LIMIT $min,$rows");
	while($rs = $db->fetch_array($query)){
		$listdb[]=$rs;
	}

	hack_admin_tpl('list');
}
elseif($action=="del"&&$Apower[copyfrom_list])
{
	foreach( $iddb AS $key=>$value){
		$db->query("DELETE FROM {$pre}copyfrom WHERE id='$value'");
	}
	jump("ɾ���ɹ�",$FROMURL,0);
}
elseif($job=="add"&&$Apower[copyfrom_list])
{
	$rsdb['list']=0;

	hack_admin_tpl('edit');
}
elseif($action=="add"&&$Apower[copyfrom_list])
{
	$db->query("INSERT INTO `{$pre}copyfrom` (`name` , `list` ) VALUES ( '$keywords', '$list')");
	jump("���ӳɹ�","index.php?lfj=$lfj&job=list",1);
}
elseif($job=="edit"&&$Apower[copyfrom_list])
{
	$rsdb=$db->get_one("SELECT * FROM {$pre}copyfrom WHERE id='$id'");

	hack_admin_tpl('edit');
}
elseif($action=="edit"&&$Apower[copyfrom_list])
{
	$db->query("UPDATE `{$pre}copyfrom` SET `name`='$keywords',`list`='$list' WHERE id='$id'");
	jump("�޸ĳɹ�","$FROMURL",1);
}

?>