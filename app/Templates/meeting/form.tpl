<link href="../styles/css/default.css" rel="stylesheet" type="text/css" />

<div class='siteContentRibbon'>Správa srazů</div>
<?php echo $data['error']; ?>
<div class='pageRibbon'>úprava srazu</div>

<form action='?meeting' method='post'>

<div class='button-line'>
 <button type='submit' onClick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('?cms=list-view')">
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
  <td><input type='text' name='place' size='30' value='<?php echo $data['place']; ?>' /></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Začátek</label></td>
  <td><div class="picker"><input id='start_date' class='datePicker' type='text' size='20' name='start_date' value="<?php echo $data['start_date']; ?>" /></div><?php $data['error_start']; ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Konec:</label></td>
  <td><div class="picker"><input id='end_date' class='datePicker' type='text' size='20' name='end_date' value="<?php echo $data['end_date']; ?>" /></div><?php $data['error_end']; ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Otevřít přihlašování:</label></td>
  <td><div class="picker"><input id='open_reg' class='dateTimePicker' type='text' size='30' name='open_reg' value="<?php echo $data['open_reg']; ?>" /></div><?php $data['error_open_reg']; ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Zavřít přihlašování:</label></td>
  <td><div class="picker"><input id='close_reg' class='dateTimePicker' type='text' size='30' name='close_reg' value="<?php echo $data['close_reg']; ?>" /></div><?php $data['error_close_reg']; ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label>Cena srazu:</label></td>
  <td><input type='text' size='30' name='cost' value="<?php echo $data['cost']; ?>" /> ,- Kč</td>
 </tr>
 <tr>
  <td class='label'><label>Záloha:</label></td>
  <td><input type='text' size='30' name='advance' value="<?php echo $data['advance']; ?>" /> ,- Kč</td>
 </tr>
 <tr>
  <td class='label'><label>Číslování dokladů:</label></td>
  <td><input type='text' size='30' name='numbering' value="<?php echo $data['numbering']; ?>" /></td>
 </tr>
 <tr>
  <td class='label'><label>Kontaktní osoba:</label></td>
  <td><input type='text' size='30' name='contact' value="<?php echo $data['contact']; ?>" /><?php $data['error_close_reg']; ?></td>
 </tr> 
 <tr>
  <td class='label'><label>E-mail:</label></td>
  <td><input type='text' size='30' name='email' value="<?php echo $data['email']; ?>" /><?php $data['error_close_reg']; ?></td>
 </tr> 
 <tr>
  <td class='label'><label>Telefon (mobil):</label></td>
  <td><input type='text' size='30' name='gsm' value="<?php echo $data['gsm']; ?>" /><?php $data['error_close_reg']; ?> (123456789)</td>
 </tr> 
</table>

 <input type='hidden' name='cms' value='<?php echo $data['todo']; ?>'>
 <input type='hidden' name='page' value='<?php echo $data['page']; ?>'>
 <input type='hidden' name='mid' value='<?php echo $data['mid']; ?>'>	
</form>