<?php
!function_exists('html') && exit('ERR');

if($job=="set"&&$Apower[dz_credit])
{
	$sms_type[$webdb[sms_type]]=' checked ';


	hack_admin_tpl('set');
}
elseif($action=="set"&&$Apower[dz_credit])
{
	write_config_cache($webdbs);
	jump("ÉèÖÃ³É¹¦",$FROMURL);
}

?>