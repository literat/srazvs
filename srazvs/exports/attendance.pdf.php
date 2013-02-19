<?php
require_once('../inc/define.inc.php');
require_once("../inc/mpdf/mpdf.php");

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

//echo $html;

//die();
$mpdf = new mPDF('utf-8', 'A4');
$mpdf->useOnlyCoreFonts = true;
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetAutoFont(0);

$mpdf->defaultfooterfontsize = 16;
$mpdf->defaultfooterfontstyle = B;
$mpdf->SetHeader(''.$meetingHeader.'|sraz VS|Prezenční listina');

// CSS soubor
//$stylesheet = file_get_contents(ROOT_DIR.'styles/css/print_event.css');

//$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($html, 0);

$name = $filename.".pdf";
// download
$mpdf->Output($name, "D");
?>