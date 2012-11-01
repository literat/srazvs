<?php
require_once('../inc/define.inc.php');
require_once("../inc/mpdf/mpdf.php");

########################### AKTUALNI SRAZ ##############################

$mid = $_SESSION['meetingID'];
//$mid = 2;

$sql = "SELECT	vis.id AS id,
				nick
		FROM kk_visitors AS vis
		WHERE meeting='".$mid."' AND vis.deleted='0'
		";
$result = mysql_query($sql);

#############################################################################

//$filename = noDiakritika($data['place'].$data['year']."-program");
$filename = "jmenovky";
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

h1 {font-size:70px;color:#555;}

.cutLine {border: 2px dotted black; vertical-align:top;}
.nick {height:206px;vertical-align:middle;text-align:center;}
";
$html .= "</style>\n";
$html .= "</head>\n";

############################ GENEROVANI STRANKY #############################

$data = array(
"Martina",
"Čerw",
"Kuklič",
"Domča",
"Dana",
"Doník",
"Fialík",
"Veverka",
"Digi",
"Ježour",
"Jirja",
"Lobo",
"Hotanka",
"Bidlo",
"Mang",
"Lucie",
"Hanka",
"Evík",
"Kaskadér",
"Saddám",
"Kemal",
"Kingkong",
"Roman",
"Pompo",
"Dändy",
"Digger",
"Mozek",
"Zdenda",
"Mikroskop",
"Pedro",
"Myšák",
"Lukáš",
"Simča",
"Olda",
"Lucka",
"Šipka",
"Bobr",
"Vojta",
"Jouda",
"Iffííí",
"Dan",
"Cedník",
"Katka",
"Maruška",
"Vendy",
"Pavel",
"Krápník",
"Šťoura",
"Vulkán",
"Dína",
"Beny"
);
//var_dump($data);
//die();
$html .= "<body>\n";

$i = 0;
for($i = 0; $i < 60; $i++){
//while($data = mysql_fetch_assoc($result)){
	$table = "   <table>\n";
	$table .= "    <tr>\n";
	$table .= "     <td class='nick'>";
	/*$table .= "      <h1>".$data['nick']."</h1>";*/
	$table .= "      <h1>".$data[$i]."</h1>";
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
	//$i++;
}

$html .= "</body>\n";

//echo $html;

//die();
$mpdf = new mPDF('utf-8', 'A4', 0, '', 15, 15, 6, 7);
$mpdf->useOnlyCoreFonts = true;
$mpdf->SetDisplayMode('fullpage');
$mpdf->SetAutoFont(0);

$mpdf->SetWatermarkImage($LOGODIR.'watermark-waves.jpg', 0.1, '');
$mpdf->showWatermarkImage = true;

// CSS soubor
//$stylesheet = file_get_contents(ROOT_DIR.'styles/css/print_event.css');

//$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($html, 0);

$name = $filename.".pdf";
// download
$mpdf->Output($name, "D");
?>