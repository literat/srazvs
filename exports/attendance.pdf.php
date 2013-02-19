<?php
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

########################### POST a GET #########################

if($mid = requested("mid","")){
	$_SESSION['meetingID'] = $mid;
} else {
	$mid = $_SESSION['meetingID'];
}

$id = requested("id","");
$cms = requested("cms","");
$error = requested("error","");

$Container = new Container($GLOBALS['cfg'], $mid);
$ExportHandler = $Container->createExport();

$ExportHandler->printAttendance();
exit();

/* depracated */

########################### AKTUALNI SRAZ ##############################

$mid = $_SESSION['meetingID'];

$sql = "SELECT	vis.id AS id,
				name,
				surname,
				nick,
				DATE_FORMAT(birthday, '%d. %m. %Y') AS birthday,
				street,
				city,
				postal_code,
				group_num,
				group_name,
				place,
				DATE_FORMAT(start_date, '%Y') AS year
		FROM kk_visitors AS vis
		LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
		WHERE meeting='".$mid."' AND vis.deleted='0'
		ORDER BY surname ASC
		";
$result = mysql_query($sql);

$headerResult = mysql_query($sql);
$headerData = mysql_fetch_assoc($headerResult);

//$mid = $headerData['id'];
$meetingHeader = $headerData['place']." ".$headerData['year'];

$filename = "attendance_list";

$fileaddr = "../tmp/".$filename.".pdf";

################## VLOZENE STYLY ##################################

$html = "<head>";
$html .= "<style>";
$html .= "
body {
	font-family:Arial,Geneva,Sans-Serif;
	text-align:left;
}

table {
	border-collapse:collapse;
	width:100%;
}

td {
	wwidth:100%;
	padding:5px;
	border:1px solid black;
	font-size:9px;
}

/*.name {width:100px;}*/
.signature {width:80px;}
.header{color:white;background-color:black;}
";
$html .= "</style>";
$html .= "</head>";
$html .= "<body>";

################################## HTML #####################################

$html .= "<table>\n";

$i = 0;
while($data = mysql_fetch_assoc($result)){
	if($i % 44 == 0){
		$html .= "<tr>\n";
		$html .= "<td class='header'>Příjmení a Jméno</td>\n";
		$html .= "<td class='header'>Narození</td>\n";
		$html .= "<td class='header'>Adresa</td>\n";
		$html .= "<td class='header'>Středisko/Přístav</td>\n";
		$html .= "<td class='header'>Podpis</td>\n";
		$html .= "</tr>\n";
	}
	
	$html .= "<tr>\n";
	$html .= "<td class='name'>\n";
	$html .= $data['surname']." ".$data['name'];
	$html .= "</td>\n";
	$html .= "<td class='birthday'>\n";
	$html .= $data['birthday'];
	$html .= "</td>\n";
	$html .= "<td class='address'>\n";
	$html .= $data['street'].", ".$data['city'].", ".$data['postal_code'];
	$html .= "</td>\n";
	$html .= "<td class='group'>\n";
	$html .= $data['group_num'].", ".$data['group_name'];
	$html .= "</td>\n";
	$html .= "<td class='signature'>\n";
	$html .= "&nbsp;";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	
	$i++;
}

$html .= "</table>\n";

################## GENEROVANI STRANKY #############################

$html .= "</body>";

if(defined('DEBUG') && DEBUG === true){
	//var_dump($ExportHandler);
	echo $html;
	exit('DEBUG_MODE');
}

$ExportHandler->Pdf->SetHeader($meetingHeader.'|sraz VS|Prezenční listina');

$ExportHandler->Pdf->WriteHTML($html, 0);

$name = $filename.".pdf";
// download
//$ExportHandler->Pdf->Output($name, "D");
?>