<div class='siteContentRibbon'>Správa kategorií</div>
<div class='pageRibbon'><?php echo $data['heading']; ?></div>

<form action='<?php echo '../../srazvs/categories/process.php' ?>' method='post'>

	<div class='button-line'>
		<button type='submit' onclick=\"this.form.submit()\">
			<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit
		</button>
		<button type='button' onclick="window.location.replace('<?php echo "index.php" ?>')">
			<img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno
		</button>
	</div>

<!-- ColorMixer by David Grundl - LaTrine - http://latrine.dgx.cz/color-mixer-aneb-michatko -->
<script type="text/javascript" src="<?php echo JS_DIR ?>colormixer/dgxcolormixer_s.js"></script>
<script type="text/javascript">
function myChangeColorHandler(mixer){
    document.title = '#' + mixer.color.toRGB().toHEX();
}

function myConfirmHandler(mixer){
    var color = mixer.color.toRGB();
    // change input's background
    mixer.attachedInput.style.backgroundColor = '#' + color.toHEX();
    // make input's text color contrast
    mixer.attachedInput.style.color = color.brightness() > 128 ? 'black' : 'white';
}

// initialization
var mixer;
window.onload = function(){
    // create mixer
    mixer = new DGXColorMixer();
    // my handlers
    mixer.onChange = myChangeColorHandler;
    mixer.onConfirm = myConfirmHandler;
    // attach to input box, and set as popup window (second param)
    mixer.attachInput('color1', true);

    // create mixer
    mixer2 = new DGXColorMixer();
    // my handlers
    mixer2.onChange = myChangeColorHandler;
    mixer2.onConfirm = myConfirmHandler;
    // attach to input box, and set as popup window (second param)
    mixer2.attachInput('color2', true);
	
	// create mixer
    mixer3 = new DGXColorMixer();
    // my handlers
    mixer3.onChange = myChangeColorHandler;
    mixer3.onConfirm = myConfirmHandler;
    // attach to input box, and set as popup window (second param)
    mixer3.attachInput('color3', true);
}

//-->
</script>
	formát barvy: zadávejte hodnoty mezi #000000 (černá) a #FFFFFF (bílá);
	<table class='form'>
		<tr>
			<td class="label"><label class="required">Název kategorie:</label></td>
			<td><input type='text' name='name' size='32' value='<?php echo $data['name']; ?>' /></td>
		</tr>
		<tr>
			<td class="label"><label>Barva pozadí:</label></td>
			<td>
				<div class="picker">
					<input id="color1" type='text' size='32' name='bgcolor' value='<?php echo $data['bgcolor']; ?>' />
					<button class="colormixer" type="button" onclick="mixer.popup()"></button>
				</div>
			</td>
		</tr>
		<tr>
			<td class="label"><label>Barva ohraničení:</label></td>
			<td>
				<div class="picker">
					<input id="color2" type='text' size='32' name='bocolor' value='<?php echo $data['bocolor']; ?>' />
					<button class="colormixer" type="button" onclick="mixer2.popup()"></button>
				</div>
			</td>
		</tr> 
		<tr>
			<td class="label"><label>Barva písma:</label></td>
			<td>
				<div class="picker">
					<input id="color3" type='text' size='26' name='focolor' value='<?php echo $data['focolor']; ?>' />
					<button class="colormixer" type="button" onclick="mixer3.popup()"></button>
				</div>
			</td>
		</tr> 
	</table>
	<input type='hidden' name='cms' value='<?php echo $data['todo']; ?>'>
	<input type='hidden' name='id' value='<?php echo $data['id']; ?>'>
</form>