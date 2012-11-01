<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

######################### KONTROLA ########################################

$mid = requested("mid","");
$cms = requested("cms","");

$name = requested("name","");
$surname = requested("surname","");
$nick = requested("nick","");
$birthday = requested("birthday","");
$street = requested("street","");
$city = requested("city","");
$postal_code = requested("postal_code","");
$province = requested("province","");
$meal = requested("meal","ne");
$group_num = requested("group_num","");
$group_name = requested("group_name","");
$troop_name = requested("troop_name","");
$bill = requested("bill",0);
$email = requested("email","");
$comment = requested("comment","");
$arrival = requested("arrival","");
$departure = requested("departure","");
$question = requested("question","");

$fry_dinner = requested("fry_dinner","");
$sat_breakfast = requested("sat_breakfast","");
$sat_lunch = requested("sat_lunch","");
$sat_dinner = requested("sat_dinner","");
$sun_breakfast = requested("sun_breakfast","");
$sun_lunch = requested("sun_lunch","");

////inicializace promenych
$error = "";
$error_name = "";
$error_surname = "";
$error_postal_code = "";
$error_email = "";
$error_group_num = "";
$error_bill = "";

########################## ROLLS ####################################  
$province_roll = "<select style='width: 195px; font-size: 10px' name='province'>\n";

$provinceSql = "SELECT	*
				FROM kk_provinces";
$provinceResult = mysql_query($provinceSql);

while($provinceData = mysql_fetch_assoc($provinceResult)){
	if($provinceData['id'] == $province){
		$sel = "selected";
	}
	else $sel = "";
	$province_roll .= "<option value='".$provinceData['id']."' ".$sel.">".$provinceData['province_name']."</option>";
}

$province_roll .= "</select>\n";

////meal roll
$meal_array = array("ano" => "ano","ne" => "ne");
$mealDayArray = array("páteční večeře"=>"fry_dinner",
					  "sobotní snídaně"=>"sat_breakfast",
					  "sobotní oběd"=>"sat_lunch",
					  "sobotní večeře"=>"sat_dinner",
					  "nedělní snídaně"=>"sun_breakfast",
					  "nedělní oběd"=>"sun_lunch");

$meal_roll = "";
foreach($mealDayArray as $mealDayKey => $mealDayVal){

	$meal_roll .= "<span style='display:block;font-size:11px;'>".$mealDayKey.":</span><select style='width:195px; font-size:11px;margin-left:5px;' name='".$mealDayVal."'>\n";
	foreach ($meal_array as $meal_key => $v2){
		if($meal_key == "ne"){
			$sel2 = "selected";
		}
		else $sel2 = "";
		$meal_roll .= "<option value='".$meal_key."' ".$sel2.">".$v2."</option>";
	}
	$meal_roll .= "</select><br />\n";
}

############################## ZPRACOVANI ####################################

if($cms == "create"){
	$sql = "INSERT	INTO `kk_visitors` (`name`, `surname`, `nick`, `birthday`, `email`, `street`, `city`, `postal_code`, `province`, `group_num`, `group_name`, `troop_name`, `comment`, `arrival`, `departure`, `bill`, `meeting`, `question`) 
			VALUES ('".$name."', '".$surname."', '".$nick."', '".$birthday."', '".$email."', '".$street."', '".$city."', '".		$postal_code."', '".$province."', '".$group_num."', '".$group_name."', '".$troop_name."', '".$comment."', '".$arrival."', '".$departure."', '".$bill."', '".$mid."', '".$question."')";
	$result = mysql_query($sql);
	$vid = mysql_insert_id();
	if(!$result) $error = "error";
	else {$error = "ok";
		$blockSql = "SELECT 	id
					 FROM kk_blocks
					 WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
		$blockResult = mysql_query($blockSql);
		while($blockData = mysql_fetch_assoc($blockResult)){
			$usrProgSql = "INSERT INTO `kk_visitor-program` (`visitor`, `program`)
						   VALUES ('".$vid."', '".$$blockData['id']."')";
			$usrProgResult = mysql_query($usrProgSql);
		}
			
		$mealSql = "INSERT	INTO `kk_meals` (`visitor`, `fry_dinner`, `sat_breakfast`, `sat_lunch`, `sat_dinner`, `sun_breakfast`, `sun_lunch`) 
			VALUES ('".$vid."', '".$fry_dinner."', '".$sat_breakfast."', '".$sat_lunch."', '".$sat_dinner."', '".$sun_breakfast."', '".$sun_lunch."')";
		$mealResult = mysql_query($mealSql);
		
		redirect("index.php?error=".$error."&mid=".$mid."");
	}
}

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

##################################################################

?>
<link href="../styles/css/default.css" rel="stylesheet" type="text/css" />

<div class='siteContentRibbon'>Správa účastníků</div>
<?php printError($error); ?>
<div class='pageRibbon'>nový účastník</div>

<form action='create.php' method='post'>

<div class='button-line'>
 <button type='submit' onClick=\"this.form.submit()\">
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
  <td><?php echo $province_roll; ?></td>
 </tr>
 <tr>
  <td class='label'><label>Stravování:</label></td>
  <td><?php echo $meal_roll; ?></td>
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

<div class='button-line'>
 <button type='submit' onClick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('index.php')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>

 <input type='hidden' name='cms' value='create'>
 <input type='hidden' name='mid' value='<?php echo $_SESSION['meetingID']; ?>'>
</form>

<?php
###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>