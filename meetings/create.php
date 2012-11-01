<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

######################### KONTROLA ########################################

$cms = requested("cms","");

$place = requested("place","");
$start_date = requested("start_date","");
$end_date = requested("end_date","");
$open_reg = requested("open_reg","");
$close_reg = requested("close_reg","");
$contact = requested("contact","");
$email = requested("email","");
$gsm = requested("gsm","");
$cost = requested("cost",0);
$advance = requested("advance",0);

////inicializace promenych
$error = "";
$error_start = "";
$error_end = "";
$error_open_reg = "";
$error_close_reg = "";
$error_login = "";

######################## ZPRACOVANI ####################################
//prihlasovaci udaje
if($cms == "create"){
	$sql = "INSERT	INTO `kk_meetings` (`place`, `start_date`, `end_date`, `open_reg`, `close_reg`, `contact`, `email`, `gsm`, `cost`, `advance`) 
			VALUES ('".$place."', '".$start_date."', '".$end_date."', '".$open_reg."', '".$close_reg."', '".$contact."', '".$email."', '".$gsm."', '".$cost."', '".$advance."')";
	$result = mysql_query($sql);
	if(!$result){
		$error = "error";
	}
	else {$error = "ok";
		redirect("index.php?error=".$error."");
	}
};

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

##################################################################

?>
<link href="../styles/css/default.css" rel="stylesheet" type="text/css" />

<div class='siteContentRibbon'>Správa srazů</div>
<?php printError($error); ?>
<div class='pageRibbon'>nový sraz</div>

<form action='create.php' method='post'>

<div class='button-line'>
 <button type='submit' onClick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('list.php')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>

<!-- Datedit by Ivo Skalicky - ITPro CZ - http://www.itpro.cz -->
<script type="text/javascript" charset="iso-8859-1" src="<?php echo $AJAXDIR; ?>datedit/datedit.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo $AJAXDIR; ?>datedit/lang/cz.js"></script>
<script type="text/javascript">
  <?php
  //jak prekonvertovat pomoci datedit datum pro sql databazi
  //datedit("start_date","dd.mm.yyyy",true,"yyyy-mm-dd");
  ?>
  datedit("start_date","yyyy-mm-dd");
  datedit("end_date","yyyy-mm-dd");
  datedit("open_reg","yyyy-mm-dd HH:MM:SS");
  datedit("close_reg","yyyy-mm-dd HH:MM:SS");
</script> 

<table class='form'>
 <tr>
  <td class='label'><label class="required">Místo:</label></td>
  <td><input type='text' name='place' size='30' value='<?php echo $place; ?>' /></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Začátek</label></td>
  <td><div class="picker"><input id='start_date' type='text' size='20' name='start_date' value="<?php echo $start_date; ?>" /></div><?php printError($error_start); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Konec:</label></td>
  <td><div class="picker"><input id='end_date' type='text' size='20' name='end_date' value="<?php echo $end_date; ?>" /></div><?php printError($error_end); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Otevřít přihlašování:</label></td>
  <td><div class="picker"><input id='open_reg' type='text' size='30' name='open_reg' value="<?php echo $open_reg; ?>" /></div><?php printError($error_open_reg); ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Zavřít přihlašování:</label></td>
  <td><div class="picker"><input id='close_reg' type='text' size='30' name='close_reg' value="<?php echo $close_reg; ?>" /></div><?php printError($error_close_reg); ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label>Cena srazu:</label></td>
  <td><input type='text' size='30' name='cost' value="<?php echo $cost; ?>" /> ,- Kč</td>
 </tr>
 <tr>
  <td class='label'><label>Záloha:</label></td>
  <td><input type='text' size='30' name='advance' value="<?php echo $advance; ?>" /> ,- Kč</td>
 </tr>
 <tr>
  <td class='label'><label>Kontaktní osoba:</label></td>
  <td><input type='text' size='30' name='contact' value="<?php echo $contact; ?>" /><?php printError($error_close_reg); ?></td>
 </tr> 
 <tr>
  <td class='label'><label>E-mail:</label></td>
  <td><input type='text' size='30' name='email' value="<?php echo $email; ?>" /><?php printError($error_close_reg); ?></td>
 </tr> 
 <tr>
  <td class='label'><label>Telefon (mobil):</label></td>
  <td><input type='text' size='30' name='gsm' value="<?php echo $gsm; ?>" /><?php printError($error_close_reg); ?> (123456789)</td>
 </tr> 
</table>

 <input type='hidden' name='cms' value='create'>
</form>

<?php
###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>