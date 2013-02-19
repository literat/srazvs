<?php

//header
require_once('../inc/define.inc.php');

########################### AKTUALNI SRAZ ##############################

$sql = "SELECT	id,
				place,
				DATE_FORMAT(start_date, '%Y') AS year,
				UNIX_TIMESTAMP(open_reg) AS open_reg,
				UNIX_TIMESTAMP(close_reg) as close_reg
		FROM kk_meetings
		/*WHERE id='2'*/
		ORDER BY id DESC
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

$mid = $data['id'];

$meetingHeader = $data['place']." ".$data['year'];

////otevirani a uzavirani prihlasovani
if(($data['open_reg'] < time()) && (time() < $data['close_reg'])) $disabled = "";
else $disabled = "disabled";

if(defined('DEBUG') && DEBUG === TRUE){
	$mid = 1;
	$disabled = "";	
}

################################ FUNKCE ###################################

function getPrograms($id, $disabled){
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
		$html = "<div>\n";
		$html .= "<input ".$disabled." checked type='radio' name='".$id."' value='0' /> Nebudu přítomen <br />\n";
		while($data = mysql_fetch_assoc($result)){
			//// resim kapacitu programu a jeho naplneni navstevniky
			$fullProgramSql = " SELECT COUNT(visitor) AS visitors FROM `kk_visitor-program` AS visprog
								LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
								WHERE program = '".$data['id']."' AND vis.deleted = '0'";
			$fullProgramResult = mysql_query($fullProgramSql);
			$fullProgramData = mysql_fetch_assoc($fullProgramResult);
		
			// nezobrazeni programu v registraci, v adminu zaskrtavatko u programu
			if($data['display_in_reg'] == 0) $notDisplayedProg = "style='display:none;'";
			else $notDisplayedProg = "";
		
			if($fullProgramData['visitors'] >= $data['capacity']){
				$html .= "<div ".$notDisplayedProg."><input disabled type='radio' name='".$id."' value='".$data['id']."' />\n";
				$fullProgramInfo = " (NELZE ZAPSAT - kapacita programu je již naplněna!)";
			}
			else {
				$html .= "<div ".$notDisplayedProg."><input ".$disabled." type='radio' name='".$id."' value='".$data['id']."' /> \n";
				$fullProgramInfo = "";
			}
			$html .= "<a class='programLink' rel='programDetail' href='".HTTP_DIR."srazvs/detail.php?id=".$data['id']."&type=program' title='".$data['name']."'>".$data['name']."</a>\n";
			$html .= $fullProgramInfo;
			$html .= "</div>\n";
		}
		$html .= "</div>\n";
	}
	return $html;
}

################################# PROGRAMY ################################

$programs = "  <div style='border-bottom:1px solid black;text-align:right;'>výběr programů</div>";
$programs .= "  <p>info: Rozkliknutím programu zobrazíte jeho detail.</p>";

$progSql = "SELECT 	id,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`,
					name,
					program
			FROM kk_blocks
			WHERE deleted = '0' AND program='1' AND meeting='".$mid."'
			ORDER BY `day`, `from` ASC";

$progResult = mysql_query($progSql);
$progRows = mysql_affected_rows();

if($progRows == 0){
	$programs .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
}
else{
	//// prasarnicka kvuli programu raftu - resim obsazenost dohromady u dvou polozek
	//$raftCountSql = "SELECT COUNT(visitor) AS raft FROM `kk_visitor-program` WHERE program='56|57'";
	//$raftCountResult = mysql_query($raftCountSql);
	//$raftCountData = mysql_fetch_assoc($raftCountResult);
	
	while($progData = mysql_fetch_assoc($progResult)){
		//nemoznost volit predsnemovni dikusi
		if($progData['id'] == 63) $notDisplayed = "style='display:none;'";
		//obsazenost raftu
		//elseif($raftCountData['raft'] >= 25){
		//	if($progData['id'] == 86) $notDisplayed = "style='display:none;'";
		//	else $notDisplayed = "";
		//}
		else $notDisplayed = "";
		$programs .= "<div ".$notDisplayed.">".$progData['day'].", ".$progData['from']." - ".$progData['to']." : ".$progData['name']."</div>\n";
		if($progData['program'] == 1) $programs .= "<div ".$notDisplayed.">".getPrograms($progData['id'], $disabled)."</div>";
		$programs .= "<br />";
	}
}

