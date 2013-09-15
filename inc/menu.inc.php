<?php

$sql = "SELECT  id AS mid,
				place,
				DATE_FORMAT(start_date, '%Y') AS year
		FROM kk_meetings
		ORDER BY id DESC";
$result = mysql_query($sql);

############################## GENEROVANI STRANKY ###############################333

$menu = "<!-- start of menuCanvas -->\n";
$menu .= "<div id='menuCanvas'>\n";
$menu .= " <div id='menuContent'>\n";
 
$menu .= "  <div class='menuItem'>všechny srazy</div>\n";
$menu .= "   <ul>";
$menu .= "    <li><a href='".MEET_DIR."/?cms=list-view'>seznam srazů</a></li>\n";
$menu .= "   </ul>";
 
$menu .= "  <div class='menuItem'>jednotlivé srazy</div>\n";
$menu .= "   <ul>";

while($data = mysql_fetch_array($result)){
	$menu .= "    <li><a href='".MEET_DIR."/?mid=".$data['mid']."'>".$data['place']." ".$data['year']."</a></li>\n";
}

$menu .= "   </ul>";
$menu .= " </div>\n";
$menu .= "</div>\n";
$menu .= "<!-- end of menuCanvas -->\n";

echo $menu;