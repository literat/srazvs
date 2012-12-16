<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

######################### KONTROLA ########################################

$id = requested("id","");
$meetingId = $id;
$page = requested("page","");
$cms = requested("cms","");

$Container = new Container($GLOBALS['cfg'], $meetingId);
$MeetingsHandler = $Container->createMeeting();

////inicializace promenych
$error = "";
$error_start = "";
$error_end = "";
$error_open_reg = "";
$error_close_reg = "";
$error_login = "";

######################## ZPRACOVANI ####################################

switch($cms) {
	/* new meeting */
	case "new":
		$heading = "nový sraz";
		$todo = "create";
		
		// requested for visitors fields
		foreach($MeetingsHandler->dbColumns as $key) {
			$$key = requested($key, "");	
		}
		break;
	/* process creation of new meeting */
	case "create":
		// requested for meeting
		foreach($MeetingsHandler->form_names as $key) {
			$$key = requested($key, null);	
		}
		
		foreach($MeetingsHandler->dbColumns as $key) {
				$dbData[$key] = $$key;	
		}
		// create
		if($MeetingsHandler->create($dbData)){	
			redirect("../".$page."?error=ok");
		}
		break;
	/* edit meeting */
	case "edit":
		$heading = "úprava srazu";
		$todo = "modify";
		// get meeting's data
		$query = "SELECT	*
					FROM kk_meetings
					WHERE id='".$id."' AND deleted='0'
					LIMIT 1"; 
		$dbData = mysql_fetch_assoc(mysql_query($query));
		
		foreach($MeetingsHandler->dbColumns as $key) {
			$$key = requested($key, $dbData[$key]);
		}
		
		break;
	/* process updating information about meeting */
	case "modify":
		foreach($MeetingsHandler->dbColumns as $key) {
			$$key = requested($key, $value);
			$dbData[$key] = $$key;
		}
		
		if($MeetingsHandler->modify($id, $dbData)){	
			redirect("../".$page."?error=ok");
		}	
		break;
}

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

##################################################################

?>
<link href="../styles/css/default.css" rel="stylesheet" type="text/css" />

<div class='siteContentRibbon'>Správa srazů</div>
<?php printError($error); ?>
<div class='pageRibbon'>úprava srazu</div>

<form action='process.php' method='post'>

<div class='button-line'>
 <button type='submit' onClick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('index.php?cms=list-view')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>

<script src='<?php echo $JSDIR ?>jquery/jquery-ui.js' type='text/javascript'></script>
<script src='<?php echo $JSDIR ?>jquery/jquery-ui-timepicker-addon.js' type='text/javascript'></script>
<script src='<?php echo $JSDIR ?>jquery/jquery-ui-slideraccess-addon.js' type='text/javascript'></script>
<script type="text/javascript">
$(function() {
	$.datepicker.setDefaults($.datepicker.regional['cs']);
	$.timepicker.setDefaults($.timepicker.regional['cs']);
	$( ".dateTimePicker" ).datetimepicker({
		showOn: "button",
		buttonImage: "../images/calendar_button.png",
		buttonImageOnly: true,
		showWeek: true,
        firstDay: 1,
		showOtherMonths: true,
        selectOtherMonths: true,
		showButtonPanel: true,
		changeMonth: true,
		changeYear: true,
		showSecond: true,
		dateFormat: 'yy-mm-dd',
		timeFormat: 'HH:mm:ss',
		addSliderAccess: true,
		sliderAccessArgs: { touchonly: false }

	});
});

$(function() {
	$.datepicker.setDefaults($.datepicker.regional['cs']);
	$( ".datePicker" ).datepicker({
		showOn: "button",
		buttonImage: "../images/calendar_button.png",
		buttonImageOnly: true,
		showWeek: true,
        firstDay: 1,
		showOtherMonths: true,
        selectOtherMonths: true,
		showButtonPanel: true,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
	});
});
</script>

<table class='form'>
 <tr>
  <td class='label'><label class="required">Místo:</label></td>
  <td><input type='text' name='place' size='30' value='<?php echo $place; ?>' /></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Začátek</label></td>
  <td><div class="picker"><input id='start_date' class='datePicker' type='text' size='20' name='start_date' value="<?php echo $start_date; ?>" /></div><?php printError($error_start); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Konec:</label></td>
  <td><div class="picker"><input id='end_date' class='datePicker' type='text' size='20' name='end_date' value="<?php echo $end_date; ?>" /></div><?php printError($error_end); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Otevřít přihlašování:</label></td>
  <td><div class="picker"><input id='open_reg' class='dateTimePicker' type='text' size='30' name='open_reg' value="<?php echo $open_reg; ?>" /></div><?php printError($error_open_reg); ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Zavřít přihlašování:</label></td>
  <td><div class="picker"><input id='close_reg' class='dateTimePicker' type='text' size='30' name='close_reg' value="<?php echo $close_reg; ?>" /></div><?php printError($error_close_reg); ?>(rrrr-mm-dd hh:mm:ss)</td>
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

 <input type='hidden' name='cms' value='<?php echo $todo; ?>'>
 <input type='hidden' name='page' value='<?php echo $page; ?>'>
 <input type='hidden' name='id' value='<?php echo $id; ?>'>	
</form>

<?php
###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>