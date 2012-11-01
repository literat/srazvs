<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

########################### POST a GET #########################

if($mid = requested("mid","")){
	$_SESSION['meetingID'] = $mid;
}
else {
	$mid = $_SESSION['meetingID'];
}

$id = requested("id","");
$cms = requested("cms","");
$search = requested("search","");
$error = requested("error","");

if(isset($_POST['checker'])){
	$id = $_POST['checker'];
	$query_id = NULL;
	foreach($id as $key => $value) {
		$query_id .= $value.',';
	}
	$query_id = rtrim($query_id, ',');
}
else {
	$query_id = $id;	
}

//// ziskam cenu srazu
$costSql = "SELECT cost, advance FROM kk_meetings WHERE id='".$mid."' LIMIT 1";
$costResult = mysql_query($costSql);
$costData = mysql_fetch_assoc($costResult);

//// ziskam pocet ucastniku
$countSql = "SELECT	COUNT(id) AS count
			FROM kk_visitors
			WHERE meeting='".$mid."' AND deleted='0'";
$countResult = mysql_query($countSql);
$visitor = mysql_fetch_assoc($countResult);

//defaultni hodnoty pro razeni
$_SESSION['order'] = "id";
if(isset($_GET['orderby'])) $order = clearString($_GET['orderby']);
else $order = "id";
if(isset($_GET['way'])) $way = clearString($_GET['way']);
else $way = "asc";

//// smazani uzivatele
if($cms == "del"){
	$delSql = "UPDATE kk_visitors
			SET deleted = '1'
			WHERE id IN (".$query_id.")";
	$delResult = mysql_query($delSql);
}

//// zaplaceni poplatku
if($cms == "pay"){
	$billSql = "SELECT bill FROM kk_visitors WHERE id IN (".$query_id.")";
	$billResult = mysql_query($billSql);
	$billData = mysql_fetch_assoc($billResult);
	
	if($billData['bill'] >= $costData['cost']){
		$error = "already_paid";
	}
	else {
		$paySql = "UPDATE kk_visitors
				SET bill = '".$costData['cost']."'
				WHERE id IN (".$query_id.")";
		$payResult = mysql_query($paySql);
	
		$setSql = "SELECT * FROM kk_settings WHERE name = 'mail_pay'";
		$setResult = mysql_query($setSql);
		$setData = mysql_fetch_assoc($setResult);

		$json = json_decode($setData['value']);

		$subject = html_entity_decode($json->subject);
		$message = html_entity_decode($json->message);
			
		$Container = new Container($GLOBALS['cfg']);
		$Emailer = $Container->createEmailer();
		if($Emailer->noticeVisitor($id, $subject, $message)) {
			redirect("index.php?error=mail_send");
		}
	}
}

//// zaplaceni zálohy
if($cms == "advance"){
	$billSql = "SELECT bill FROM kk_visitors WHERE id IN (".$query_id.")";
	$billResult = mysql_query($billSql);
	$billData = mysql_fetch_assoc($billResult);
	
	if($billData['bill'] >= $costData['advance']){
		$error = "already_paid";
	}
	else {
		$payAdvanceSql = "UPDATE kk_visitors
			SET bill = '".$costData['advance']."'
			WHERE id IN (".$query_id.")";
		$payAdvanceResult = mysql_query($payAdvanceSql);
	
		$setSql = "SELECT * FROM kk_settings WHERE name = 'mail_advance'";
		$setResult = mysql_query($setSql);
		$setData = mysql_fetch_assoc($setResult);

		$json = json_decode($setData['value']);

		$subject = html_entity_decode($json->subject);
		$message = html_entity_decode($json->message);
		
		$Container = new Container($GLOBALS['cfg']);
		$Emailer = $Container->createEmailer();
		if($Emailer->noticeVisitor($id, $subject, $message)) {
			redirect("index.php?error=mail_send");
		}
	}
}

