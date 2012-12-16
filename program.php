<?php
require_once('inc/define.inc.php');

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

############################## GENEROVANI PROGRAMU ##########################

function getPrograms($id){
	$sql = "SELECT 	progs.id AS id,
					progs.name AS name,
					style
			FROM kk_programs AS progs
			LEFT JOIN kk_categories AS cat ON cat.id = progs.category
			WHERE block='".$id."' AND progs.deleted='0'
			LIMIT 10";
	$result = mysql_query($sql);
	$rows = mysql_affected_rows();

	if($rows == 0) $html = "";
	else{
		$html = "<table>\n";
		$html .= " <tr>\n";
		while($data = mysql_fetch_assoc($result)){			
			$html .= "<td class='cat-".$data['style']."' style='text-align:center;'>\n";
			$html .= "<a class='program-link' rel='programDetail' href='detail.php?id=".$data['id']."&type=program' title='".$data['name']."' rel='programDetail'>".$data['name']."</a>\n";
			$html .= "</td>\n";
		}
		$html .= " </tr>\n";
		$html .= "</table>\n";
	}
	return $html;
}

################################## HTML #####################################

$days = array("pátek", "sobota", "neděle");
$html = "";

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
			$html .= "<td class='time'>".$data['from']." - ".$data['to']."</td>\n";
			if(($data['program'] == 1) && ($data['display_progs'] == 1)){ 
				$html .= "<td class='cat-".$data['style']."' class='daytime'>\n";
			 	$html .= "<div>\n";
			  	$html .= "<a class='program-link rel='programDetail' href='detail.php?id=".$data['id']."&type=block' title='".$data['name']."' rel='programDetail'>".$data['name']."</a>\n";
			 	$html .= "</div>\n";
				$html .= getPrograms($data['id']);
				$html .= "</td>\n";
			}
			else {
				$html .= "<td class='cat-".$data['style']."'>";
				$html .= "<a class='program-link rel='programDetail' href='detail.php?id=".$data['id']."&type=block' title='".$data['name']."' rel='programDetail'>".$data['name']."</a>\n";
				$html .= "</td>\n";
			}
			$html .= "</tr>\n";
		}
	}
	$html .= "</table>\n";
}

################## VLOZENE STYLY ##################################

$style = getCategoryStyle();
$style .= "<style>";
$style .= "
table {
	border-collapse:separate;
	width:100%;
}

td {
	.width:100%;
	text-align:center;
	padding:0px;
}

td.day {
	border:1px solid black;
	background-color:#777777;
	width:80px;
}

td.time {
	background-color:#cccccc;
	width:80px;
}

#footer {
    background: url('../plugins/templates/hkvs2/images/outer-bottom-program.png') no-repeat scroll left top transparent;
}

";
$style .= "</style>";

################## GENEROVANI STRANKY #############################
?>

<?php include_once($INCDIR."vodni_header.inc.php"); ?>

    <!-- content -->
    <div id="content-program">
    <div id="content-pad-program">
	<h1>Program srazu vodních skautů</h1>
	<br />
	<h2><?php echo $meetingHeader; ?></h2>
	<br />
	<p>info: Po rozkliknutí programu se Vám zobrazí jeho detail.</p>

<!-- REGISTRACNI FORMULAR SRAZU -->

<?php echo $html; ?>

	<br />
	<a style="text-decoration:none; padding-right:4px;" href="program.pdf.php">
    	<img style='border:none;' align='absbottom' src='styles/layout/icons/small/pdf.png' />
    </a>
	<a href="program.pdf.php">Stáhněte si program srazu ve formátu PDF</a>
	<p style="text-align:center; font-size:medium;">Změna programu vyhrazena!</p>

<!-- REGISTRACNI FORMULAR SRAZU -->

		<p></p>
		<p style="text-align: center; "></p>
	 </div>
    </div>
    <div class="cleaner"></div>
  </div>
</div>

<?php include_once($INCDIR."vodni_footer.inc.php"); ?>