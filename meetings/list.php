<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

############################# POST a GET ##################################

$id = requested("id","");
$cms = requested("cms","");
$error = requested("error","");

//defaultni hodnoty pro razeni
$_SESSION['order'] = "id";
if(isset($_GET['orderby'])) $order = clearString($_GET['orderby']);
else $order = "id";
if(isset($_GET['way'])) $way = clearString($_GET['way']);
else $way = "asc";

//smazani uzivatele
if($cms == "del"){
	$sql = "UPDATE kk_meetings 
			SET deleted = '1'
			WHERE id = ".$id."
			LIMIT 1";
	$result = mysql_query($sql);
}

########################## RAZENI ##########################
$arrow['id'] = "";
$arrow['name'] = "";
$arrow['place'] = "";
$arrow['start_date'] = "";
$arrow['contact'] = "";

if($way == "asc"){
	$_SESSION['order'] = $order;
	$_SESSION['order_way'] = "asc";
	$way = "desc";
	$orderby = "ORDER BY `$order` ASC";
}
else{
	$_SESSION['order'] = $order;
	$_SESSION['order_way'] = "desc";
	$way = "asc";
	$orderby = "ORDER BY `$order` DESC";
}
$arrow[$order] = "<img src='".$ICODIR."small/".$way.".png'>";

########################## GENEROVANI STRANKY #############################
include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Správa srazů</div>
<? printError($error); ?>
<div class='pageRibbon'>seznam srazů</div>

<?php

########################## GENEROVANI STRANKY #############################33

$html = "<div class='link'><a class='link' href='create.php'><img src='".$ICODIR."small/new.png' />NOVÝ SRAZ</a></div>\n";

$html .= "<table class='list'>\n";
$html .= "<tr>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td class='tab1'><a href='?orderby=id&amp;way=".$way."'>id ".$arrow['id']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=place&amp;way=".$way."'>místo ".$arrow['place']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=start_date&amp;way=".$way."'>začátek ".$arrow['start_date']."</a></td>\n";
$html .= "<td class='tab1'>konec</td>\n";
$html .= "<td class='tab1'>otevření registrace</td>\n";
$html .= "<td class='tab1'>uzavření registrace</td>\n";
$html .= "<td class='tab1'><a href='?orderby=contact&amp;way=".$way."'>kontakt ".$arrow['contact']."</a></td>\n";
$html .= "<td class='tab1'>e-mail</td>\n";
$html .= "<td class='tab1'>telefon</td>\n";
$html .= "</tr>\n";

$sql = "SELECT 	id,
				place,
				DATE_FORMAT(start_date, '%d. %m. %Y') AS start_date,
				DATE_FORMAT(end_date, '%d. %m. %Y') AS end_date,
				DATE_FORMAT(open_reg, '%d. %m. %Y %H:%i:%s') AS open_reg,
				DATE_FORMAT(close_reg, '%d. %m. %Y %H:%i:%s') AS close_reg,
				contact,
				email,
				gsm
		FROM kk_meetings
		WHERE deleted = '0'
		".$orderby." 
		LIMIT 30";
$result = mysql_query($sql);
$rows = mysql_affected_rows();

if($rows == 0){
	$html .= "<tr class='radek1'>";
	$html .= "<td><img class='edit' src='".$ICODIR."small/edit2.gif' /></td>\n";
	$html .= "<td><img class='edit' src='".$ICODIR."small/delete2.gif' /></td>\n";
	$html .= "<td colspan='11' class='emptyTable'>Nejsou k dispozici žádné položky.</td>";
	$html .= "</tr>";
}
else{
	while($data = mysql_fetch_assoc($result)){			
    	$html .= "<tr class='radek1'>";
		$html .= "<td><a href='update.php?mid=".$data['id']."' title='Upravit'><img class='edit' src='".$ICODIR."small/edit.gif' /></a></td>\n";
		$html .= "<td><a href=\"javascript:confirmation('?id=".$data['id']."&amp;cms=del', 'sraz: ".$data['place']." ".$data['start_date']." -> Opravdu SMAZAT tento sraz? Jste si jisti?')\" title='Odstranit'><img class='edit' src='".$ICODIR."small/delete.gif' /></a></td>\n";
		$html .= "<td class='text'>".$data['id']."</td>\n";
		$html .= "<td class='text'>".$data['place']."</td>\n";
    	$html .= "<td class='text'>".$data['start_date']."</td>\n";
		$html .= "<td class='text'>".$data['end_date']."</td>\n";
		$html .= "<td class='text'>".$data['open_reg']."</td>\n";
		$html .= "<td class='text'>".$data['close_reg']."</td>\n";
		$html .= "<td class='text'>".$data['contact']."</td>\n";
		$html .= "<td class='text'>".$data['email']."</td>\n";
		$html .= "<td class='text'>".$data['gsm']."</td>\n";
		$html .= "</tr>";
	}
};

$html .= "<tr>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td class='tab1'><a href='?orderby=id&amp;way=".$way."'>id ".$arrow['id']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=place&amp;way=".$way."'>místo ".$arrow['place']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=start_date&amp;way=".$way."'>začátek ".$arrow['start_date']."</a></td>\n";
$html .= "<td class='tab1'>konec</td>\n";
$html .= "<td class='tab1'>otevření registrace</td>\n";
$html .= "<td class='tab1'>uzavření registrace</td>\n";
$html .= "<td class='tab1'><a href='?orderby=contact&amp;way=".$way."'>kontakt ".$arrow['contact']."</a></td>\n";
$html .= "<td class='tab1'>e-mail</td>\n";
$html .= "<td class='tab1'>telefon</td>\n";
$html .= "</tr>\n";              
$html .= "</table>\n";

echo $html;

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>