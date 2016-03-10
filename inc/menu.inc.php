<?php

$menuItems = $data['database']->query(
	'SELECT id AS mid,
			place,
			DATE_FORMAT(start_date, "%Y") AS year
	FROM kk_meetings
	WHERE deleted = ?
	ORDER BY id DESC',
	'0')->fetchAll();

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

foreach($menuItems as $item) {
	$menu .= "    <li><a href='?mid=".$item['mid']."'>".$item['place']." ".$item['year']."</a></li>\n";
}

$menu .= "   </ul>";
$menu .= " </div>\n";
$menu .= "</div>\n";
$menu .= "<!-- end of menuCanvas -->\n";

echo $menu;