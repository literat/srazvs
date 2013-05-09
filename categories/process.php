<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA #########################

include_once(INC_DIR.'access.inc.php');

####################### KONTROLA ###################################

$mid = $_SESSION['meetingID'];
$id = requested("id","");
$cms = requested("cms","");

$Container = new Container($GLOBALS['cfg'], $mid);
$CategoryHandler = $Container->createCategory();

// styly jednotlivych kategorii
$style = $CategoryHandler->getStyles();

##################### ADD, MODIFY a DELETE #########################

switch($cms) {
	case "new":
		$heading = "nová kategorie";
		$todo = "create";
		
		foreach($CategoryHandler->DB_columns as $key) {
			$$key = requested($key, "");	
		}
		break;
	case "create":
		foreach($CategoryHandler->DB_columns as $key) {
			$DB_data[$key] = requested($key, "");	
		}
		
		if($CategoryHandler->create($DB_data)){	
			redirect("index.php?error=ok");
		}
		break;
	case "edit":
		$heading = "úprava kategorie";
		$todo = "modify";
		
		$query = "SELECT * FROM kk_categories WHERE id = ".$id." LIMIT 1"; 
		$DB_data = mysql_fetch_assoc(mysql_query($query));
		
		foreach($CategoryHandler->DB_columns as $key) {
			$$key = requested($key, $DB_data[$key]);	
		}
		break;
	case "modify":
		foreach($CategoryHandler->DB_columns as $key) {
			$DB_data[$key] = requested($key, "");	
		}
		
		if($CategoryHandler->modify($id, $DB_data)){
			redirect("index.php?error=ok");
		}
		break;
}

#################### GENEROVANI STRANKY ############################

include_once(INC_DIR.'http_header.inc.php');
include_once(INC_DIR.'header.inc.php');

############################ FORMULAR ###############################

?>

<div class='siteContentRibbon'>Správa kategorií</div>
<div class='pageRibbon'><?php echo $heading; ?></div>

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
			<td><input type='text' name='name' size='32' value='<?php echo $name; ?>' /></td>
		</tr>
		<tr>
			<td class="label"><label>Barva pozadí:</label></td>
			<td>
				<div class="picker">
					<input id="color1" type='text' size='32' name='bgcolor' value='<?php echo $bgcolor; ?>' />
					<button class="colormixer" type="button" onclick="mixer.popup()"></button>
				</div>
			</td>
		</tr>
		<tr>
			<td class="label"><label>Barva ohraničení:</label></td>
			<td>
				<div class="picker">
					<input id="color2" type='text' size='32' name='bocolor' value='<?php echo $bocolor; ?>' />
					<button class="colormixer" type="button" onclick="mixer2.popup()"></button>
				</div>
			</td>
		</tr> 
		<tr>
			<td class="label"><label>Barva písma:</label></td>
			<td>
				<div class="picker">
					<input id="color3" type='text' size='26' name='focolor' value='<?php echo $focolor; ?>' />
					<button class="colormixer" type="button" onclick="mixer3.popup()"></button>
				</div>
			</td>
		</tr> 
	</table>
	<input type='hidden' name='cms' value='<?php echo $todo; ?>'>
	<input type='hidden' name='id' value='<?php echo $id; ?>'>
</form>

<?php
###################################################################

include_once(INC_DIR.'footer.inc.php');

###################################################################
?>