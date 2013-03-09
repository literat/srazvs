<?php
require_once('../inc/define.inc.php');
require_once(LIBS_DIR."Mpdf/mpdf.php");

########################### AKTUALNI SRAZ ##############################

$mid = $_SESSION['meetingID'];
//$mid = 2;

$Container = new Container($GLOBALS['cfg'], $mid);
$ExportHandler = $Container->createExport();

$ExportHandler->printMealTicket();
exit();

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

################################ FUNKCE ###################################

################################# JIDLO ################################

function getMeals($vid)
{
	$meals = "<tr>";
	$meals .= " <td class='progPart'>";

	$mealSql = "SELECT 	*
				FROM kk_meals
				WHERE visitor='".$vid."'
				";

	$mealResult = mysql_query($mealSql);
	$mealRows = mysql_affected_rows();

	if($mealRows == 0){
		$meals .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
	}
	else{	
		while($mealData = mysql_fetch_assoc($mealResult)){
			$meals .= "<div class='block'>".$mealData['fry_dinner'].", ".$mealData['sat_breakfast']." - ".$mealData['sat_lunch']." : ".$mealData['sat_dinner']."</div>\n";

		}
	}

	$meals .= "</td>";
	$meals .= "</tr>";

	return $meals;
}
#############################################################################

//$filename = noDiakritika($data['place'].$data['year']."-program");
$filename = "vlastni_stravenky";
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
	text-align:center;
	border-right:1px dotted black;
	vertical-align:middle;
}

img {height:32px;}

.cutLine {border: 2px dotted black; vertical-align:top;}

.nick {font-size:17px;color:navy;}

.meal {font-weight:bold;}

td.name {
	font-size:14px;
	width:220px;
	padding-right:5px;
}

.name, .group_name, .meeting {text-align:right;}
.program, .nick {font-weight:bold;}
";
$html .= "</style>\n";
$html .= "</head>\n";

############################ GENEROVANI STRANKY #############################

$html .= "<body>\n";

$i = 0;
while($data = mysql_fetch_assoc($result)){
	if($data['fry_dinner'] == "ano") $imgUrl1 = $ICODIR."dinner.png";
	else $imgUrl1 = $ICODIR."nomeal.png";
	if($data['sat_breakfast'] == "ano") $imgUrl2 = $ICODIR."breakfast.png";
	else $imgUrl2 = $ICODIR."nomeal.png";
	if($data['sat_lunch'] == "ano") $imgUrl3 = $ICODIR."lunch.png";
	else $imgUrl3 = $ICODIR."nomeal.png";
	if($data['sat_dinner'] == "ano") $imgUrl4 = $ICODIR."dinner.png";
	else $imgUrl4 = $ICODIR."nomeal.png";
	if($data['sun_breakfast'] == "ano") $imgUrl5 = $ICODIR."breakfast.png";
	else $imgUrl5 = $ICODIR."nomeal.png";
	if($data['sun_lunch'] == "ano") $imgUrl6 = $ICODIR."lunch.png";
	else $imgUrl6 = $ICODIR."nomeal.png";

	$table = "   <table class='cutLine'>\n";
	$table .= "    <tr>\n";
	$table .= "		<td><div>Pátek</div><div><img src='".$imgUrl1."' /></div><div class='meal'>Večeře</div></td>\n";
	$table .= "		<td><div>Sobota</div><div><img src='".$imgUrl2."' /></div><div class='meal'>Snídaně</div></td>\n";
	$table .= "		<td><div>Sobota</div><div><img src='".$imgUrl3."' /></div><div class='meal'>Oběd</div></td>\n";
	$table .= "		<td><div>Sobota</div><div><img src='".$imgUrl4."' /></div><div class='meal'>Večeře</div></td>\n";
	$table .= "		<td><div>Neděle</div><div><img src='".$imgUrl5."' /></div><div class='meal'>Snídaně</div></td>\n";
	$table .= "		<td><div>Neděle</div><div><img src='".$imgUrl6."' /></div><div class='meal'>Oběd</div></td>\n";
	$table .= "		<td class='name'><span class='nick'>".$data['nick']."</span> ".$data['name']." ".$data['surname']."</td>\n";
	$table .= "    </tr>\n";
	$table .= "   </table>\n";
	
	$html .= $table;
	$i++;
}

$html .= "</body>\n";

//echo $html;

//die();
$mpdf = new mPDF('utf-8', 'A4', 0, '', 5, 5, 9, 7);
$mpdf->useOnlyCoreFonts = true;
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetAutoFont(0);

//$mpdf->SetWatermarkImage($LOGODIR.'watermark.jpg', 0.1, '');
//$mpdf->showWatermarkImage = true;

// CSS soubor
//$stylesheet = file_get_contents(ROOT_DIR.'styles/css/print_event.css');

//$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($html, 0);

$name = $filename.".pdf";
// download
$mpdf->Output($name, "D");
?>