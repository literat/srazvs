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
/*
if($mid = requested("mid","")){
	$_SESSION['meetingID'] = $mid;
} else {
	$mid = $_SESSION['meetingID'];
}
*/

////otevirani a uzavirani prihlasovani
if(($data['open_reg'] < time()) || DEBUG === TRUE){
	$display_program = TRUE;
} else {
	$display_program = FALSE;
}

$Container = new Container($GLOBALS['cfg'], $mid);
$MeetingHandler = $Container->createMeeting();

################## VLOZENE STYLY ##################################

$style = "<style>";
$style .= Category::getStyles();
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

$page_title = "Program srazu VS";

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

	<!-- PROGRAM SRAZU -->

<?php if($display_program){ ?>

	<?php echo $MeetingHandler->renderPublicProgramOverview(); ?>

	<br />
	<a style="text-decoration:none; padding-right:4px;" href="program.pdf.php">
    	<img style='border:none;' align='absbottom' src='styles/layout/icons/small/pdf.png' />
    </a>
	<a href="program.pdf.php">Stáhněte si program srazu ve formátu PDF</a>
    <br />
    <a style="text-decoration:none; padding-right:4px;" href="exports/?program-details">
    	<img style='border:none;' align='absbottom' src='styles/layout/icons/small/pdf.png' />
    </a>
	<a href="exports/?program-details">Stáhněte si detaily programů srazu ve formátu PDF</a>
	<p style="text-align:center; font-size:medium;">Změna programu vyhrazena!</p>

	<!-- PROGRAM SRAZU -->

<?php } else { ?>
<div>Registrace není otevřena, sraz se stále ještě připravuje!</div>
<?php } ?>

		<p></p>
		<p style="text-align: center; "></p>
	 </div>
    </div>
    <div class="cleaner"></div>
  </div>
</div>

<?php include_once($INCDIR."vodni_footer.inc.php"); ?>