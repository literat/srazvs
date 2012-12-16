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
		while($data = mysql_fetch_assoc($result)){			
			$html .= $data['name'].",\n";
		}
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
$filename = "badge_programy";
$fileaddr = "../tmp/".$filename.".pdf";

###################################### CSS ##################################

$html = "<head>\n";
$html .= "<style>\n";
$html .= "
body {
	font-family:Verdana,Arial,Geneva,Sans-Serif;
	font-size:9px;
	text-align:left;
}

table {
	border-collapse:collapse;
	width:100%;
}

td {
	text-align:left;
	_width:375px;
	_border:1px solid black;
}

.cutLine {border: 2px dotted black; vertical-align:top;}

.day {font-size:12px;font-weight:bold;margin-top:15px;}
.time {color:grey;}
.meal {font-size:10px;font-weight:bold;}

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

$days = array("PÁTEK", "SOBOTA", "NEDĚLE");

$i = 0;
while($data = mysql_fetch_assoc($result)){
	$table = "   <table>\n";
	$table .= "    <tr>\n";
	$table .= "     <td>\n";
	
	$table .= "    <table>\n";

	foreach($days as $dayKey => $dayVal){
		$table .= " <tr>\n";
		$table .= "  <td class='day'>".$dayVal."</td>\n";
		$table .= " </tr>\n";
	
		$sqlF = "SELECT 	blocks.id AS id,
						day,
						DATE_FORMAT(`from`, '%H:%i') AS `from`,
						DATE_FORMAT(`to`, '%H:%i') AS `to`,
						blocks.name AS name,
						program,
						display_progs,
						style,
						cat.id AS category
				FROM kk_blocks AS blocks
				LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
				/* 18 - pauzy */
				WHERE blocks.deleted = '0' AND day='".$dayVal."' AND meeting='".$mid."' AND category != '18'
				ORDER BY `from` ASC";
	
		$resultF = mysql_query($sqlF);
		$rowsF = mysql_affected_rows();
	
		if($rowsF == 0){
			$table .= "<td class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</td>\n";
		}
		else{
			while($dataF = mysql_fetch_assoc($resultF)){
				$table .= "<tr>\n";
				if($dataF['category'] == 7) $table .= "<td><span class='meal'>".$dataF['from']." - ".$dataF['to']." ";
				else $table .= "<td><span class='time'>".$dataF['from']." - ".$dataF['to']."</span> ";
				
				// kdyz je programovy blok, tak zobrazim jenom jeho obsah
				if($dataF['program']) {
					$table .= "".getPrograms($dataF['id'])."</td>\n";
				}
				else {
					if($dataF['category'] == 7) $table .= "".$dataF['name']."</span></td>\n";
					else $table .= "".$dataF['name']."</td>\n";
				}
				
				$table .= "</tr>\n";
				if($dayVal == "SOBOTA" && $dataF['name'] == "Oběd"){
					$table .= "    </table>\n";
					$table .= "   </td>\n";
					$table .= "   <td>\n";
					$table .= "    <table>\n";
				}
			}
		}
	}
	
	$table .= "    </table>\n";
    
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
	if($i == 8) break;
}

$html .= "</body>\n";

//echo $html;

//die();
$mpdf = new mPDF('utf-8', 'A4', 0, '', 5, 5, 9, 7);
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