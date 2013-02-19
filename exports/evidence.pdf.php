<?php
require_once('../inc/define.inc.php');
require_once("../libs/mpdf/mpdf.php");

########################### AKTUALNI SRAZ ##############################

$vid = intval(requested("vid",""));
$type = requested("type","");
$mid = $_SESSION['meetingID'];
//$mid = 2;
if($vid == "all"){
	$limit = "";
	$visid = "";
}
else {
	$limit = "LIMIT 1";
	$visid = "vis.id='".$vid."' AND";
}

$Container = new Container($GLOBALS['cfg'], $mid);
$ExportHandler = $Container->createExport();

$ExportHandler->printEvidence($type, $vid);
exit();

/* depracated */

$sql = "SELECT	vis.id AS id,
				name,
				surname,
				street,
				city,
				postal_code,
				bill,
				place,
				UPPER(LEFT(place, 2)) AS abbr_place,
				DATE_FORMAT(start_date, '%d. %m. %Y') AS date,
				DATE_FORMAT(start_date, '%Y') AS year,
				cost - bill AS balance,
				DATE_FORMAT(birthday, '%d. %m. %Y') AS birthday,
				group_num,
				numbering
		FROM kk_visitors AS vis
		LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
		WHERE ".$visid." meeting='".$mid."' AND vis.deleted='0'
		ORDER BY surname, name
		".$limit."";
$result = mysql_query($sql);

//$filename = noDiakritika($data['place'].$data['year']."-program");
$filename = "faktura";
$fileaddr = "../tmp/".$filename.".pdf";

###################################### CSS ##################################

$mpdf = new mPDF('utf-8', 'A4');

$html = "<head>\n";
$html .= "<style>\n";
$html .= "
body {
	font-family:Arial,Geneva,Sans-Serif;
	font-size:9px;
	text-align:left;
}

table {
	border:3px solid black;
	border-collapse:separate;
	width:100%;
}

td {
	border:1px solid black;
	text-align:left;
	padding:3px;
}

table.summary {
	border-collapse:collapse;
	width:100%;
	border: 1px;
}

table.summary td {
	padding:5px;
	border:1px solid black;
	font-size:9px;
}

/*.name {width:100px;}*/
.signature {width:80px;}
.header{color:white;background-color:black;}

";
$html .= "</style>\n";
$html .= "</head>\n";

############################ GENEROVANI STRANKY #############################

$hkvs_header = "Junák ČR, Kapitanát vodních skautů | ";
$hkvs_header .= "Senovážné náměstí 977/24, Praha 1, 116 47 | ";
$hkvs_header .= "IČ: 65991753, ČÚ: 2300183549/2010";

$html .= "<body>\n";

if($type == 'summary') {
	$i = 0;
	$html .= "<table class='summary'>\n";
}
else $i = 1;

