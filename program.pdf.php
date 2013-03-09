<?php
require_once('inc/define.inc.php');
require_once(LIBS_DIR."Mpdf/mpdf.php");

########################### AKTUALNI SRAZ ##############################

$sql = "SELECT	id,
				place,
				DATE_FORMAT(start_date, '%Y') AS year,
				UNIX_TIMESTAMP(open_reg) AS open_reg,
				UNIX_TIMESTAMP(close_reg) as close_reg
		FROM kk_meetings
		ORDER BY id DESC
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

$mid = $data['id'];
$meetingHeader = $data['place']." ".$data['year'];

$filename = removeDiacritic($data['place'].$data['year']."-program");

$fileaddr = "../tmp/".$filename.".pdf";

################## VLOZENE STYLY ##################################

$style = getCategoryStyle();

$html = "<head>";
$html .= "<style>";
$html .= "
body {
	font-family:Arial,Geneva,Sans-Serif;
	font-size:15px;
	text-align:center;
}

table {
	border-collapse:collapse;
	width:100%;
}

td {
	width:100%;
	text-align:center;
	padding:3px;
}

td.day {
	border:1px solid black;
	background-color:#777777;
	width:80px;
}

td.time {
	border:1px solid black;
	background-color:#cccccc;
	width:100px;
}

";
$html .= "</style>";
$html .= $style;
$html .= "</head>";
$html .= "<body>";

############################## GENEROVANI PROGRAMU ##########################

function getPrograms($id){
	$sql = "SELECT 	progs.name AS name,
					style
			FROM kk_programs AS progs
			LEFT JOIN kk_categories AS cat ON cat.id = progs.category
			WHERE block='".$id."' AND progs.deleted='0'
			LIMIT 10";
	$result = mysql_query($sql);
	$rows = mysql_affected_rows();

	if($rows == 0) $html = "";
	else{
		$html = "<table>";
		$html .= " <tr>";
		while($data = mysql_fetch_assoc($result)){			
			$html .= "<td class='cat-".$data['style']."'>".$data['name']."</td>\n";
		}
		$html .= " </tr>\n";
		$html .= "</table>\n";
	}
	return $html;
}

################################## HTML #####################################

$html .= "<h2>".$meetingHeader."</h2>";
$html .= "<h4>program srazu vodních skautů</h4>";

$days = array("pátek", "sobota", "neděle");

foreach($days as $dayKey => $dayVal){
	$html .= "<table>\n";
	$html .= " <tr>\n";
	$html .= "  <td class='day' colspan='2' >".$dayVal."</td>\n";
	$html .= " </tr>\n";

	$sql = "SELECT 	blocks.id AS id,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`,
					blocks.name AS name,
					program,
					display_progs,
					style
			FROM kk_blocks AS blocks
			LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
			WHERE blocks.deleted = '0' AND day='".$dayVal."' AND meeting='".$mid."'
			ORDER BY `from` ASC";

	$result = mysql_query($sql);
	$rows = mysql_affected_rows();

	if($rows == 0){
		$html .= "<td class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</td>\n";
	}
	else{
		while($data = mysql_fetch_assoc($result)){
			$html .= "<tr>\n";
			$html .= "<td class='time'>".$data['from']." - ".$data['to']."</td>";
			if(($data['program'] == 1) && ($data['display_progs'] == 1)){
				$html .= "<td class='cat-".$data['style']."' style='text-align:center;border:1px solid black;'>";
				$html .= "<div>".$data['name']."</div>";
				$html .= "".getPrograms($data['id'])."</td>";
			}
			else{
				$html .= "<td class='cat-".$data['style']."' style='text-align:center;border:1px solid black;'>".$data['name']."</td>\n";
				$html .= "</tr>\n";
			}
		}
	}
	$html .= "</table>\n";
}

################## GENEROVANI STRANKY #############################

$html .= "<p style='text-align:center; font-size:medium;'>Změna programu vyhrazena!</p>";
$html .= "</body>";

//echo $html;

$mpdf = new mPDF('utf-8', 'A4');
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