######################### KONTROLA ########################################

////inicializace promenych
$error = "";
$error_name = "";
$error_surname = "";
$error_postal_code = "";
$error_email = "";
$error_street = "";
$error_city = "";
$error_group_num = "";
$error_group_name = "";
$error_birthday = "";
$error_bill = "";

//$mid = requested("mid","");
$cms = requested("cms","");
//$error = requested("error","");

////ziskani zvolenych programu
$blockSql = "SELECT 	id
			 FROM kk_blocks
			 WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
$blockResult = mysql_query($blockSql);
while($blockData = mysql_fetch_assoc($blockResult)){
	$$blockData['id'] = requested($blockData['id'],0);
	//echo $blockData['id'].":".$$blockData['id']."|";
}

$name = requested("name","");
$surname = requested("surname","");
$nick = requested("nick","");
$birthday = requested("birthday","");
$street = requested("street","");
$city = requested("city","");
$postal_code = requested("postal_code","");
$province = requested("province","");
$group_num = requested("group_num","");
$group_name = requested("group_name","");
$troop_name = requested("troop_name","");
$bill = requested("bill",0);
$email = requested("email","");
$comment = requested("comment","");
$arrival = requested("arrival","");
$departure = requested("departure","");
$question = requested("question","");

$fry_dinner = requested("fry_dinner","");
$sat_breakfast = requested("sat_breakfast","");
$sat_lunch = requested("sat_lunch","");
$sat_dinner = requested("sat_dinner","");
$sun_breakfast = requested("sun_breakfast","");
$sun_lunch = requested("sun_lunch","");

######################## ZPRACOVANI ####################################

if($cms == "create"){
	$birthday = cleardate2DB($birthday, "Y-m-d");
	
	$error = "";
	//kontrola PSC	
	if(!isZipCode($postal_code)){
		$error = "error";
		$error_postal_code = "zip_code";
	}
	
	//kontrola e-mailu
	//if(!isEmail($email)){
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = "error";
		$error_email = "email";
	}
	
	//kontrola e-mailu
	if(!isGroupNumber($group_num)){
		$error = "error";
		$error_group_num = "group_num";
	}
	
	if(isEmpty($name)) $error_name = "empty";
	if(isEmpty($surname)) $error_surname = "empty";
	if(isEmpty($birthday)) $error_birthday = "empty";
	if(isEmpty($street)) $error_street = "empty";
	if(isEmpty($city)) $error_city = "empty";
	if(isEmpty($group_name)) $error_group_name = "empty";
	
	if($error == ""){
		$sql = "INSERT	INTO `kk_visitors` (`name`, `surname`, `nick`, `birthday`, `email`, `street`, `city`, `postal_code`, `province`, `group_num`, `group_name`, `troop_name`, `comment`, `arrival`, `departure`, `bill`, `meeting`, `question`, `code`,`reg_daytime`) 
			VALUES ('".$name."', '".$surname."', '".$nick."', '".$birthday."', '".$email."', '".$street."', '".$city."', '".$postal_code."', '".$province."', '".$group_num."', '".$group_name."', '".$troop_name."', '".$comment."', '".$arrival."', '".$departure."', '".$bill."', '".$mid."', '".$question."', CONCAT(LEFT('".$name."',1),LEFT('".$surname."',1),SUBSTRING('".$birthday."',3,2)),'".date('Y-m-d H:i:s')."')";
			
		$result = mysql_query($sql);
		$vid = mysql_insert_id();
		if(!$result)$error = "error";
		else {$error = "ok";
			//$vid = 5;
			$blockSql = "SELECT 	id
		 				 FROM kk_blocks
		 				 WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
			$blockResult = mysql_query($blockSql);
			while($blockData = mysql_fetch_assoc($blockResult)){
				$usrProgSql = "INSERT INTO `kk_visitor-program` (`visitor`, `program`)
							   VALUES ('".$vid."', '".$$blockData['id']."')";
				$usrProgResult = mysql_query($usrProgSql);
			}
			
			$mealSql = "INSERT	INTO `kk_meals` (`visitor`, `fry_dinner`, `sat_breakfast`, `sat_lunch`, `sat_dinner`, `sun_breakfast`, `sun_lunch`) 
			VALUES ('".$vid."', '".$fry_dinner."', '".$sat_breakfast."', '".$sat_lunch."', '".$sat_dinner."', '".$sun_breakfast."', '".$sun_lunch."')";
			$mealResult = mysql_query($mealSql);
		
			######################## ODESILAM EMAIL ##########################

			// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
			$code4bank = substr($name, 0, 1).substr($surname, 0, 1).substr($birthday, 2, 2);
			$hash = ((int)$vid.$mid) * 147 + 49873;	
							
			$recipient_mail = $email;
			$recipient_name = $name." ".$surname;
			
			$Container = new Container($GLOBALS['cfg']);
			$Emailer = $Container->createEmailer();
			if($return = $Emailer->sendRegistrationSummary($recipient_mail, $recipient_name, $hash, $code4bank)) {
				redirect("check.php?hash=".$hash."&error=".$error."");
			} else {
				echo 'Došlo k chybě při odeslání e-mailu.';
				echo 'Chybová hláška: ' . $return;
			}
		
		
			##################################################################
		}
	}
}

