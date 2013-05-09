<?php

//header
require_once('../inc/define.inc.php');

########################### AKTUALNI SRAZ ##############################

$cms = requested("cms","");
$hash = requested("formkey","");
$type = requested("type","");
$mid = (($hash - 39147) / 116)%10;
$pid = floor((($hash - 39147) / 116)/10);

$sql = "SELECT	id,
				place,
				DATE_FORMAT(start_date, '%Y') AS year,
				UNIX_TIMESTAMP(close_reg) as close_reg
		FROM kk_meetings
		WHERE id='".$mid."'
		ORDER BY id DESC
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

// nadpis stranky
$meetingHeader = $data['place']." ".$data['year'];

// otevirani a uzavirani prihlasovani
if(time() < $data['close_reg']) $disabled = "";
else $disabled = "disabled";

//// nacitam data
$sql = "SELECT	*
		FROM kk_".$type."s
		WHERE id='".$pid."' AND deleted='0'
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

$error = requested("error","");
$name = requested("name",$data['name']);
$description = requested("description",$data['description']);
$material = requested("material",$data['material']);
$tutor = requested("tutor",$data['tutor']);
$email = requested("email",$data['email']);
$capacity = requested("capacity",$data['capacity']);

######################### KONTROLA ########################################

////inicializace promenych
$error_name = "";
$error_description = "";
$error_material = "";
$error_tutor = "";
$error_email = "";

######################## ZPRACOVANI ####################################

//prihlasovaci udaje
if($cms == "update"){
	$sql = "UPDATE `kk_".$type."s`
			SET `name` = '".$name."', 
				`description` = '".$description."',
				`material` = '".$material."',
				`tutor` = '".$tutor."',  
				`email` = '".$email."',
				`capacity` = '".$capacity."'
			WHERE id = '".$pid."'
			LIMIT 1";
	$result = mysql_query($sql);
	
	if(!$result){
		$error = "error";
	}
	else {$error = "ok";
		redirect("setcontent.php?error=".$error."&type=".$type."&formkey=".$hash."");
	}
};

################## VLOZENE STYLY ##################################

$style .= "<style>";
$style .= "
#footer {
    background: url('../../plugins/templates/hkvs2/images/outer-bottom-program.png') no-repeat scroll left top transparent;
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
<link href='<?php echo CSS_DIR ?>default.css' rel='stylesheet' type='text/css' />
<link href="<?php echo HTTP_DIR; ?>plugins/templates/hkvs2/style/system.css?1" type="text/css" rel="stylesheet" />
<link href="<?php echo HTTP_DIR; ?>plugins/templates/hkvs2/style/layout.css?1" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="<?php echo CSS_DIR ?>datedit.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo CSS_DIR ?>dgxcolormixer_s.css" type="text/css" media="screen,projection,tv" />
<script type="text/javascript">/* <![CDATA[ */var sl_indexroot='./';/* ]]> */</script>
<script type="text/javascript" src="<?php echo HTTP_DIR; ?>remote/jscript.php?1&amp;default"></script>

<link rel="stylesheet" href="<?php echo HTTP_DIR; ?>remote/lightbox/style.css?1" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo HTTP_DIR; ?>remote/lightbox/script.js?1"></script>
<link rel="alternate" type="application/rss+xml" href="<?php echo HTTP_DIR; ?>remote/rss.php?tp=4&amp;id=-1" title="Nejnovější články" />
<link rel="shortcut icon" href="<?php echo HTTP_DIR; ?>favicon.ico?1" />
<title>Registrace programů pro lektory</title>

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
				<li class="act menu-item-100 first">
					<a href='<?php echo HTTP_DIR; ?><?php echo HTTP_DIR; ?>'>Novinky</a>
				</li>
				<li class="menu-item-7">
					<a href='<?php echo HTTP_DIR; ?><?php echo HTTP_DIR; ?>najdi-oddil-vs'>Najdi oddíl VS</a>
				</li>
				<li class="menu-item-21">
					<a href='<?php echo HTTP_DIR; ?><?php echo HTTP_DIR; ?>o-vodnim-skautingu'>O vodním skautingu</a>
				</li>
				<li class="menu-item-6">
					<a href='<?php echo HTTP_DIR; ?><?php echo HTTP_DIR; ?>vs-v-obrazech'>VS v obrazech</a>
				</li>
				<li class="menu-item-44 last">
					<a href='<?php echo HTTP_DIR; ?><?php echo HTTP_DIR; ?>english-2'>English</a>
				</li>
			</ul>
		</div>

		<hr class="hidden" />

    <!-- content -->
    <div id="content-program">
    <div id="content-pad-program">
<h1>Anotace programu na srazy VS</h1>
<br />
<h2><?php echo $meetingHeader; ?></h2>
<br />
<?php printError($error); ?>

<!-- REGISTRACNI FORMULAR SRAZU -->

<form action='setcontent.php' method='post'>

<div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div> 

<div style="text-align:center;"><span class="required">*</span>Takto označené položky musí být vyplněné!</div>

<table class='form'>
 <tr>
  <td class='label'><label><span class="required">*</span>Název:</label></td>
  <td><input type='text' name='name' size='30' value='<?php echo $name; ?>' /><?php printError($error_name); ?></td>
 </tr>
 <tr>
  <td colspan="2">Doplň, prosím, popis Tvého programu (bude se zobrazovat účastníkům na webu při výběru programu).
  </td>
 </tr>
 <tr>
  <td class='label'><label>Popis:</label></td>
  <td><textarea name='description' rows='10' cols="70"><?php echo $description; ?></textarea><?php printError($error_description); ?></td>
 </tr>
 <tr>
  <td colspan="2">Doplň, prosím, vybavení, které budeš potřebovat na Tvůj program a které máme zajistit.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Materiál:</label></td>
  <td><textarea name='material' rows='2' cols="70"><?php echo $material; ?></textarea><?php printError($error_material); ?></td>
 </tr>
 <tr>
  <td class='label'><label>Lektor:</label></td>
  <td><input type='text' name='tutor' size='30' value='<?php echo $tutor; ?>' /><?php printError($error_tutor); ?></td>
 </tr>
 <tr>
  <td class='label'><label>E-mail:</label></td>
  <td><input type='text' name='email' size='30' value='<?php echo $email; ?>' /><?php printError($error_email); ?></td>
 </tr>
 <tr>
  <td colspan="2">Doplň, prosím, maximální počet účastníků, kteří se mohou Tvého programu zúčastnit.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Kapacita:</label></td>
  <td><input type='text' name='capacity' size='10' value='<?php echo $capacity; ?>' /> (omezeno na 255)</td>
 </tr>
</table>

 <input type='hidden' name='cms' value='update'>
 <input type='hidden' name='formkey' value='<?php echo $hash; ?>'>
 <input type='hidden' name='type' value='<?php echo $type; ?>'>
 
 <div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div>
 
</form>

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
	} catch(err) {}
</script>

	</body>
</html>