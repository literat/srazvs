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
$error_material = "";

$ProgramHandler = new Program($mid);

######################## ZPRACOVANI ####################################

switch($cms) {
	case "new":
		$heading = "nový program";
		$todo = "create";
		
		foreach($ProgramHandler->form_names as $key) {
				if($key == 'display_in_reg') $value = 1;
				else $value = "";
				$$key = requested($key, $value);	
		}
		break;
	case "create":
		foreach($ProgramHandler->form_names as $key) {
				if($key == 'display_in_reg') $$key = requested($key, 0);
				else $$key = requested($key, null);	
		}
	
		foreach($ProgramHandler->DB_columns as $key) {
			$DB_data[$key] = $$key;	
		}

		if($ProgramHandler->create($DB_data)){	
			redirect("index.php?error=ok");
		}
		break;
	case "edit":
		$heading = "úprava programu";
		$todo = "modify";
		
		$query = "SELECT	*
					FROM kk_programs
					WHERE id='".$id."' AND deleted='0'
					LIMIT 1"; 
		$DB_data = mysql_fetch_assoc(mysql_query($query));
		
		foreach($ProgramHandler->form_names as $key) {
			$$key = requested($key, $DB_data[$key]);
		}
		
		break;
	case "modify":
		foreach($ProgramHandler->form_names as $key) {
				if($key == 'display_in_reg' && $$key == '') $value = 1;
				$$key = requested($key, $value);
		}
		
		foreach($ProgramHandler->DB_columns as $key) {
			$DB_data[$key] = $$key;	
		}
		
		if($ProgramHandler->modify($id, $DB_data)){	
			redirect("../".$page."?error=ok");
		}
			
		break;
	case "mail":
		$pid = requested("pid","");
    	$Container = new Container($GLOBALS['cfg']);
		$Emailer = $Container->createEmailer();
		if($Emailer->tutor($pid, $mid, "program")) {
			redirect("index.php?error=mail_send");
		}
		break;
}

######################## STATIC BOXES ##################################

// category styles
$style = Category::getStyles();
// category select box
$cat_roll = Category::renderHtmlSelect($category);
// blocks select box
$block_roll = Blocks::renderHtmlSelect($block);
// display in registration check box
$display_in_reg_checkbox = Form::renderHtmlCheckBox('display_in_reg', 0, $display_in_reg);

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

##################################################################

?>

<div class='siteContentRibbon'>Správa programů</div>
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
  <td><input type='text' name='name' size='30' value='<?php echo $name; ?>' /><?php printError($error_name); ?></td>
 </tr>
 <tr>
  <td class='label'><label>Popis:</label></td>
  <td><textarea name='description' rows='10' cols="80"><?php echo $description; ?></textarea><?php printError($error_description); ?></td>
 </tr>
 <tr>
  <td class='label'><label>Materiál:</label></td>
  <td><textarea name='material' rows='2' cols="80"><?php echo $material; ?></textarea><?php printError($error_material); ?></td>
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
  <td class='label'><label>Kapacita:</label></td>
  <td><input type='text' name='capacity' size='10' value='<?php echo $capacity; ?>' /> (omezeno na 255)</td>
 </tr>
 <tr>
  <td class='label'><label>Nezobrazovat v registraci:</label></td>
  <td><?php echo $display_in_reg_checkbox; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Blok:</label></td>
  <td><?php echo $block_roll; ?></td>
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
################################ VISITORS ###################################

$visitors = "  <div style='border-bottom:1px solid black;text-align:right;'>účastníci</div>";

$visitSql = "SELECT vis.name AS name,
					vis.surname AS surname,
					vis.nick AS nick
			FROM kk_visitors AS vis
			LEFT JOIN `kk_visitor-program` AS visprog ON vis.id = visprog.visitor
			WHERE visprog.program = '".$id."' AND vis.deleted = '0'";
$visitResult = mysql_query($visitSql);
$i = 1;
while($visitData = mysql_fetch_assoc($visitResult)){
	$visitors .= $i.". ".$visitData['name']." ".$visitData['surname']." - ".$visitData['nick']."<br />";
	$i++;
}
echo $visitors;

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>