########################## ROLLS ####################################  
$province_roll = "<select ".$disabled." style='width: 195px; font-size:11px' name='province'>\n";

$provinceSql = "SELECT	*
				FROM kk_provinces";
$provinceResult = mysql_query($provinceSql);

while($provinceData = mysql_fetch_assoc($provinceResult)){
	if($provinceData['id'] == $province){
		$sel = "selected";
	}
	else $sel = "";
	$province_roll .= "<option value='".$provinceData['id']."' ".$sel.">".$provinceData['province_name']."</option>";
}
$province_roll .= "</select>\n";

////meal roll
// poradi mus byt nejdrive NE a potom ANO, podle toho, co chci, aby se defaultne zobrazilo ve formulari
$meal_array = array("ne" => "ne","ano" => "ano");
$mealDayArray = array("páteční večeře"=>"fry_dinner",
					  "sobotní snídaně"=>"sat_breakfast",
					  "sobotní oběd"=>"sat_lunch",
					  "sobotní večeře"=>"sat_dinner",
					  "nedělní snídaně"=>"sun_breakfast",
					  "nedělní oběd"=>"sun_lunch");

$meal_roll = "";
foreach($mealDayArray as $mealDayKey => $mealDayVal){
	if(preg_match("/breakfast/", $mealDayVal)) $mealIcon = "breakfast";
	if(preg_match("/lunch/", $mealDayVal)) $mealIcon = "lunch";
	if(preg_match("/dinner/", $mealDayVal)) $mealIcon = "dinner";
	
	$meal_roll .= "<span style='display:block;font-size:11px;'>".$mealDayKey.":</span><img style='width:18px;' src='".$ICODIR."small/".$mealIcon.".png' /><select ".$disabled." style='width:195px; font-size:11px;margin-left:5px;' name='".$mealDayVal."'>\n";
	foreach ($meal_array as $meal_key => $v2){
		if($meal_key == $$mealDayVal){
			$sel2 = "selected";
		}
		else $sel2 = "";
		$meal_roll .= "<option value='".$meal_key."' ".$sel2.">".$v2."</option>";
	}
	$meal_roll .= "</select><br />\n";
}

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

<link rel="stylesheet" href="<?php echo HTTP_DIR; ?>remote/lightbox/style.css?1" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo HTTP_DIR; ?>remote/lightbox/script.js?1"></script>

