<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

################################ SQL, KONTROLA #############################

$mid = requested("mid","");

$sql = "SELECT	*
		FROM kk_meetings
		WHERE id='".$mid."' AND deleted='0'
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

$cms = requested("cms","");
$redir = requested("redir","");

$place = requested("place",$data['place']);
$start_date = requested("start_date",$data['start_date']);
$end_date = requested("end_date",$data['end_date']);
$open_reg = requested("open_reg",$data['open_reg']);
$close_reg = requested("close_reg",$data['close_reg']);
$contact = requested("contact",$data['contact']);
$email = requested("email",$data['email']);
$gsm = requested("gsm",$data['gsm']);
$cost = requested("cost",$data['cost']);
$advance = requested("advance",$data['advance']);
$numbering = requested("numbering",$data['numbering']);

////inicializace promenych
$error = "";
$error_start = "";
$error_end = "";
$error_open_reg = "";
$error_close_reg = "";
$error_login = "";

######################## ZPRACOVANI ####################################
//prihlasovaci udaje
if($cms == "update"){
	$sql = "UPDATE `kk_meetings`
			SET `place` = '".$place."', 
				`start_date` = '".$start_date."', 
				`end_date` = '".$end_date."', 
				`open_reg` = '".$open_reg."', 
				`close_reg` = '".$close_reg."', 
				`contact` = '".$contact."', 
				`email` = '".$email."', 
				`gsm` = '".$gsm."',
				`cost` = '".$cost."',
				`advance` = '".$advance."',
				`numbering` = '".$numbering."'
			WHERE id = '".$mid."'
			LIMIT 1";
	$result = mysql_query($sql);
	if(!$result){
		$error = "error";
	}
	else {$error = "ok";
		if($redir == "index") $page = "index";
		else $page = "list";
		redirect($page.".php?error=".$error."&mid=".$mid."");
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
<div class='pageRibbon'>úprava srazu</div>

<form action='update.php' method='post'>

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
  <td class='label'><label>Číslování dokladů:</label></td>
  <td><input type='text' size='30' name='numbering' value="<?php echo $numbering; ?>" /></td>
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

 <input type='hidden' name='cms' value='update'>
 <input type='hidden' name='mid' value='<?php echo $mid; ?>'>
</form>

<?php
###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>