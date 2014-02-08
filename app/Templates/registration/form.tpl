

<?php $page_title = "Registrace srazu VS"; ?>

	<!-- content -->
	<div id="content-program">
	<div id="content-pad-program">
<h1>Registrace na Srazy VS</h1>
<h2><?php echo $data['meeting_heading']; ?></h2>
<br />
<?php echo $data['error']; ?>

<?php if($data['display_registration']) { ?>

<script type="text/javascript" src="<?php echo JS_DIR ?>jquery/jquery.tinytips.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('a.programLink').tinyTips('light', 'title');
});
</script>

<script src='<?php echo JS_DIR; ?>jquery/jquery-ui.min.js' type='text/javascript'></script>
<script src='<?php echo JS_DIR; ?>jquery/jquery-ui-datepicker-validation.min.js' type='text/javascript'></script>
<script type="text/javascript">
$(function() {
	$.datepicker.setDefaults($.datepicker.regional['cs']);
	// deactivated 2013-09-26
	// is not usefull as well
	$( ".datePicker" ).datepicker({
		showOn: "button",
		buttonImage: "<?php echo IMG_DIR; ?>/calendar_button.png",
		buttonImageOnly: true,
		showWeek: true,
		firstDay: 7,
		showOtherMonths: true,
		selectOtherMonths: true,
		showButtonPanel: true,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'dd.mm.yy',
		maxDate: '0',
		minDate: '-100Y',
		yearRange: 'c-70,c+00',
		monthNamesShort: ['Led','Úno','Bře','Dub','Kvě','Čer','Čec','Srp','Zář','Říj','Lis','Pro'],
		dayNamesShort: ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'],
		dayNamesMin: ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'],
		weekHeader: 'Tý',
	});
});
</script>

<script src='<?php echo JS_DIR; ?>jquery/validation/messages_cs.js' type='text/javascript'></script>
<script src='<?php echo JS_DIR; ?>jquery/validation/methods_de.js' type='text/javascript'></script> 
<script type="text/javascript">
	$.validator.addMethod("groupnumber", function(value, element) {
		if(value.match(/^[1-9]{1}[0-9]{2}\.[0-9]{1}[0-9a-z]{1}$/)) return true;
		else return false;
	}, "Hodnota musí být ve formátu nnn.nn!");

	$.validator.addMethod("postalcode", function(value, element) {
		if(value.match(/^[1-9]{1}[0-9]{4}$/)) return true;
		else return false;
	}, "Hodnota musí být ve formátu nnnnn!");

	$(document).ready(function(){
		$("#registration").validate({
			submitHandler: function(form) {
				form.submit();
			},
			rules: {
				name: {
					required: true,
					maxlength: 20
				},
				surname: {
					required: true,
					maxlength: 30
				},
				nick: {
					required: true,
					maxlength: 20
				},
				email: {
					required: true,
					email: true,
					maxlength: 30
				},
				birthday: {
					required: true,
					date: true,
					dpDate: true
				},
				street: {
					required: true,
					maxlength: 30
				},
				city: {
					required: true,
					maxlength: 64
				},
				postal_code: {
					required: true,
					postalcode: true,
					minlength: 5,
					maxlength: 5
				},
				group_num: {
					required: true,
					groupnumber: true,
					maxlength: 6,
					minlength: 6
				},
				group_name: {
					required: true,
					maxlength: 50
				},
				troop_name: {
					maxlength: 50
				}
			},
			messages: {
				name:         "Jméno musí být vyplněno (max 20 znaků)!",
				surname:      "Příjmení musí být vyplněno (max 30 znaků)!",
				nick:         "Přezdívka musí být vyplněna (max 20 znaků)!",
				email:        "Zadejte validní e-mailovou adresu (max 30 znaků)!",
				birthday:     "Zadejte datum narození ve správném formátu!",
				street:       "Ulice musí být vyplněna (max 30 znaků)!",
				city:         "Město musí být vyplněno (max 64 znaků)!",
				postal_code:  "Zadejte PSČ ve správném formátu!",
				group_num:    "Zadejte číslo střediska/přístavu ve správném formátu!",
				group_name:   "Název střediska/přístavu musí být vyplněno (max 50 znaků)!",
				troop_name:   "Název oddílu nemůže být delší jak 50 znaků!"
			}
		});
	});
</script>

<!-- REGISTRACNI FORMULAR SRAZU -->

