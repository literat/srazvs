<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

######################### KONTROLA ########################################

$id = requested("id","");
$mid = $_SESSION['meetingID'];
$cms = requested("cms","");
$page = requested("page","");

////inicializace promenych
$error = "";
$error_name = "";
$error_description = "";
$error_tutor = "";
$error_email = "";

$Container = new Container($GLOBALS['cfg'], $mid);
$BlocksHandler = $Container->createBlock();

######################## ZPRACOVANI ####################################

switch($cms) {
	case "new":
		$heading = "nový blok";
		$todo = "create";
		
		foreach($BlocksHandler->form_names as $key) {
				if($key == 'start_hour') $value = date("H");
				elseif($key == 'end_hour') $value = date("H")+1;
				elseif($key == 'start_minute') $value = date("i");
				elseif($key == 'end_minute') $value = date("i");
				elseif($key == 'program') $value = 0;
				elseif($key == 'display_progs') $value = 1;
				else $value = "";
				$$key = requested($key, $value);	
		}
		break;
	case "create":
		foreach($BlocksHandler->form_names as $key) {
				if($key == 'program') $$key = requested($key, 0);
				elseif($key == 'display_progs') $$key = requested($key, 1);
				else $$key = requested($key, null);	
		}
		
		$from = date("H:i:s",mktime($start_hour,$start_minute,0,0,0,0));
		$to = date("H:i:s",mktime($end_hour,$end_minute,0,0,0,0));
	
		//TODO: dodelat osetreni chyb
		if($from > $to) echo "chyba";
		else {
			foreach($BlocksHandler->DB_columns as $key) {
				$DB_data[$key] = $$key;	
			}
			$DB_data['from'] = $from;
			$DB_data['to'] = $to;
			$DB_data['capacity'] = 0;
			$DB_data['meeting'] = $mid;
		}
		
		if($BlocksHandler->create($DB_data)){	
			redirect("index.php?error=ok");
		}	
		break;
	case "edit":
		$heading = "úprava bloku";
		$todo = "modify";
		
		$query = "SELECT	name,
			   		DATE_FORMAT(`from`,'%H') AS start_hour,
			   		DATE_FORMAT(`to`,'%H') AS end_hour,
			   		DATE_FORMAT(`from`,'%i') AS start_minute,
			   		DATE_FORMAT(`to`,'%i') AS end_minute,
					day,
					program,
					display_progs,
					description,
					material,
					tutor,
					email,
					capacity,
					category
				FROM kk_blocks
				WHERE id='".$id."' AND deleted='0'
				LIMIT 1"; 
		$DB_data = mysql_fetch_assoc(mysql_query($query));
		
		foreach($BlocksHandler->form_names as $key) {
			$$key = requested($key, $DB_data[$key]);
		}
		
		break;
	case "modify":
		foreach($BlocksHandler->form_names as $key) {
				if($key == 'start_hour') $value = date("H");
				elseif($key == 'end_hour') $value = date("H")+1;
				elseif($key == 'start_minute') $value = date("i");
				elseif($key == 'end_minute') $value = date("i");
				elseif($key == 'program') $value = 0;
				elseif($key == 'display_progs') $value = 1;
				else $value = "";
				$$key = requested($key, $value);	
		}

		$from = date("H:i:s",mktime($start_hour,$start_minute,0,0,0,0));
		$to = date("H:i:s",mktime($end_hour,$end_minute,0,0,0,0));
		
		//TODO: dodelat osetreni chyb
		if($from > $to) echo "chyba";
		else {
			foreach($BlocksHandler->DB_columns as $key) {
				$DB_data[$key] = $$key;	
			}
			$DB_data['from'] = $from;
			$DB_data['to'] = $to;
			$DB_data['capacity'] = 0;
			$DB_data['meeting'] = $mid;
		}
		
		if($BlocksHandler->modify($id, $DB_data)){	
			redirect("../".$page."?error=ok");
		}
			
		break;
	case "mail":
		$pid = requested("pid","");
		$Container = new Container($GLOBALS['cfg']);
		$Emailer = $Container->createEmailer();
		if($Emailer->tutor($pid, $mid, "block")) {
			redirect("index.php?error=mail_send");
		}
		break;
}