<link rel="stylesheet" href="<?php echo $CSSDIR ?>colorbox.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script src="<?php echo $AJAXDIR ?>colorbox/jquery.colorbox.js"></script>
<script>
$(document).ready(function(){
	$(".programLink").colorbox({rel:'programDetail', width:"75%", height:"50%", transition:"fade"});
	//Example of preserving a JavaScript event for inline calls.
	$("#click").click(function(){ 
		$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
		return false;
	});
});
</script>

<link rel="alternate" type="application/rss+xml" href="<?php echo HTTP_DIR; ?>remote/rss.php?tp=4&amp;id=-1" title="Nejnovější články" />
<link rel="shortcut icon" href="<?php echo HTTP_DIR; ?>favicon.ico?1" />
<title>Novinky - HKVS</title>

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
<!-- PlexIS -->

<title>Registrace - Sraz vodních skautů</title>
</head>

<body>

<!-- outer -->

<div id="outer">

  <!-- page -->
  <div id="page">

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

    <!-- column -->
    <div id="column">
    <!--div id="column-pad"-->

<h3 class='box-title'>Menu</h3>
<ul class='menu'>
<li class="menu-item-29 first"><a href=<?php echo HTTP_DIR; ?>'z-kapitanskeho-mustku' class='hs_closed' onclick="return _sysHideShow('sh1', this)">Z kapitánského můstku</a>
<ul class='hs_content hs_hidden' id='sh1'>
<li class="menu-item-14 first"><a href='<?php echo HTTP_DIR; ?>hkvs-hlasi'>HKVS hlásí</a></li><li class="menu-item-46"><a href='<?php echo HTTP_DIR; ?>slozeni-hkvs'>Složení HKVS</a></li><li class="menu-item-35"><a href='<?php echo HTTP_DIR; ?>zapisy'>Zápisy</a></li><li class="menu-item-36"><a href='<?php echo HTTP_DIR; ?>rady-vyhlasky-predpisy'>Řády, vyhlášky, předpisy</a></li><li class="menu-item-63"><a href='<?php echo HTTP_DIR; ?>registr-skautskych-jachet'>Registr skautských jachet</a></li><li class="menu-item-99 last"><a href='<?php echo HTTP_DIR; ?>kapitanat-vodnich-skautu-2'>Kapitanát vodních skautů</a></li>

</ul>
</li>
<li class="menu-item-57"><a href='<?php echo HTTP_DIR; ?>metodika' class='hs_closed' onclick="return _sysHideShow('sh2', this)">Metodika</a>
<ul class='hs_content hs_hidden' id='sh2'>
<li class="menu-item-58 first"><a href='<?php echo HTTP_DIR; ?>metodicke-aktuality'>Metodické aktuality</a></li><li class="menu-item-106"><a href='<?php echo HTTP_DIR; ?>metodicke-materialy'>Metodické materiály</a></li><li class="menu-item-59"><a href='<?php echo HTTP_DIR; ?>zabicky-a-vlcata'>Žabičky a vlčata</a></li><li class="menu-item-60 last"><a href='<?php echo HTTP_DIR; ?>skautky-a-skauti'>Skautky a skauti</a></li>
</ul>
</li>
<li class="menu-item-28"><a href='<?php echo HTTP_DIR; ?>vzdelavani' class='hs_closed' onclick="return _sysHideShow('sh3', this)">Vzdělávání</a>
<ul class='hs_content hs_hidden' id='sh3'>
<li class="menu-item-105 first"><a href='<?php echo HTTP_DIR; ?>vodacke-kvalifikace'>Vodácké kvalifikace</a></li><li class="menu-item-34"><a href='<?php echo HTTP_DIR; ?>materialy-ke-studiu'>Materiály ke studiu</a></li><li class="menu-item-109"><a href='<?php echo HTTP_DIR; ?>lektori-a-instruktori-vs'>Lektoři a instruktoři VS</a></li><li class="menu-item-82"><a href='<?php echo HTTP_DIR; ?>namorni-akademie'>Námořní akademie</a></li><li class="menu-item-83"><a href='<?php echo HTTP_DIR; ?>lesni-skola-vodnich-skautu-2'>Lesní škola vodních skautů</a></li><li class="menu-item-13 last"><a href='<?php echo HTTP_DIR; ?>clanky-o-vzdel.akcich'>Články o vzděl. akcích</a></li>

