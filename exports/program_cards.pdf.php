<?php
require_once('../inc/define.inc.php');
require_once($LIBSDIR."Mpdf/mpdf.php");

########################### AKTUALNI SRAZ ##############################

$mid = $_SESSION['meetingID'];
//$mid = 2;

$sql = "SELECT	vis.id AS id,
				name,
				surname,
				nick,
				DATE_FORMAT(birthday, '%Y-%m-%d') AS birthday,
				street,
				city,
				postal_code,
				province,
				province_name,
				group_num,
				group_name,
				troop_name,
				comment,
				arrival,
				departure,
				question,
				fry_dinner,
				sat_breakfast,
				sat_lunch,
				sat_dinner,
				sun_breakfast,
				sun_lunch,
				bill,
				place,
				DATE_FORMAT(start_date, '%d. -') AS start_date,
				DATE_FORMAT(end_date, '%d. %m. %Y') AS end_date
		FROM kk_visitors AS vis
		LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
		LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
		LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
		WHERE meeting='".$mid."' AND vis.deleted='0'
		";
$result = mysql_query($sql);
//$data = mysql_fetch_assoc($result);

////ziskani zvolenych programu
$blockSql = "SELECT 	id
			 FROM kk_blocks
			 WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
$blockResult = mysql_query($blockSql);
while($blockData = mysql_fetch_assoc($blockResult)){
	$$blockData['id'] = requested($blockData['id'],0);
	//echo $blockData['id'].":".$$blockData['id']."|";
}

################################ FUNKCE ###################################

function getPrograms($id, $vid){
	$sql = "SELECT 	*
			FROM kk_programs
			WHERE block='".$id."' AND deleted='0'
			LIMIT 10";
	$result = mysql_query($sql);
	$rows = mysql_affected_rows();

	if($rows == 0){
		$html = "";
	}
	else{

		$html = "<div class='program'>\n";
	
		while($data = mysql_fetch_assoc($result)){			
			$programSql = "SELECT * FROM `kk_visitor-program` WHERE program = '".$data['id']."' AND visitor = '".$vid."'";
			$programResult = mysql_query($programSql);
			$rows = mysql_affected_rows();
			if($rows == 1) $html .= $data['name'];			
		}
		$html .= "</div>\n";
	}
	return $html;
}

################################# PROGRAMY ################################

function getBlocks($vid)
{
$programs = "<tr>";
$programs .= " <td class='progPart'>";

$progSql = "SELECT 	id,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`,
					name,
					program
			FROM kk_blocks
			WHERE deleted = '0' AND program='1' AND meeting='".$_SESSION['meetingID']."'
			ORDER BY `day` ASC";

$progResult = mysql_query($progSql);
$progRows = mysql_affected_rows();

if($progRows == 0){
	$programs .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
}
else{	
	while($progData = mysql_fetch_assoc($progResult)){
		// zbaveni se predsnemovni diskuse
		if($progData['id'] == 63) $programs .= "";
		else {
			$programs .= "<div class='block'>".$progData['day'].", ".$progData['from']." - ".$progData['to']." : ".$progData['name']."</div>\n";
		
			if($progData['program'] == 1) $programs .= "<div>".getPrograms($progData['id'], $vid)."</div>";
		}
	}
}

$programs .= "</td>";
$programs .= "</tr>";

return $programs;
}
#############################################################################

//$filename = noDiakritika($data['place'].$data['year']."-program");
$filename = "vlastni_programy";
$fileaddr = "../tmp/".$filename.".pdf";

###################################### CSS ##################################

$html = "<head>\n";
$html .= "<style>\n";
$html .= "
body {
	font-family:Verdana,Arial,Geneva,Sans-Serif;
	font-size:10px;
	text-align:left;
}

table {
	border-collapse:collapse;
	width:100%;
}

td {
	text-align:left;
	width:375px;
	_border:1px solid black;
}

.cutLine {border: 2px dotted black; vertical-align:top;}

.nick {font-size:17px;color:navy;}
.name {font-size:14px;}
.progPart {height:207px;padding-left:5px;}

.program{font-size:13px;color:black;}

.meeting{font-size:13px; color:#4169e1;}
.block, .group_name {color:grey;}
.name, .group_name, .meeting {text-align:right;}
.program, .nick {font-weight:bold;}
";
$html .= "</style>\n";
$html .= "</head>\n";

############################ GENEROVANI STRANKY #############################

$html .= "<body>\n";

$i = 0;
while($data = mysql_fetch_assoc($result)){
	$table = "   <table>\n";
	$table .= "    <tr>\n";
	$table .= "     <td class='name'>";
	$table .= "      <span class='nick'>".$data['nick']."</span> ".$data['name']." ".$data['surname']."";
	//$table .= "      <div class='group_name'>".$data['group_name']."</div>\n";
	$table .= "     </td>\n";
	$table .= "    </tr>\n";
	$table .= "    <tr>\n";
	
	$table .= "    </tr>\n";
	$table .= getBlocks($data['id']);
	$table .= "    <tr>\n";
	$table .= "     <td class='meeting'>\n";
	$table .= "      SRAZ VS - ".$data['place']." ".$data['start_date']." ".$data['end_date']."\n";
	$table .= "     </td>\n";
	$table .= "    </tr>\n";
	$table .= "   </table>\n";
	
	if($i%2 == 0){
		$html .= "<table>\n";
		$html .= " <tr>\n";
		$html .= "  <td class='cutLine'>\n";
		$html .= $table;
		$html .= "  </td>\n";
	}
	else {
		$html .= "  <td class='cutLine'>\n";
		$html .= $table;
		$html .= "  </td>\n";
		$html .= " </tr>\n";
		$html .= "</table>\n";	
	}
	$i++;
}

if($i%2 != 0){
	$html .= "  <td class='cutLine'>\n";
	$html .= "  </td>\n";
	$html .= " </tr>\n";
	$html .= "</table>\n";	
}

$html .= "</body>\n";

//echo $html;

//die();
$mpdf = new mPDF('utf-8', 'A4', 0, '', 5, 5, 9, 7);
$mpdf->useOnlyCoreFonts = true;
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetAutoFont(0);

$mpdf->SetWatermarkImage($LOGODIR.'watermark.jpg', 0.1, '');
$mpdf->showWatermarkImage = true;

// CSS soubor
//$stylesheet = file_get_contents(ROOT_DIR.'styles/css/print_event.css');

//$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($html, 0);

$name = $filename.".pdf";
// download
$mpdf->Output($name, "D");
?>