<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

################################ SQL, KONTROLA #############################

$ID_meeting = $_SESSION['meetingID'];
$id = requested("id","");
$meal_value = array();
$page = requested("page","");

$Container = new Container($GLOBALS['cfg'], $ID_meeting);
$VisitorsHandler = $Container->createVisitor();

// TODO
////ziskani zvolenych programu
$blockSql = "SELECT 	id
			 FROM kk_blocks
			 WHERE meeting='".$ID_meeting."' AND program='1' AND deleted='0'";
$blockResult = mysql_query($blockSql);
while($blockData = mysql_fetch_assoc($blockResult)){
	$$blockData['id'] = requested($blockData['id'],0);
	$programs_data[$blockData['id']] = $$blockData['id'];
	//echo $blockData['id'].":".$$blockData['id']."|";
}
// TODO

######################### KONTROLA ########################################

$cms = requested("cms","");
$meeting = requested("meeting","");
$redir = requested("redir","");
$disabled = requested("disabled","");

////inicializace promenych
$error = "";
$error_name = "";
$error_surname = "";
$error_postal_code = "";
$error_email = "";
$error_group_num = "";
$error_bill = "";

######################## ZPRACOVANI ####################################

switch($cms) {
	/* new visitor */
	case "new":
		$heading = "nový účastník";
		$todo = "create";
		
		// requested for meals
		foreach($VisitorsHandler->Meals->day_meal as $key => $value) {
			$meal_value[$value] = requested($value, "ne");	
		}
	
		// requested for visitors fields
		foreach($VisitorsHandler->DB_columns as $key) {
			if($key == 'bill') $value = 0;
			else $value = "";
			$$key = requested($key, $value);	
		}
		break;
	/* process creation of new visitor */
	case "create":
		// requested for visitors
		foreach($VisitorsHandler->DB_columns as $key) {
				if($key == 'bill') $$key = requested($key, 0);
				else $$key = requested($key, null);
				$DB_data[$key] = $$key;	
		}
		// requested for meals
		foreach($VisitorsHandler->Meals->DB_columns as $var_name) {
			$$var_name = requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}
		// create
		if($VisitorsHandler->create($DB_data, $meals_data, $programs_data)){	
			redirect("../".$page."?error=ok");
		}
		break;
	/* edit visitor*/
	case "edit":
		$heading = "úprava programu";
		$todo = "modify";
		// get visitor's data
		$query = "SELECT	*
					FROM kk_visitors
					WHERE id='".$id."' AND deleted='0'
					LIMIT 1"; 
		$DB_data = mysql_fetch_assoc(mysql_query($query));
		
		foreach($VisitorsHandler->DB_columns as $key) {
			$$key = requested($key, $DB_data[$key]);
		}
		// get meal's data
		$query = "SELECT	*
					FROM kk_meals
					WHERE visitor='".$id."'
					LIMIT 1"; 
		$DB_data = mysql_fetch_assoc(mysql_query($query));
		
		foreach($VisitorsHandler->Meals->DB_columns as $var_name) {
			$$var_name = requested($var_name, $DB_data[$var_name]);
			$meals_data[$var_name] = $$var_name;
		}
		
		break;
	/* process updating information about visitor */
	case "modify":
		foreach($VisitorsHandler->DB_columns as $key) {
				if($key == 'bill') $$key = requested($key, 0);
				else $$key = requested($key, null);
				$DB_data[$key] = $$key;	
		}

		foreach($VisitorsHandler->Meals->DB_columns as $var_name) {
			$$var_name = requested($var_name, null);
			$meals_data[$var_name] = $$var_name;
		}
		// i must add visitor's ID because it is empty
		$meals_data['visitor'] = $id;
		
		if($VisitorsHandler->modify($id, $DB_data, $meals_data, $programs_data)){	
			redirect("../".$page."?error=ok");
		}	
		break;
	/** not usefull yet **
	case "mail":
		$pid = requested("pid","");
    	$Container = new Container($GLOBALS['cfg']);
		$Emailer = $Container->createEmailer();
		if($Emailer->tutor($pid, $mid, "program")) {
			redirect("index.php?error=mail_send");
		}
		break;
	*/
}

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

###################################################################

?>
<link href="../styles/css/default.css" rel="stylesheet" type="text/css" />

<div class='siteContentRibbon'>Správa účastníků</div>
<?php printError($error); ?>
<div class='pageRibbon'><?php echo $heading; ?></div>

<form action='process.php' method='post'>

<div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('index.php')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>

