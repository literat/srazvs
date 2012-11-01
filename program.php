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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="keywords" content="HKVS, vodní skauti, seascout" />
<meta name="description" content="Hlavní kapitanát vodních skautů" />
<meta name="author" content="HKVS team" />
<meta name="generator" content="SunLight CMS 7.5.1 STABLE0" />
<meta name="robots" content="index, follow" />
<link href='<?php echo $CSSDIR ?>default.css' rel='stylesheet' type='text/css' />
<link href="<?php echo HTTP_DIR; ?>plugins/templates/hkvs2/style/system.css?1" type="text/css" rel="stylesheet" />
<link href="<?php echo HTTP_DIR; ?>plugins/templates/hkvs2/style/layout.css?1" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="<?php echo $CSSDIR ?>datedit.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo $CSSDIR ?>dgxcolormixer_s.css" type="text/css" media="screen,projection,tv" />
<script type="text/javascript">/* <![CDATA[ */var sl_indexroot='./';/* ]]> */</script>
<script type="text/javascript" src="<?php echo HTTP_DIR; ?>remote/jscript.php?1&amp;default"></script>

<link rel="stylesheet" href="<? echo HTTP_DIR; ?>remote/lightbox/style.css?1" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo HTTP_DIR; ?>remote/lightbox/script.js?1"></script>

<link rel="stylesheet" href="<?php echo $CSSDIR ?>colorbox.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script src="<?php echo $AJAXDIR ?>colorbox/jquery.colorbox.js"></script>
<script>
$(document).ready(function(){
	$(".program-link").colorbox({rel:'programDetail', width:"75%", height:"50%", transition:"fade"});
	//Example of preserving a JavaScript event for inline calls.
	$("#click").click(function(){ 
		$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
		return false;
	});
});
</script>

<link rel="alternate" type="application/rss+xml" href="<?php echo HTTP_DIR; ?>remote/rss.php?tp=4&amp;id=-1" title="Nejnovější články" />
<link rel="shortcut icon" href="<?php echo HTTP_DIR; ?>favicon.ico?1" />
<title>Program srazu VS</title>

<!-- GA Tracking Code -->
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-325304-10']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

<?php echo $style; ?>
<!-- PlexIS -->
<title>Program - Sraz vodních skautů</title>
</head>

<body>

<!-- outer -->
<div id="outer-program">

  <!-- page -->
  <div id="page-program">

    <!-- head -->
    <div id="head">
    <a href="<?php echo HTTP_DIR; ?>" title="HKVS - Hlavní kapitanát vodních skautů"><span>HKVS</span></a>
    </div>
    
    <!-- menu -->
    <div id="menu">
    <ul class='menu'>
<li class="act menu-item-100 first"><a href='<?php echo HTTP_DIR; ?>'>Novinky</a></li>
<li class="menu-item-7"><a href='<?php echo HTTP_DIR; ?>najdi-oddil-vs'>Najdi oddíl VS</a></li>
<li class="menu-item-21"><a href='<?php echo HTTP_DIR; ?>o-vodnim-skautingu'>O vodním skautingu</a></li>
<li class="menu-item-6"><a href='<?php echo HTTP_DIR; ?>vs-v-obrazech'>VS v obrazech</a></li>
<li class="menu-item-44 last"><a href='<?php echo HTTP_DIR; ?>english-2'>English</a></li>
</ul>    </div>

    <hr class="hidden" />

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
<a style="text-decoration:none; padding-right:4px;" href="program.pdf.php"><img style='border:none;' align='absbottom' src='styles/layout/icons/small/pdf.png' /></a>
<a href="program.pdf.php">Stáhněte si program srazu ve formátu PDF</a>
<p style="text-align:center; font-size:medium;">Změna programu vyhrazena!</p>

<!-- REGISTRACNI FORMULAR SRAZU -->

<p></p>
<p style="text-align: center; "></p>    </div>
    </div>

    <div class="cleaner"></div>
    

  </div>



</div>

<!-- footer -->
<hr class="hidden" />
<div id="footer">
Qrka &copy;  <a href="www.qrka.cz"></a>  &nbsp;&bull;&nbsp;  <a href='http://sunlight.shira.cz/'>SunLight CMS</a>  &nbsp;&bull;&nbsp;  <a href='<?php echo HTTP_DIR ?>admin/index.php'>administrace</a></div>


<!-- Google Analysis -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-10570895-3");
pageTracker._trackPageview();
} catch(err) {}</script>

</body>
</html>