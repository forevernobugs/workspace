<?php
require_once(dirname(__FILE__)."/"."global.php");

if(WEB_LANG!='utf-8'){	
	require_once(ROOT_PATH."inc/class.chinese.php");
	$cnvert = new Chinese("UTF8","GB2312",$queryString,ROOT_PATH."./inc/gbkcode/");
	$queryString = $cnvert->ConvertIT();
}
$queryString=filtrate($queryString);
  if(strlen($queryString) >0) {		
	  $query = $db->query("SELECT title FROM {$pre}article WHERE title LIKE '%$queryString%' ORDER BY hits DESC LIMIT 10");
		  while ($result = $db->fetch_array($query)) {
		
			  echo '<li onClick="fill(\''.$result[title].'\');">'.$result[title].'</li>';
		  }
  }

?>