//// vyhledavani
if($cms == "search" || isset($search)){
	if($search != ""){
		$searchQuery = "AND (`code` REGEXP '".$search."' 
						OR `group_num` REGEXP '".$search."' 
						OR `name` REGEXP '".$search."' 
						OR `surname` REGEXP '".$search."'
						OR `nick` REGEXP '".$search."'
						OR `city` REGEXP '".$search."' 
						OR `group_name` REGEXP '".$search."')";
	}
	else $searchQuery = "";
}
else $searchQuery = "";

########################## RAZENI ##########################
$arrow['id'] = "";
$arrow['name'] = "";
$arrow['surname'] = "";
$arrow['nick'] = "";
$arrow['email'] = "";
$arrow['group_name'] = "";
$arrow['bill'] = "";
$arrow['city'] = "";
$arrow['province'] = "";
$arrow['code'] = "";
$arrow['group_num'] = "";

if($way == "asc"){
	$_SESSION['order'] = $order;
	$_SESSION['order_way'] = "asc";
	$way = "desc";
	$orderby = "ORDER BY ".$order." ASC";
}
else{
	$_SESSION['order'] = $order;
	$_SESSION['order_way'] = "desc";
	$way = "asc";
	$orderby = "ORDER BY ".$order." DESC";
}
$arrow[$order] = "<img src='".$ICODIR."small/".$way.".png'>";

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Správa účastníků</div>
<?php printError($error); ?>
<div class='pageRibbon'>informace a exporty</div>
<div>
 počet účastníků: <span style="font-size:12px; font-weight:bold;"><?php echo $visitor['count']; ?></span>
 <span style="margin-left:10px; margin-right:10px;">|</span> 
 <a style='text-decoration:none; padding-right:2px;' href='export.xls.php?mid=<?php echo $mid; ?>'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/xlsx.png' />
  export účastníků
 </a>

</div>

<div class='pageRibbon'>seznam účastníků</div>

<?php

########################## GENEROVANI STRANKY #############################33

$html = "<div class='link'>\n";


$html .= "
<form action='index.php' method='post'>
<label>hledej:</label>
<input type='text' name='search' size='30' value='".$search."' />

 <button type='submit' onClick=\"this.form.submit()\">
  <img src='".$ICODIR."small/search.png' /> Hledej</button>
 <!--<button type='button' onclick=\"window.location.replace('index.php')\">
  <img src='".$ICODIR."small/storno.png'  /> Zpět</button>-->
  
<input type='hidden' name='cms' value='search'>
</form>
";

$html .= "<a class='link' href='create.php'>\n";
$html .= "<img src='".$ICODIR."small/new.png' />NOVÝ ÚČASTNÍK\n";
$html .= "</a>\n";

$html .= "</div>";

$html .= "<form name='checkerForm' method='post' action='index.php'>\n";

$html .= "<a href='javascript:select(1)'>Zaškrtnout vše</a> / \n";
$html .= "<a href='javascript:select(0)'>Odškrtnout vše</a>\n";
$html .= "<span style='margin-left:10px;'>Zaškrtnuté:</span>\n";
$html .= "<button onClick=\"this.form.submit()\" title='Záloha' value='advance' name='cms' type='submit'>\n";
$html .= "<img class='edit' src='".$ICODIR."small/advance.png' /> Záloha\n";
$html .= "</button>\n";
$html .= "<button onClick=\"this.form.submit()\" title='Platba' value='pay' name='cms' type='submit'>\n";
$html .= "<img class='edit' src='".$ICODIR."small/pay.png' /> Platba\n";
$html .= "</button>\n";
$html .= "<button onClick=\"return confirm('Opravdu SMAZAT tyto účastníky? Jste si jisti?')\"' title='Smazat' value='del' name='cms' type='submit'>\n";
$html .= "<img class='edit' src='".$ICODIR."small/delete.gif' /> Smazat\n";
$html .= "</button>\n";
$html .= "<button onclick=\"this.form.action='mail.php';\" title='Hromadný e-mail' value='mail' name='cms' type='submit'>\n";
$html .= "<img src='".$ICODIR."small/mail.png'  /> Hromadný e-mail\n";
$html .= "</button>\n";