</ul>
</li>
<li class="menu-item-27"><a href='<?php echo HTTP_DIR; ?>akce' class='hs_closed' onclick="return _sysHideShow('sh4', this)">Akce</a>
<ul class='hs_content hs_hidden' id='sh4'>
<li class="menu-item-76 first"><a href='<?php echo HTTP_DIR; ?>aktuality-o-akcich'>Aktuality o akcích</a></li><li class="menu-item-33"><a href='<?php echo HTTP_DIR; ?>terminka'>Termínka</a></li><li class="menu-item-77"><a href='<?php echo HTTP_DIR; ?>sraz-vs-usk'>Sraz VS - ÚSK</a></li><li class="menu-item-80"><a href='<?php echo HTTP_DIR; ?>zavody'>Závody</a></li><li class="menu-item-32"><a href='<?php echo HTTP_DIR; ?>pres-tri-jezy'>Přes tři jezy</a></li><li class="menu-item-110"><a href='<?php echo HTTP_DIR; ?>skare' target='_blank'>SKARE</a></li><li class="menu-item-79"><a href='<?php echo HTTP_DIR; ?>navigamus'>Navigamus</a></li><li class="menu-item-54"><a href='<?php echo HTTP_DIR; ?>namorni-akademie-2'>Námořní akademie</a></li><li class="menu-item-26"><a href='<?php echo HTTP_DIR; ?>lesni-skola-vodnich-skautu'>Lesní škola vodních skautů</a></li><li class="menu-item-101 last"><a href='<?php echo HTTP_DIR; ?>oslavy-100-let-vs'>Oslavy 100 let VS</a></li>
</ul>

</li>
<li class="menu-item-112"><a href='<?php echo HTTP_DIR; ?>hospodareni' class='hs_closed' onclick="return _sysHideShow('sh5', this)">Hospodaření</a>
<ul class='hs_content hs_hidden' id='sh5'>
<li class="menu-item-111 first"><a href='<?php echo HTTP_DIR; ?>vodacke-desetikoruny'>Vodácké desetikoruny</a></li>
</ul>
</li>
<li class="menu-item-30"><a href='<?php echo HTTP_DIR; ?>casopisy' class='hs_closed' onclick="return _sysHideShow('sh6', this)">Časopisy</a>
<ul class='hs_content hs_hidden' id='sh6'>
<li class="menu-item-10 first"><a href='<?php echo HTTP_DIR; ?>aktuality-o-casopisech'>Aktuality o časopisech</a></li><li class="menu-item-37"><a href='<?php echo HTTP_DIR; ?>kapitanska-posta'>Kapitánská pošta</a></li><li class="menu-item-50"><a href='<?php echo HTTP_DIR; ?>modkre-stranky'>Mod/kré stránky</a></li><li class="menu-item-108"><a href='<?php echo HTTP_DIR; ?>euronaut'>Euronaut</a></li><li class="menu-item-38"><a href='<?php echo HTTP_DIR; ?>oddilove-casopisy'>Oddílové časopisy</a></li><li class="menu-item-103 last"><a href='<?php echo HTTP_DIR; ?>napsali-o-nas'>Napsali o nás</a></li>

