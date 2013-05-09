<?php

//header
require_once('../inc/define.inc.php');

########################### AKTUALNI SRAZ ##############################

// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
//$hash = ((int)192) * 147 + 49873;	

$hash = requested("hash","");
$mid = (($hash - 49873) / 147)%10;
$vid = floor((($hash - 49873) / 147)/10);

$sql = "SELECT	id,
				place,
				DATE_FORMAT(start_date, '%Y') AS year
		FROM kk_meetings
		WHERE id='".$mid."'
		ORDER BY id DESC
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

$meetingHeader = $data['place']." ".$data['year'];

########################## NAVSTEVNIK #####################################

$sql = "SELECT	vis.id AS id,
				name,
				surname,
				nick,
				DATE_FORMAT(birthday, '%d. %m. %Y') AS birthday,
				street,
				city,
				postal_code,
				province_name,
				group_num,
				group_name,
				troop_name,
				email,
				comment,
				arrival,
				departure,
				question,
				fry_dinner,
				sat_breakfast,
				sat_lunch,
				sat_dinner,
				sun_breakfast,
				sun_lunch
		FROM kk_visitors AS vis
		LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
		LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
		WHERE vis.id='".$vid."' AND meeting='".$mid."' AND deleted='0'
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

################################ PROGRAMY ###################################

$programs = "  <div style='border-bottom:1px solid black;text-align:right;'>vybrané programy</div>";

$progSql = "SELECT  progs.name AS prog_name,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`
			FROM kk_programs AS progs
			LEFT JOIN `kk_visitor-program` AS visprog ON progs.id = visprog.program
			LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
			LEFT JOIN kk_blocks AS blocks ON progs.block = blocks.id
			WHERE vis.id = '".$vid."'
			ORDER BY `day`, `from` ASC";
$progResult = mysql_query($progSql);
while($progData = mysql_fetch_assoc($progResult)){
	$programs .= $progData['day'].", ".$progData['from']." - ".$progData['to']."";
	$programs .= "<div style='padding:5px 0px 5px 20px;'>- ".$progData['prog_name']."</div>";
}

######################### KONTROLA ########################################

////inicializace promenych
$error = "";

$error = requested("error","");

////ziskani zvolenych programu
$blockSql = "SELECT 	id
			 FROM kk_blocks
			 WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
$blockResult = mysql_query($blockSql);
while($blockData = mysql_fetch_assoc($blockResult)){
	$$blockData['id'] = requested($blockData['id'],0);
	//echo $blockData['id'].":".$$blockData['id']."|";
}

$name = requested("name",$data['name']);
$surname = requested("surname",$data['surname']);
$nick = requested("nick",$data['nick']);
$birthday = requested("birthday",$data['birthday']);
$street = requested("street",$data['street']);
$city = requested("city",$data['city']);
$postal_code = requested("postal_code",$data['postal_code']);
$province = requested("province",$data['province_name']);
$group_num = requested("group_num",$data['group_num']);
$group_name = requested("group_name",$data['group_name']);
$troop_name = requested("troop_name",$data['troop_name']);
$email = requested("email",$data['email']);
$comment = requested("comment",$data['comment']);
$arrival = requested("arrival",$data['arrival']);
$departure = requested("departure",$data['departure']);
$question = requested("question",$data['question']);

$fry_dinner = requested("fry_dinner",$data['fry_dinner']);
$sat_breakfast = requested("sat_breakfast",$data['sat_breakfast']);
$sat_lunch = requested("sat_lunch",$data['sat_lunch']);
$sat_dinner = requested("sat_dinner",$data['sat_dinner']);
$sun_breakfast = requested("sun_breakfast",$data['sun_breakfast']);
$sun_lunch = requested("sun_lunch",$data['sun_lunch']);

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
<h1>Registrace na srazy K + K</h1>
<h2><?php echo $meetingHeader; ?></h2>
<br />
<?php printError($error); ?>

<!-- REGISTRACNI FORMULAR SRAZU -->

<p style="font-weight:bold;">Zkontrolujte si, prosím, Vámi zadané údaje. V případě nesouhlasících údajů a provedení změny kontaktujte, prosím, <a href="mailto:tomaslitera&#64;hotmail.com" title="správce registrace">správce</a>. Pokud problémy přetrvávají, můžete se pokusit ho na nějakém srazu VS chytit a ukamenovat...</p>

<table class='form'>
 <tr>
  <td class='label'>Jméno:</td>
  <td><?php echo $name; ?></td>
 </tr>
 <tr>
  <td class='label'>Příjmení:</td>
  <td><?php echo $surname; ?></td>
 </tr>
 <tr>
  <td class='label'>Přezdívka:</td>
  <td><?php echo $nick; ?></td>
 </tr>
 <tr>
  <td class='label'>E-mail:</td>
  <td><?php echo $email; ?></td>
 </tr>
 <tr>
  <td class='label'>Datum narození:</td>
  <td><?php echo $birthday; ?></td>
 </tr>
 <tr>
  <td class='label'>Ulice:</td>
  <td><?php echo $street; ?></td>
 </tr>
 <tr>
  <td class='label'>Město:</td>
  <td><?php echo $city; ?></td>
 </tr>
 <tr>
  <td class='label'>PSČ:</td>
  <td><?php echo $postal_code; ?></td>
 </tr>
 <tr>
  <td class='label'>Číslo střediska/přístavu:</td>
  <td><?php echo $group_num; ?></td>
 </tr>
 <tr>
  <td class='label'>Název střediska/přístavu:</td>
  <td><div style="margin:2px 0px 2px 0px; font-weight:bold;">Junák - svaz skautů a skautek ČR</div>
   <?php echo $group_name; ?>
  </td>
 </tr>
 <tr>
  <td class='label'>Název oddílu:</td>
  <td><?php echo $troop_name; ?></td>
 </tr>
 <tr>
  <td class='label'>Kraj:</td>
  <td><?php echo $province; ?></td>
 </tr>
 <tr>
  <td class='label'>Stravování:</td>
  <td>
   <div>páteční večeře: <span style="font-weight:bold;"><?php echo $fry_dinner; ?></span></div>
   <div>sobotní snídaně: <span style="font-weight:bold;"><?php echo $sat_breakfast; ?></span></div>
   <div>sobotní oběd: <span style="font-weight:bold;"><?php echo $sat_lunch; ?></span></div>
   <div>sobotní večeře: <span style="font-weight:bold;"><?php echo $sat_dinner; ?></span></div>
   <div>nedělní snídaně: <span style="font-weight:bold;"><?php echo $sun_breakfast; ?></span></div>
   <div>nedělní oběd: <span style="font-weight:bold;"><?php echo $sun_lunch; ?></span></div>
  </td>
 </tr>
 <tr>
  <td class='label'>Informace o příjezdu:</td>
  <td><?php echo $arrival; ?></td>
 </tr>
 <tr>
  <td class='label'>Informace o odjezdu:</td>
  <td><?php echo $departure; ?></td>
 </tr>
 <tr>
  <td class='label'>Dotazy, přání, připomínky, stížnosti:</td>
  <td><?php echo $comment; ?></td>
 </tr>
 <tr>
  <td class='label'>Vaše otázka:</td>
  <td><?php echo $question; ?></td>
 </tr>
</table>
 
 <?php echo $programs; ?>


<p><h3>Děkujeme za přihlášení na sraz VS.</h3></p>

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