while($data = mysql_fetch_assoc($result)){
	if($type == "evidence" && $data['balance'] > 0){
	$html .= "<h2>PŘÍJMOVÝ POKLADNÍ DOKLAD</h2>\n";
	
	$html .= "<table>\n";
	$html .= " <tr>\n";
	$html .= "  <td width='34' style='text-align:center;'>\n";
	$html .= "   <img width='32' src='".$LOGODIR."vs_logo.jpg' />\n";
	$html .= "  </td>\n";
	$html .= "  <td width='450'>\n";
	$html .= "   <b>Junák - svaz skautů a skautek ČR, Kapitanát vodních skautů</b><br />\n";
	$html .= "   &nbsp;&nbsp;Senovážné náměstí 977/24, Praha 1, 116 47 <br />\n";
	$html .= "   &nbsp;&nbsp;IČ: 65991753, číslo účtu: 2300183549/2010, Fio banka a.s.<br />\n";
	$html .= "   &nbsp;&nbsp;http://vodni.skauting.cz/ | mustek@hkvs.cz | +420 777 222 141  <br />\n";
	$html .= "  </td>\n";
	$html .= "  <td>\n";
	$html .= "   <strong>číslo:</strong> "./*($mid%2+1)*/$data['numbering']."/PPD".$i."<br />\n";
	$html .= "   <strong>ze dne:</strong> ".$data['date']."<br />\n";
	$html .= "  </td>\n";
	$html .= " </tr>\n";
	$html .= " <tr>\n";
	$html .= "  <td colspan='3' style='margin:5px;'>\n";
	$html .= "   <table style='border:none;width:100%;'>\n";
	$html .= "    <tr>\n";
	$html .= "     <td style='border:none;'>\n";
	$html .= "      <b>Přijato od:</b> ".$data['name']." ".$data['surname'].", ".$data['street'].", ".$data['city'].", ".$data['postal_code']."\n";
	$html .= "     </td>\n";
	$html .= "    </tr>\n";
	$html .= "    <tr>\n";
	$html .= "     <td style='border:none;'>\n";
	$html .= "      <b>Účel platby:</b> účastnický poplatek na sraz VS\n";
	$html .= "     </td>\n";
	$html .= "    </tr>\n";
	$html .= "    <tr>\n";
	$html .= "     <td style='border:none;'>\n";
	$html .= "      <b>Celkem Kč:</b> =".$data['balance'].",- &nbsp;&nbsp;&nbsp;&nbsp; <strong>Slovy Kč:</strong> ".ucfirst(number2word($data['balance'], true))."korun~\n";
	$html .= "     </td>\n";
	$html .= "    </tr>\n";
	$html .= "    <tr>\n";
	$html .= "     <td style='border:none;text-align:right;padding-left:450px;'>\n";
	$html .= "      <strong>Převzal:</strong>..................................................\n";
	$html .= "     </td>\n";
	$html .= "    </tr>\n";
	$html .= "   </table>\n";
	$html .= "  </td>\n";
	$html .= " </tr>\n";
	$html .= "</table>\n";
	$html .= "<br />\n";
	
	$i++;
	}
	
	if($type == "confirm"){
	$html .= "<h2>POTVRZENÍ O PŘIJETÍ ZÁLOHY</h2>\n";
	$html .= "<h4>Potvrzujeme přijetí účastnické zálohy na běžný účet číslo 66655333/5500.</h4>";
	$html .= "<table>\n";
	$html .= " <tr>\n";
	$html .= "  <td width='34' style='text-align:center;'>\n";
	$html .= "   <img width='32' src='".$LOGODIR."vs_logo.jpg' />\n";
	$html .= "  </td>\n";
	$html .= "  <td width='450'>\n";
	$html .= "   <b>Junák - svaz skautů a skautek ČR, Kapitanát vodních skautů</b><br />\n";
	$html .= "   &nbsp;&nbsp;Senovážné náměstí 977/24, Praha 1, 116 47 <br />\n";
	$html .= "   &nbsp;&nbsp;IČ: 65991753, číslo účtu: 2300183549/2010, Fio banka a.s.<br />\n";
	$html .= "   &nbsp;&nbsp;http://vodni.skauting.cz/ | mustek@hkvs.cz | +420 777 222 141  <br />\n";
	$html .= "  </td>\n";
	$html .= "  <td>\n";
	$html .= "   <strong>číslo:</strong> "./*($mid%2+1)*/$data['numbering']."/PZ".$i."<br />\n";
	$html .= "   <strong>ze dne:</strong> ".$data['date']."<br />\n";
	$html .= "  </td>\n";
	$html .= " </tr>\n";
	$html .= " <tr>\n";
	$html .= "  <td colspan='3' style='margin:5px;'>\n";
	$html .= "   <table style='border:none;width:100%;'>\n";
	$html .= "    <tr>\n";
	$html .= "     <td style='border:none;'>\n";
	$html .= "      <b>Přijato od:</b> ".$data['name']." ".$data['surname'].", ".$data['street'].", ".$data['city'].", ".$data['postal_code']."\n";
	$html .= "     </td>\n";
	$html .= "    </tr>\n";
	$html .= "    <tr>\n";
	$html .= "     <td style='border:none;'>\n";
	$html .= "      <b>Účel platby:</b> účastnický poplatek na sraz VS\n";
	$html .= "     </td>\n";
	$html .= "    </tr>\n";
	$html .= "    <tr>\n";
	$html .= "     <td style='border:none;'>\n";
	$html .= "      <b>Celkem Kč:</b> =".$data['bill'].",- &nbsp;&nbsp;&nbsp;&nbsp; <strong>Slovy Kč:</strong> ".ucfirst(number2word($data['bill'], true))."korun~&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;............................................................\n";
	$html .= "     </td>\n";
	$html .= "    </tr>\n";
	$html .= "   </table>\n";
	$html .= "  </td>\n";
	$html .= " </tr>\n";
	$html .= "</table>\n";
	$html .= "<br />\n";
	
	$i++;
	}
	
	if($type == "summary"){

	$mpdf->defaultfooterfontsize = 16;
	$mpdf->defaultfooterfontstyle = B;
	$mpdf->SetHeader($hkvs_header);
	
	if($i % 44 == 0){
		$html .= "<tr>\n";
		$html .= "<td class='header'>Příjmení a Jméno</td>\n";
		$html .= "<td class='header'>Narození</td>\n";
		$html .= "<td class='header'>Adresa</td>\n";
		$html .= "<td class='header'>Jednotka</td>\n";
		$html .= "<td class='header'>Záloha</td>\n";
		$html .= "<td class='header'>Doplatek</td>\n";
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
	$html .= $data['group_num'];
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= $data['bill'];
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= $data['balance'];
	$html .= "</td>\n";
	$html .= "<td class='signature'>\n";
	$html .= "&nbsp;";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	
	$i++;
	}
}

if($type == 'summary') {
	$html .= "</table>\n";
}

$html .= "</body>\n";

//echo $html;

//die();
//$mpdf = new mPDF('utf-8', 'A4');
$mpdf->useOnlyCoreFonts = true;
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetAutoFont(0);

// CSS soubor
//$stylesheet = file_get_contents(ROOT_DIR.'styles/css/print_event.css');

//$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($html, 0);

$name = $filename.".pdf";
// download
$mpdf->Output($name, "D");
?>