$html .= "<table class='list'>\n";
$html .= "<tr>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td class='tab1'><a href='?orderby=id&amp;way=".$way."'>id ".$arrow['id']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=code&amp;way=".$way."'>symbol".$arrow['code']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=group_num&amp;way=".$way."'>evidence".$arrow['group_num']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=name&amp;way=".$way."'>jméno ".$arrow['name']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=surname&amp;way=".$way."'>příjmení ".$arrow['surname']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=nick&amp;way=".$way."'>přezdívka ".$arrow['nick']."</a></td>\n";
$html .= "<td class='tab1'>e-mail".$arrow['email']."</td>\n";
$html .= "<td class='tab1'><a href='?orderby=group_name&amp;way=".$way."'>středisko/přístav".$arrow['group_name']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=city&amp;way=".$way."'>město".$arrow['city']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=province&amp;way=".$way."'>kraj".$arrow['province']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=bill&amp;way=".$way."'>zaplaceno".$arrow['bill']."</a></td>\n";
$html .= "</tr>\n";

//// ziskam data ucastniku
$sql = "SELECT 	vis.id AS id,
				code,
				name,
				surname,
				nick, 
				email,
				group_name,
				group_num,
				city,
				province_name AS province,
				bill,
				birthday
				/*CONCAT(LEFT(name,1),LEFT(surname,1),SUBSTRING(birthday,3,2)) AS code*/
		FROM kk_visitors AS vis
		LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
		WHERE meeting='".$mid."' AND deleted='0' ".$searchQuery."
		".$orderby."";
$result = mysql_query($sql);
$rows = mysql_affected_rows();

if($rows == 0){
	$html .= "<tr class='radek1'>";
	$html .= "<td><input disabled type='checkbox' /></td>\n";
	$html .= "<td><img class='edit' src='".$ICODIR."small/edit2.gif' /></td>\n";
	$html .= "<td><img class='edit' src='".$ICODIR."small/delete2.gif' /></td>\n";
	$html .= "<td><img class='edit' src='".$ICODIR."small/advance2.png' /></td>\n";
	$html .= "<td><img class='edit' src='".$ICODIR."small/pay2.png' /></td>\n";
	$html .= "<td><img class='edit' src='".$ICODIR."small/pdf2.png' /></td>\n";
	$html .= "<td colspan='15' class='emptyTable'>Nejsou k dispozici žádné položky.</td>";
	$html .= "</tr>";
}
else{
	while($data = mysql_fetch_assoc($result)){
		$code4bank = substr($data['name'], 0, 1).substr($data['surname'], 0, 1).substr($data['birthday'], 2, 2);
		
		if($costData['cost'] <= $data['bill']) $payment = "<acronym title='Zaplaceno'><img src='".$ICODIR."small/paid.png' alt='zaplaceno' /></acronym>";
		elseif(200 <= $data['bill']) $payment = "<acronym title='Zaplacena záloha'><img src='".$ICODIR."small/advancement.png' alt='zaloha' /></acronym>";
		else $payment = "<acronym title='Nezaplaceno'><img src='".$ICODIR."small/notpaid.png' alt='nezaplaceno' /></acronym>";
		
    	$html .= "<tr class='radek1'>";
		$html .= "<td><input type='checkbox' name='checker[]'  value='".$data['id']."' /></td>\n";
		$html .= "<td><a href='update.php?id=".$data['id']."' title='Upravit'><img class='edit' src='".$ICODIR."small/edit.gif' /></a></td>\n";
		$html .= "<td><a href='?id=".$data['id']."&amp;cms=advance&amp;search=".$search."' title='Záloha'><img class='edit' src='".$ICODIR."small/advance.png' /></a></td>\n";
		$html .= "<td><a href='?id=".$data['id']."&amp;cms=pay&amp;search=".$search."' title='Zaplatit'><img class='edit' src='".$ICODIR."small/pay.png' /></a></td>\n";
		$html .= "<td><a href='../exports/evidence.pdf.php?vid=".$data['id']."&type=confirm' title='Doklad'><img class='edit' src='".$ICODIR."small/pdf.png' /></a></td>\n";
		$html .= "<td><a href=\"javascript:confirmation('?id=".$data['id']."&amp;cms=del&amp;search=".$search."', 'účastník: ".$data['nick']." -> Opravdu SMAZAT tohoto účastníka? Jste si jisti?')\" title='Odstranit'><img class='edit' src='".$ICODIR."small/delete.gif' /></a></td>\n";
		$html .= "<td class='text'>".$data['id']."</td>\n";
		$html .= "<td class='text'>".$data['code']."</td>\n";
		$html .= "<td class='text'>".$data['group_num']."</td>\n";
		$html .= "<td class='text'>".$data['name']."</td>\n";
    	$html .= "<td class='text'>".$data['surname']."</td>\n";
		$html .= "<td class='text'>".$data['nick']."</td>\n";
		$html .= "<td class='text'>".$data['email']."</td>\n";
		$html .= "<td class='text'>".$data['group_name']."</td>\n";
		$html .= "<td class='text'>".$data['city']."</td>\n";
		$html .= "<td class='text'>".$data['province']."</td>\n";
		$html .= "<td class='text'>".$payment." ".$data['bill'].",-Kč</td>\n";
		$html .= "</tr>";
	}
};