######################## STATIC BOXES ##################################

$hours_array = array (0 => "00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23");
$minutes_array = array (00 => "00", 05 => "05", 10 => "10",15 => "15", 20 => "20",25 => "25", 30 => "30",35 => "35", 40 => "40", 45 => "45", 50 => "50", 55 => "55");

// category styles
$style = Category::getStyles();
// category select box
$cat_roll = Category::renderHtmlSelect($category);
// time select boxes
$day_roll = Form::renderHtmlSelectBox('day', array('pátek'=>'pátek', 'sobota'=>'sobota', 'neděle'=>'neděle'), $DB_data['day'], 'width:172px;');
$hour_roll = Form::renderHtmlSelectBox('start_hour', $hours_array, $start_hour);
$minute_roll = Form::renderHtmlSelectBox('start_minute', $minutes_array, $start_minute);
$end_hour_roll = Form::renderHtmlSelectBox('end_hour', $hours_array, $end_hour);
$end_minute_roll = Form::renderHtmlSelectBox('end_minute', $minutes_array, $end_minute);
// is program block check box
$program_checkbox = Form::renderHtmlCheckBox('program', 1, $program);
// display programs in block check box
$display_progs_checkbox = Form::renderHtmlCheckBox('display_progs', 0, $display_progs);

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

##################################################################

?>

<div class='siteContentRibbon'>Správa bloků</div>
<?php printError($error); ?>
<div class='pageRibbon'><?php echo $heading; ?></div>

<form action='process.php' method='post'>

<div class='button-line'>
	<button type='submit' onclick=\"this.form.submit()\">
		<img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
	<button type='button' onclick="window.location.replace('index.php')">
		<img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
 <?php
 if($cms == "edit") {
 ?>
	<button type='button' onclick="window.location.replace('process.php?cms=mail&pid=<?php echo $id; ?>')">
		<img src='<?php echo $ICODIR; ?>small/mail.png'  /> Odeslat lektorovi</button>
 <?php } ?>
</div>

<table class='form'>
	<tr>
		<td class='label'><label class="required">Název:</label></td>
		<td><input type='text' name='name' size='50' value='<?php echo $name; ?>' /><?php printError($error_name); ?></td>
	</tr>
	<tr>
		<td class='label'><label class="required">Den:</label></td>
		<td><?php echo $day_roll; ?></td>
	</tr>
 <tr>
  <td class='label'><label class="required">Od:</label></td>
  <td><?php echo $hour_roll." ".$minute_roll; ?></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Do:</label></td>
  <td><?php echo $end_hour_roll." ".$end_minute_roll; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Popis:</label></td>
  <td><textarea name='description' rows='10' cols="80"><?php echo $description; ?></textarea><?php printError($error_description); ?></td>
 </tr>
 <tr>
  <td class='label'><label>Lektor:</label></td>
  <td><input type='text' name='tutor' size='30' value='<?php echo $tutor; ?>' /><?php printError($error_tutor); ?></td>
 </tr>
 <tr>
  <td class='label'><label>E-mail:</label></td>
  <td><input type='text' name='email' size='30' value='<?php echo $email; ?>' /><?php printError($error_email); ?> (více mailů musí být odděleno čárkou)</td>
 </tr>
 <tr>
  <td class='label'><label>Programový blok:</label></td>
  <td><?php echo $program_checkbox; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Nezobrazovat programy:</label></td>
  <td><?php echo $display_progs_checkbox; ?></td>
 </tr>
 <tr>
  <td class="label"><label>Kategorie:</label></td>
  <td><?php echo $cat_roll; ?></td>
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