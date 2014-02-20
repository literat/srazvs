<link href="<?php echo CSS_DIR; ?>default.css" rel="stylesheet" type="text/css" />

<div class='siteContentRibbon'>Správa účastníků</div>
<?php echo $data['error']; ?>
<div class='pageRibbon'><?php echo $data['heading']; ?></div>

<form action='?visitor' method='post'>

	<div class='button-line'>
		<button type='submit' onclick=\"this.form.submit()\">
			<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
		<button type='button' onclick="window.location.replace('<?php echo VISIT_DIR; ?>')">
			<img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
	</div>

	<script src='<?php echo JS_DIR; ?>jquery/jquery-ui.min.js' type='text/javascript'></script>
	<script type="text/javascript">
	$(function() {
		$.datepicker.setDefaults($.datepicker.regional['cs']);
		$( ".datePicker" ).datepicker({
			showOn: "button",
			buttonImage: "<?php echo IMG_DIR; ?>calendar_button.png",
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
			<td class='label'><label class="required">Jméno:</label></td>
			<td><input type='text' name='name' size='30' value='<?php echo $data['name']; ?>' /><?php echo $data['error_name']; ?></td>
		</tr>
		<tr>
			<td class='label'><label class="required">Příjmení:</label></td>
			<td><input type="text" name='surname' size="30" value='<?php echo $data['surname']; ?>' /><?php echo $data['error_surname']; ?></td>
		</tr>
		<tr>
			<td class='label'><label class="required">Přezdívka:</label></td>
			<td><input type='text' name='nick' size='30' value='<?php echo $data['nick']; ?>' /><?php echo $data['error_nick']; ?></td>
		</tr>
		<tr>
			<td class='label'><label class="required">E-mail:</label></td>
			<td><input type='text' name='email' size='30' value='<?php echo $data['email'] ?>' /><?php echo $data['error_email']; ?></td>
		</tr>
		<tr>
			<td class='label'><label class="required">Datum narození:</label></td>
			<td><div class="picker"><input id="birthday" class="datePicker" type='text' name='birthday' size='30' value='<?php echo $data['birthday']; ?>' /></div> (datum ve formátu rrrr-mm-dd)</td>
		</tr>
		<tr>
			<td class='label'><label class="required">Ulice:</label></td>
			<td><input type='text' name='street' size='30' value='<?php echo $data['street']; ?>' /></td>
		</tr>
		<tr>
			<td class='label'><label class="required">Město:</label></td>
			<td><input type='text' name='city' size='30' value='<?php echo $data['city']; ?>' /></td>
		</tr>
		<tr>
			<td class='label'><label class="required">PSČ:</label></td>
			<td><input type='text' name='postal_code' size='10' value='<?php echo $data['postal_code']; ?>' /> (formát: 12345)<?php echo $data['error_postal_code']; ?></td>
		</tr>
		<tr>
			<td class='label'><label class="required">Číslo střediska/přístavu:</label></td>
			<td><input type='text' name='group_num' size='10' value='<?php echo $data['group_num']; ?>' /> (formát: 214[tečka]02)<?php echo $data['error_group_num']; ?></td>
		</tr>
		<tr>
			<td class='label'><label class="required">Název střediska/přístavu:</label></td>
			<td><div style="margin:2px 0px 2px 0px; font-weight:bold;">Junák - svaz skautů a skautek ČR</div>
			<input type='text' name='group_name' size='30' value='<?php echo $data['group_name']; ?>' /> (2. přístav Poutníci Kolín)
			</td>
		</tr>
		<tr>
			<td class='label'><label>Název oddílu:</label></td>
			<td><input type='text' name='troop_name' size='30' value='<?php echo $data['troop_name']; ?>' /> (22. oddíl Galeje)</td>
		</tr>
		<tr>
			<td class='label'><label>Kraj:</label></td>
			<td><?php echo $data['province']; ?></td>
		</tr>
		<tr>
			<td class='label'><label>Stravování:</label></td>
			<td><?php echo $data['meals']; ?></td>
		</tr>
		<tr>
			<td class='label'><label>Informace o příjezdu:</label></td>
			<td><textarea name='arrival' cols="50" rows="3" ><?php echo $data['arrival']; ?></textarea></td>
		</tr>
		<tr>
			<td class='label'><label>Informace o odjezdu:</label></td>
			<td><textarea name='departure' cols="50" rows="3" ><?php echo $data['departure']; ?></textarea></td>
		</tr>
		<tr>
			<td class='label'><label>Dotazy, přání, připomínky, stížnosti:</label></td>
			<td><textarea name='comment' cols="50" rows="8" ><?php echo $data['comment']; ?></textarea></td>
		</tr>
		<tr>
			<td class='label'><label>Vaše otázka:</label></td>
			<td><textarea name='question' cols="50" rows="8" ><?php echo $data['question']; ?></textarea></td>
		</tr>
		<tr>
			<td class='label'><label>Zaplaceno:</label></td>
			<td><input type='text' name='bill' size='15' value='<?php echo $data['bill']; ?>' /> ,- Kč <?php echo $data['error_bill']; ?></td>
		</tr>
		<tr>
			<td class='label'><label>Poplatek:</label></td>
			<td><input type='text' name='cost' size='15' value='<?php echo $data['cost']; ?>' /> ,- Kč <?php echo $data['error_cost']; ?></td>
		</tr>
	</table>

	<div style='border-bottom:1px solid black;text-align:right;'>výběr programů</div>

		<?php echo $data['program_switcher']; ?>

		<input type='hidden' name='id' value='<?php echo $data['id']; ?>'>
		<input type='hidden' name='cms' value='<?php echo $data['todo']; ?>'>
		<input type='hidden' name='page' value='<?php echo $data['page']; ?>'>
	 
		<div class='button-line'>
		<button type='submit' onclick=\"this.form.submit()\">
			<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
		<button type='button' onclick="window.location.replace('<?php echo VISIT_DIR; ?>')">
			<img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
	</div>
 
</form>