<?php

require_once('inc/define.inc.php');
include_once($INCDIR.'access.inc.php');

$sql = "SELECT id
		FROM kk_meetings
		ORDER BY id DESC
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

redirect("meetings/?mid=".$data['id']."");

/*echo "<script type='javascript'>
	   window.location='meetings/?mid=".$data['id']."';
	  </script>";*/
?>