$html .= "<tr>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td></td>\n";
$html .= "<td class='tab1'><a href='?orderby=id&amp;way=".$way."'>id ".$arrow['id']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=code&amp;way=".$way."'>symbol".$arrow['code']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=group_num&amp;way=".$way."'>evidence".$arrow['group_num']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=name&amp;way=".$way."'>jméno ".$arrow['name']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=surname&amp;way=".$way."'>příjmení ".$arrow['surname']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=nick&amp;way=".$way."'>přezdívka ".$arrow['nick']."</a></td>\n";
$html .= "<td class='tab1'>e-mail".$arrow['email']."</td>\n";
$html .= "<td class='tab1'><a href='?orderby=group_name&amp;way=".$way."'>středisko/přístav".$arrow['group_name']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=city&amp;way=".$way."'>město".$arrow['city']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=province&amp;way=".$way."'>kraj".$arrow['province']."</a></td>\n";
$html .= "<td class='tab1'><a href='?orderby=bill&amp;way=".$way."'>zaplaceno".$arrow['bill']."</a></td>\n";
$html .= "</tr>\n";              
$html .= "</table>\n";

$html .= "<a onClick=\"checkAllCheckboxes(document.visitors.checker)\"'>Zaškrtnout vše</a> / \n";
$html .= "<a onClick=\"uncheckAllCheckboxes(document.visitors.checker)\">Odškrtnout vše</a>\n";
$html .= "<span style='margin-left:10px;'>Zaškrtnuté:</span>\n";
$html .= "<button onClick=\"this.form.submit()\" title='Záloha' value='advance' name='cms' type='submit'>\n";
$html .= "<img class='edit' src='".$ICODIR."small/advance.png' /> Záloha\n";
$html .= "</button>\n";
$html .= "<button onClick=\"this.form.submit()\" title='Platba' value='pay' name='cms' type='submit'>\n";
$html .= "<img class='edit' src='".$ICODIR."small/pay.png' /> Platba\n";
$html .= "</button>\n";
$html .= "<button onClick=\"this.form.submit()\"' title='Smazat' value='del' name='cms' type='submit'>\n";
$html .= "<img class='edit' src='".$ICODIR."small/delete.gif' /> Smazat\n";
$html .= "</button>\n";
$html .= "<button onclick=\"this.form.submit()\" title='Hromadný e-mail' value='mail' name='cms' type='submit'>\n";
$html .= "<img src='".$ICODIR."small/mail.png'  /> Hromadný e-mail\n";
$html .= "</button>\n";

$html .= "</form>\n";

echo $html;

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>