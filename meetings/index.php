<?php

require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once(INC_DIR.'access.inc.php');

###########################################################################

if($mid = requested("mid","")){
	$_SESSION['meetingID'] = $mid;
} else {
	$mid = $_SESSION['meetingID'];
}

$id = requested("id",$mid);
$cms = requested("cms","");
$error = requested("error","");

$Container = new Container($GLOBALS['cfg'], $mid);
$MeetingsHandler = $Container->createMeeting();

// delete program
if($cms == "del"){
	if($MeetingsHandler->delete($id)){	
	  	redirect("index.php?error=del");
	}
}

if($cms == 'list-view'){
	$heading1 = 'Správa srazů';
	$heading2 = 'seznam srazů';	
} else {
	$heading1 = 'Aktuální sraz';
	$heading2 = 'program';
}

$sql = "SELECT	*
		FROM kk_meetings
		WHERE id='".$mid."' AND deleted='0'
		LIMIT 1";
$result = mysql_query($sql);
$dbData = mysql_fetch_assoc($result);

foreach($MeetingsHandler->form_names as $key) {
	$$key = requested($key, $dbData[$key]);
}

////inicializace promenych
$error_start = "";
$error_end = "";
$error_open_reg = "";
$error_close_reg = "";
$error_login = "";

// styles in header
$style = Category::getStyles();

############################## GENEROVANI STRANKY ##########################

include_once(INC_DIR.'http_header.inc.php');
include_once(INC_DIR.'header.inc.php');

?>

<div class='siteContentRibbon'><?php echo $heading1; ?></div>
<?php printError($error); ?>
<div class='pageRibbon'><?php echo $heading2; ?></div>

<script src='<?php echo JS_DIR ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
<script>
$(document).ready(function() {
	$("#MeetingsTable").tablesorter( {
		headers: {
			0: { sorter: false},
			1: { sorter: false},
			5: { sorter: false},
			6: { sorter: false},
			7: { sorter: false},
			9: { sorter: false},
			10: { sorter: false},
		}
	} );
} );
</script>

<?php 

if($cms == 'list-view') {
	echo "<div class='link'><a class='link' href='process.php?cms=new&page=meetings'><img src='".$ICODIR."small/new.png' />NOVÝ SRAZ</a></div>\n";
	echo $MeetingsHandler->renderData();
} else {
	echo $MeetingsHandler->renderProgramOverview();
}

?>

<div class='pageRibbon'>nastavení</div>

<form action='process.php?page=meetings' method='post'>

<div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('index.php?cms=list-view')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div>

<script src='<?php echo JS_DIR ?>jquery/jquery-ui.js' type='text/javascript'></script>
<script src='<?php echo JS_DIR ?>jquery/jquery-ui-timepicker-addon.js' type='text/javascript'></script>
<script src='<?php echo JS_DIR ?>jquery/jquery-ui-slideraccess-addon.js' type='text/javascript'></script>
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
  <td><div class="picker"><input id="start_date" class='datePicker' type='text' size='20' name='start_date' value="<?php echo $start_date; ?>" /></div><?php printError($error_start); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Konec:</label></td>
  <td><div class="picker"><input id='end_date' class="datePicker" type='text' size='20' name='end_date' value="<?php echo $end_date; ?>" /></div><?php printError($error_end); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Otevřít přihlašování:</label></td>
  <td><div class="picker"><input id="open_reg" class='dateTimePicker' type='text' size='30' name='open_reg' value="<?php echo $open_reg; ?>" /></div>
  <?php printError($error_open_reg); ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Zavřít přihlašování:</label></td>
  <td><div class="picker"><input id="close_reg" class='dateTimePicker' type='text' size='30' name='close_reg' value="<?php echo $close_reg; ?>" /></div><?php printError($error_close_reg); ?>(rrrr-mm-dd hh:mm:ss)</td>
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

 <input type='hidden' name='cms' value='modify'>
 <input type='hidden' name='mid' value='<?php echo $mid; ?>'>
</form>

<?php

###################################################################

include_once(INC_DIR.'footer.inc.php');

###################################################################
?>