</ul>
</li>
<li class="menu-item-39"><a href='<?php echo HTTP_DIR; ?>kontakty' class='hs_closed' onclick="return _sysHideShow('sh7', this)">Kontakty</a>
<ul class='hs_content hs_hidden' id='sh7'>
<li class="menu-item-92 first"><a href='<?php echo HTTP_DIR; ?>adresar-vodnich-skautu'>Adresář vodních skautů</a></li><li class="menu-item-41"><a href='<?php echo HTTP_DIR; ?>prehled-oddilu-vs'>Přehled oddílů VS</a></li><li class="menu-item-48"><a href='<?php echo HTTP_DIR; ?>cinovnici-hkvs'>Činovníci HKVS</a></li><li class="menu-item-107"><a href='<?php echo HTTP_DIR; ?>infokanaly-vs'>Infokanály VS</a></li><li class="menu-item-96"><a href='<?php echo HTTP_DIR; ?>kapitanat-vodnich-skautu'>Kapitanát vodních skautů</a></li><li class="menu-item-102 last"><a href='<?php echo HTTP_DIR; ?>sprava-webu-hkvs'>Správa webu HKVS</a></li>
</ul>
</li>
<li class="menu-item-49"><a href='<?php echo HTTP_DIR; ?>odkazy'>Odkazy</a></li>
<li class="menu-item-61"><a href='<?php echo HTTP_DIR; ?>galerie-a-video'>Galerie a video</a></li>

<li class="menu-item-90 last"><a href='<?php echo HTTP_DIR; ?>forum'>Fórum</a></li>
</ul>

<h3 class='box-title'>Redakce</h3>
<ul>
  <li><a href="admin/index.php?p=content-articles-edit">napsat článek</a></li>
  <li><a href="admin/index.php?p=content-articles">spravovat články</a></li>
  <li><a href="index.php?a=50">jak napsat článek (video)</a></li>

</ul>

<h3 class='box-title'>Uživatel</h3>
<ul>
<li><a href='<?php echo HTTP_DIR; ?>./index.php?m=messages' class='usermenu-item-messages'>vzkazy</a></li>
<li><a href='<?php echo HTTP_DIR; ?>./index.php?m=settings' class='usermenu-item-settings'>nastavení</a></li>
<li><a href='<?php echo HTTP_DIR; ?>./remote/logout.php?_return=%2Fvodni%2F&amp;_security_token=4f3c229b76d179.71247191' class='usermenu-item-logout'>odhlásit</a></li>
<li><a href='<?php echo HTTP_DIR; ?>./index.php?m=ulist' class='usermenu-item-ulist'>uživatelé</a></li>
</ul>



<h3 class='box-title'>Vyhledávání</h3>
<form action='index.php' method='get' class='searchform'>
<input type='hidden' name='m' value='search' />
<input type='hidden' name='root' value='1' />
<input type='hidden' name='art' value='1' />
<input type='hidden' name='post' value='1' />
<input type="hidden" name="_security_token" value="4f3c229b76d179.71247191" />
<input type='text' name='q' class='q' /> <input type='submit' value='Vyhledat' />
</form>

    <!--/div-->
    </div>

    <!-- content -->
    <div id="content">
    <div id="content-pad">
<h1>Registrace na Srazy VS</h1>
<h2><?php echo $meetingHeader; ?></h2>
<br />
<?php printError($error); ?>

<?php
/*echo $error;
echo $error_name;
echo $error_surname;
echo $error_postal_code;
echo $error_email;
echo $error_group_num;
echo $error_bill;*/
?>

<!-- REGISTRACNI FORMULAR SRAZU -->

<form action='index.php' method='post'>

<div class='button-line'>
 <button <?php echo $disabled; ?> type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>

<div style="text-align:center;"><span class="required">*</span>Takto označené položky musí být vyplněné!</div>

<!-- Datedit by Ivo Skalicky - ITPro CZ - http://www.itpro.cz -->
<script type="text/javascript" charset="iso-8859-1" src="<?php echo $AJAXDIR; ?>datedit/datedit.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo $AJAXDIR; ?>datedit/lang/cz.js"></script>
<script type="text/javascript">
  <?php
  //jak prekonvertovat pomoci datedit datum pro sql databazi
  //datedit("start_date","dd.mm.yyyy",true,"yyyy-mm-dd");
  ?>
  datedit("birthday","dd.mm.yyyy");
</script> 

