<?php
!function_exists('html') && exit('ERR');

if($webdb['close_dz_credit']){
	showerr('系统关闭了兑换功能!');
}

if(!defined("UC_CONNECT")){
	showerr('你没有整合UC!');
}
if(!$webdb['dzbbs_pre']){
	showerr('请先在后台<整合外部系统设置>中去设置<DISCUZ论坛贴子调用设置>中的<数据表前缀>');
}
if(!$lfjuid){
	showerr('你还没登录!');
}

$webdb['dz_credit_name1'] || $webdb['dz_credit_name1']='论坛威望';
$webdb['dz_credit_name2'] || $webdb['dz_credit_name2']='论坛金钱';
$webdb['dz_credit_name3'] || $webdb['dz_credit_name3']='论坛贡献';

$scaleA = array(
			'c1'=>($webdb['dz_credit_1']?$webdb['dz_credit_1']:2),		//2个论坛威望换一个本系统积分
			'c2'=>($webdb['dz_credit_2']?$webdb['dz_credit_2']:1),	//1.5个论坛金钱换一个本系统积分
			'c3'=>($webdb['dz_credit_3']?$webdb['dz_credit_3']:0.5),	//0.5个论坛贡献换一个本系统积分
		);

$dzCR = $db->get_one("SELECT * FROM {$webdb[dzbbs_pre]}common_member_count WHERE uid='$lfjuid'");

$scaleB = '';
foreach($scaleA AS $key=>$value){
	$scaleB[$key] = 1/$value;
	if(!is_int($scaleB[$key])){
		$scaleB[$key] =ceil($scaleB[$key]*10)/10;
	}
}

if($step){
	if(!$scaleA["c$type"]){
		showerr('请选择一个论坛积分类型!');
	}
	$atc_num = intval($atc_num);
	if($atc_num<1){
		showerr('兑换数量必须1个以上!');
	}
	if($step==2){
		$num = ceil($atc_num * $scaleA["c$type"]);
		if($dzCR["extcredits$type"]<$num){
			showerr("你的论坛积分不足 {$num} 个");
		}
		$db->query("UPDATE {$webdb[dzbbs_pre]}common_member_count SET extcredits$type=extcredits$type-$num WHERE uid='$lfjuid'");
		add_user($lfjuid,$atc_num,'论坛积分兑入');
	}elseif($step==3){
		$num = ceil($atc_num * $scaleB["c$type"]);
		if($lfjdb['money']<$num){
			showerr("你在本系统的{$webdb[MoneyName]}不足 {$num} {$webdb[MoneyDW]}");
		}
		$db->query("UPDATE {$webdb[dzbbs_pre]}common_member_count SET extcredits$type=extcredits$type+$atc_num WHERE uid='$lfjuid'");
		add_user($lfjuid,-$num,'论坛积分兑出');
	}
	refreshto("hack.php?hack=$hack",'兑换成功!',1);
}


require(ROOT_PATH."member/head.php");
require(dirname(__FILE__)."/template/dz_credit.htm");
require(ROOT_PATH."member/foot.php");
?>