<!-- Datedit by Ivo Skalicky - ITPro CZ - http://www.itpro.cz -->
<script type="text/javascript" charset="iso-8859-1" src="<?php echo $AJAXDIR; ?>datedit/datedit.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo $AJAXDIR; ?>datedit/lang/cz.js"></script>
<script type="text/javascript">
  <?php
  //jak prekonvertovat pomoci datedit datum pro sql databazi
  //datedit("start_date","dd.mm.yyyy",true,"yyyy-mm-dd");
  ?>
  datedit("birthday","yyyy-mm-dd");
</script> 

<table class='form'>
 <tr>
  <td class='label'><label class="required">Jméno:</label></td>
  <td><input type='text' name='name' size='30' value='<?php echo $name; ?>' /><?php printError($error_name); ?></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Příjmení:</label></td>
  <td><input type="text" name='surname' size="30" value='<?php echo $surname; ?>' /><?php printError($error_surname); ?></td>
 </tr>
 <tr>
  <td class='label'><label>Přezdívka:</label></td>
  <td><input type='text' name='nick' size='30' value='<?php echo $nick; ?>' /></td>
 </tr>
 <tr>
  <td class='label'><label class="required">E-mail:</label></td>
  <td><input type='text' name='email' size='30' value='<?php echo $email; ?>' /><?php printError($error_email); ?></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Datum narození:</label></td>
  <td><div class="picker"><input id="birthday" type='text' name='birthday' size='30' value='<?php echo $birthday; ?>' /></div> (datum ve formátu rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Ulice:</label></td>
  <td><input type='text' name='street' size='30' value='<?php echo $street; ?>' /></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Město:</label></td>
  <td><input type='text' name='city' size='30' value='<?php echo $city; ?>' /></td>
 </tr>
 <tr>
  <td class='label'><label class="required">PSČ:</label></td>
  <td><input type='text' name='postal_code' size='10' value='<?php echo $postal_code; ?>' /> (formát: 12345)<?php printError($error_postal_code); ?></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Číslo střediska/přístavu:</label></td>
  <td><input type='text' name='group_num' size='10' value='<?php echo $group_num; ?>' /> (formát: 214[tečka]02)<?php printError($error_group_num); ?></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Název střediska/přístavu:</label></td>
  <td><div style="margin:2px 0px 2px 0px; font-weight:bold;">Junák - svaz skautů a skautek ČR</div>
   <input type='text' name='group_name' size='30' value='<?php echo $group_name; ?>' /> (2. přístav Poutníci Kolín)
  </td>
 </tr>
 <tr>
  <td class='label'><label>Název oddílu:</label></td>
  <td><input type='text' name='troop_name' size='30' value='<?php echo $troop_name; ?>' /> (22. oddíl Galeje)</td>
 </tr>
 <tr>
  <td class='label'><label>Kraj:</label></td>
  <td><?php echo $VisitorsHandler->Meeting->renderHtmlProvinceSelect($province); ?></td>
 </tr>
  <tr>
  <td class='label'><label>Stravování:</label></td>
  <td><?php echo $VisitorsHandler->Meals->renderHtmlMealsSelect($meals_data, $disabled); ?></td>
 </tr>
 <tr>
  <td class='label'><label>Informace o příjezdu:</label></td>
  <td><textarea name='arrival' cols="50" rows="3" ><?php echo $arrival; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Informace o odjezdu:</label></td>
  <td><textarea name='departure' cols="50" rows="3" ><?php echo $departure; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Dotazy, přání, připomínky, stížnosti:</label></td>
  <td><textarea name='comment' cols="50" rows="8" ><?php echo $comment; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Vaše otázka:</label></td>
  <td><textarea name='question' cols="50" rows="8" ><?php echo $question; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Zaplaceno:</label></td>
  <td><input type='text' name='bill' size='15' value='<?php echo $bill; ?>' /> ,- Kč <?php printError($error_bill); ?></td>
 </tr>
</table>

<div style='border-bottom:1px solid black;text-align:right;'>výběr programů</div>

<?php echo $VisitorsHandler->renderProgramSwitcher($ID_meeting, $id); ?>

 <input type='hidden' name='meeting' value='<?php echo $_SESSION['meetingID']; ?>'>
 <input type='hidden' name='id' value='<?php echo $id; ?>'>
 <input type='hidden' name='cms' value='<?php echo $todo; ?>'>
 <input type='hidden' name='page' value='<?php echo $page; ?>'>
 
 <div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('index.php')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>
 
</form>

<?php
###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>