<form id="registration" action='' method='post'>
	<div class='button-line'>
		<button <?php echo $data['disabled']; ?> type='submit'>
			<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
		<button type='button' onClick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
			<img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
	</div>
	<div style="text-align:center;"><span class="required">*</span>Takto označené položky musí být vyplněné!</div>

	<table class='form'>
		<tr>
			<td class='label'><label><span class="required">*</span>Jméno:</label></td>
			<td><input id="name" <?php echo $data['disabled']; ?> type='text' name='name' size='30' value='<?php echo $data['name']; ?>' /></td>
		</tr>
		<tr>
			<td class='label'><label><span class="required">*</span>Příjmení:</label></td>
			<td><input id="surname" <?php echo $data['disabled']; ?> type="text" name='surname' size="30" value='<?php echo $data['surname']; ?>' /></td>
		</tr>
		<tr>
			<td class='label'><label><span class="required">*</span>Přezdívka:</label></td>
			<td><input id="nick" <?php echo $data['disabled']; ?> type='text' name='nick' size='30' value='<?php echo $data['nick']; ?>' /></td>
		</tr>
		<tr>
			<td class='label'><label><span class="required mail">*</span>E-mail:</label></td>
			<td><input id="email" <?php echo $data['disabled']; ?> type='email' name='email' size='30' value='<?php echo $data['email']; ?>' /></td>
		</tr>
		<tr>
			<td class='label'><label><span class="required">*</span>Datum narození:</label></td>
			<td><div class="picker"><input <?php echo $data['disabled']; ?> id="birthday" class="datePicker" type='text' name='birthday' size='30' value='<?php echo $data['birthday']; ?>' /></div> (datum ve formátu dd.mm.rrrr)</td>
		</tr>
		<tr>
			<td class='label'><label><span class="required">*</span>Ulice:</label></td>
			<td><input <?php echo $data['disabled']; ?> type='text' name='street' size='30' value='<?php echo $data['street']; ?>' /></td>
		</tr>
		<tr>
			<td class='label'><label><span class="required">*</span>Město:</label></td>
			<td><input <?php echo $data['disabled']; ?> type='text' name='city' size='30' value='<?php echo $data['city']; ?>' /></td>
		</tr>
		<tr>
			<td class='label'><label><span class="required">*</span>PSČ:</label></td>
			<td><input <?php echo $data['disabled']; ?> type='text' name='postal_code' size='10' value='<?php echo $data['postal_code']; ?>' /> (formát: 12345</td>
		</tr>
		<tr>
			<td></td>
			<td><div style="margin:2px 0px 2px 0px; font-weight:bold;">Junák - svaz skautů a skautek ČR</div></td>
		</tr>
		<tr>
			<td class='label'><label><span class="required">*</span>Číslo střediska/přístavu:</label></td>
			<td><input <?php echo $data['disabled']; ?> type='text' name='group_num' size='10' value='<?php echo $data['group_num']; ?>' /> (formát: 214[tečka]02)</td>
		</tr>
		<tr>
			<td class='label'><label><span class="required">*</span>Název střediska/přístavu:</label></td>
			<td>
				<input <?php echo $data['disabled']; ?> type='text' name='group_name' size='30' value='<?php echo $data['group_name']; ?>' /> (2. přístav Poutníci Kolín)
			</td>
		</tr>
		<tr>
			<td class='label'><label>Název oddílu:</label></td>
			<td><input <?php echo $data['disabled']; ?> type='text' name='troop_name' size='30' value='<?php echo $data['troop_name']; ?>' /> (22. oddíl Galeje)</td>
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
			<td colspan="2">Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) přijedete na místo srazu a v kolik hodin (přibližně) a jakým prostředkem sraz opustíte.
			</td>
		</tr>
		<tr>
			<td style="font-weight:bold;" colspan="2">Pokud máte volná místa v autě a jste ochotni někoho vzít, vyplňte prosím odkud jedete a kolik máte volných míst.
			</td>
		</tr>
		<tr>
			<td class='label'><label>Informace o příjezdu:</label></td>
			<td><textarea <?php echo $data['disabled']; ?> name='arrival' cols="50" rows="3" ><?php echo $data['arrival']; ?></textarea></td>
		</tr>
		<tr>
			<td class='label'><label>Informace o odjezdu:</label></td>
			<td><textarea <?php echo $data['disabled']; ?> name='departure' cols="50" rows="3" ><?php echo $data['departure']; ?></textarea></td>
		</tr>
		<tr>
			<td class='label'><label>Dotazy, přání, připomínky, stížnosti:</label></td>
			<td><textarea <?php echo $data['disabled']; ?> name='comment' cols="50" rows="8" ><?php echo $data['comment']; ?></textarea></td>
		</tr>
	<!-- <tr>
			<td style="font-weight:bold;" colspan="2">Máte nezodpovězené otázky ohledně Junáka a celé organizace nebo HKVS? Nerozumíte něčemu? Trápí vás to? Nevyznáte se v něčem? Potřebujete poradit? Zde napiště svoji otázku a my vám na ní odpovíme! Odpovědi najdete na jarním srazu nebo později na webu HKVS.
			</td>
		</tr>
		<tr>
			<td class='label'><label>Vaše otázka:</label></td>
			<td><textarea <?php echo $data['disabled']; ?> name='question' cols="50" rows="8" ><?php echo $data['question']; ?></textarea></td>
		</tr>-->
	</table>

	<input <?php echo $data['disabled']; ?> type='hidden' name='cms' value='<?php echo (isset($_GET['hash']) ? 'modify' : 'create'); ?>' />
	<input <?php echo $data['disabled']; ?> type='hidden' name='mid' value='<?php echo $data['mid'];  ?>' />
	<input <?php echo $data['disabled']; ?> type='hidden' name='id' value='<?php echo $data['id'];  ?>' />
	<input <?php echo $data['disabled']; ?> type="hidden" name="bill" value="0" />
	 
	<?php echo $data['programs']; ?>
	 
	<div class='button-line'>
		<button <?php echo $data['disabled']; ?> type='submit'>
			<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
		<button type='button' onClick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
			<img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
	</div>

</form>


<!-- REGISTRACNI FORMULAR SRAZU -->

<?php } else { ?>
<div>Registrace je uzavřena!</div>
<?php } ?>

				<p> </p>
				<p style="text-align: center;"> </p>
			</div>
		</div>
		<div class="cleaner"></div>
	</div>
</div>