<table class='form'>
 <tr>
  <td class='label'><label><span class="required">*</span>Jméno:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='name' size='30' value='<?php echo $name; ?>' /><?php printError($error_name); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Příjmení:</label></td>
  <td><input <?php echo $disabled; ?> type="text" name='surname' size="30" value='<?php echo $surname; ?>' /><?php printError($error_surname); ?></td>
 </tr>
 <tr>
  <td class='label'><label>Přezdívka:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='nick' size='30' value='<?php echo $nick; ?>' /></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>E-mail:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='email' size='30' value='<?php echo $email; ?>' /><?php printError($error_email); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Datum narození:</label></td>
  <td><div class="picker"><input <?php echo $disabled; ?> id="birthday" type='text' name='birthday' size='30' value='<?php echo formatDateFromDB($birthday,"d.m.Y"); ?>' /></div> (datum ve formátu dd.mm.rrrr) <?php printError($error_birthday); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Ulice:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='street' size='30' value='<?php echo $street; ?>' /><?php printError($error_street); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Město:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='city' size='30' value='<?php echo $city; ?>' /><?php printError($error_city); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>PSČ:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='postal_code' size='10' value='<?php echo $postal_code; ?>' /> (formát: 12345)<?php printError($error_postal_code); ?></td>
 </tr>
 <tr>
  <td></td>
  <td><div style="margin:2px 0px 2px 0px; font-weight:bold;">Junák - svaz skautů a skautek ČR</div></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Číslo střediska/přístavu:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='group_num' size='10' value='<?php echo $group_num; ?>' /> (formát: 214[tečka]02)<?php printError($error_group_num); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Název střediska/přístavu:</label></td>
  <td>
   <input <?php echo $disabled; ?> type='text' name='group_name' size='30' value='<?php echo $group_name; ?>' /> (2. přístav Poutníci Kolín) <?php printError($error_group_name); ?>
  </td>
 </tr>
 <tr>
  <td class='label'><label>Název oddílu:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='troop_name' size='30' value='<?php echo $troop_name; ?>' /> (22. oddíl Galeje)</td>
 </tr>
 <tr>
  <td class='label'><label>Kraj:</label></td>
  <td><?php echo $province_roll; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Stravování:</label></td>
  <td><?php echo $meal_roll; ?></td>
 </tr>
 <tr>
  <td colspan="2">Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) přijedete na místo srazu a v kolik hodin (přibližně) a jakým prostředkem sraz opustíte.
  </td>
 </tr>
 <tr>
  <td style="font-weight:bold;" colspan="2">Pokud máte volná místa v autě a jste ochotni někoho vzít, vyplňte prosím odkud jedete a kolik máte volných míst.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Informace o příjezdu:</label></td>
  <td><textarea <?php echo $disabled; ?> name='arrival' cols="50" rows="3" ><?php echo $arrival; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Informace o odjezdu:</label></td>
  <td><textarea <?php echo $disabled; ?> name='departure' cols="50" rows="3" ><?php echo $departure; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Dotazy, přání, připomínky, stížnosti:</label></td>
  <td><textarea <?php echo $disabled; ?> name='comment' cols="50" rows="8" ><?php echo $comment; ?></textarea></td>
 </tr>
<!-- <tr>
  <td style="font-weight:bold;" colspan="2">Máte nezodpovězené otázky ohledně Junáka a celé organizace nebo HKVS? Nerozumíte něčemu? Trápí vás to? Nevyznáte se v něčem? Potřebujete poradit? Zde napiště svoji otázku a my vám na ní odpovíme! Odpovědi najdete na jarním srazu nebo později na webu HKVS.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Vaše otázka:</label></td>
  <td><textarea <?php echo $disabled; ?> name='question' cols="50" rows="8" ><?php echo $question; ?></textarea></td>
 </tr>-->
</table>

 <input <?php echo $disabled; ?> type='hidden' name='cms' value='create' />
 <input <?php echo $disabled; ?> type='hidden' name='mid' value='<?php echo $mid;  ?>' />
 <input <?php echo $disabled; ?> type="hidden" name="bill" value="0" />
 
 <?php echo $programs; ?>
 
 <div class='button-line'>
 <button <?php echo $disabled; ?> type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>
 
</form>


<!-- REGISTRACNI FORMULAR SRAZU -->

<p> </p>
<p style="text-align: center;"> </p>    </div>
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