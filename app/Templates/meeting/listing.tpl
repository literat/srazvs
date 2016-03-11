<div class='siteContentRibbon'>Správa srazů</div>
<?php $data['error']; ?>
<div class='pageRibbon'>Seznam srazů</div>

<div class='link'><a class='link' href='?cms=new&page=meetings'><img src='<?php echo IMG_DIR; ?>icons/new.png' />NOVÝ SRAZ</a></div>

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

<table id='MeetingsTable' class='list tablesorter'>
	<thead>
		<tr>
			<th></th>
			<th></th>
			<th class='tab1'>id</th>
			<th class='tab1'>místo</th>
			<th class='tab1'>začátek</th>
			<th class='tab1'>konec</th>
			<th class='tab1'>otevření registrace</th>
			<th class='tab1'>uzavření registrace</th>
			<th class='tab1'>kontakt</th>
			<th class='tab1'>e-mail</th>
			<th class='tab1'>telefon</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th></th>
			<th></th>
			<th class='tab1'>id</th>
			<th class='tab1'>místo</th>
			<th class='tab1'>začátek</th>
			<th class='tab1'>konec</th>
			<th class='tab1'>otevření registrace</th>
			<th class='tab1'>uzavření registrace</th>
			<th class='tab1'>kontakt</th>
			<th class='tab1'>e-mail</th>
			<th class='tab1'>telefon</th>
		</tr>
	</tfoot>
	<tbody>
		<?php if($data['render'] == 0) { ?>
		<tr class='radek1'>";
			<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/edit2.gif' /></td>
			<td><img class='edit' src='<?php echo IMG_DIR; ?>icons/delete2.gif' /></td>
			<td colspan='11' class='emptyTable'>Nejsou k dispozici žádné položky.</td>
		</tr>
		<?php
			} else {
				foreach($data['render'] as $row) {
		?>
		<tr class='radek1'>
			<td><a href='?id=<?php echo $row['id']; ?>&cms=edit&page=meetings' title='Upravit'><img class='edit' src='<?php echo IMG_DIR; ?>icons/edit.gif' /></a></td>
			<td><a href="javascript:confirmation('?id=<?php echo $row['id']; ?>&amp;cms=delete', 'sraz: <?php echo $row['place']; ?> <?php echo $row['start_date']; ?> -> Opravdu SMAZAT tento sraz? Jste si jisti?')" title='Odstranit'><img class='edit' src='<?php echo IMG_DIR; ?>icons/delete.gif' /></a></td>
			<td class='text'><?php echo $row['id']; ?></td>
			<td class='text'><?php echo $row['place']; ?></td>
			<td class='text'><?php echo $row['start_date']; ?></td>
			<td class='text'><?php echo $row['end_date']; ?></td>
			<td class='text'><?php echo $row['open_reg']; ?></td>
			<td class='text'><?php echo $row['close_reg']; ?></td>
			<td class='text'><?php echo $row['contact']; ?></td>
			<td class='text'><?php echo $row['email']; ?></td>
			<td class='text'><?php echo $row['gsm']; ?></td>
		</tr>
		<?php
				}
			}
		?>
	</tbody>
</table>


<div class='pageRibbon'>nastavení</div>

<form action='?meeting' method='post'>

<div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('?cms=list-view')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div>

<script src='<?php echo JS_DIR ?>jquery/jquery-ui.min.js' type='text/javascript'></script>
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
  <td><div class="picker"><input id="start_date" class='datePicker' type='text' size='20' name='start_date' value="<?php echo $data['start_date']; ?>" /></div><?php printError($data['error_start']); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Konec:</label></td>
  <td><div class="picker"><input id='end_date' class="datePicker" type='text' size='20' name='end_date' value="<?php echo $data['end_date']; ?>" /></div><?php printError($data['error_end']); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Otevřít přihlašování:</label></td>
  <td><div class="picker"><input id="open_reg" class='dateTimePicker' type='text' size='30' name='open_reg' value="<?php echo $data['open_reg']; ?>" /></div>
  <?php printError($data['error_open_reg']); ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Zavřít přihlašování:</label></td>
  <td><div class="picker"><input id="close_reg" class='dateTimePicker' type='text' size='30' name='close_reg' value="<?php echo $data['close_reg']; ?>" /></div><?php printError($data['error_close_reg']); ?>(rrrr-mm-dd hh:mm:ss)</td>
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
 <tr>
  <td class='label'><label>Číslování dokladů:</label></td>
  <td><input type='text' size='30' name='numbering' value="<?php echo $data['numbering']; ?>" /></td>
 </tr>
 <tr>
  <td class='label'><label>Kontaktní osoba:</label></td>
  <td><input type='text' size='30' name='contact' value="<?php echo $data['contact']; ?>" /><?php printError($data['error_close_reg']); ?></td>
 </tr>
 <tr>
  <td class='label'><label>E-mail:</label></td>
  <td><input type='text' size='30' name='email' value="<?php echo $data['email']; ?>" /><?php printError($data['error_close_reg']); ?></td>
 </tr>
 <tr>
  <td class='label'><label>Telefon (mobil):</label></td>
  <td><input type='text' size='30' name='gsm' value="<?php echo $data['gsm']; ?>" /><?php printError($data['error_close_reg']); ?> (123456789)</td>
 </tr>
</table>

 <input type='hidden' name='cms' value='modify'>
 <input type='hidden' name='mid' value='<?php echo $data['mid']; ?>'>
 <input type='hidden' name='page' value='meeting/?cms=list-view'>
</form>
