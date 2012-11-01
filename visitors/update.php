<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

################################ SQL, KONTROLA #############################

$mid = $_SESSION['meetingID'];
$id = requested("id","");

$sql = "SELECT	vis.id AS id,
				name,
				surname,
				nick,
				DATE_FORMAT(birthday, '%Y-%m-%d') AS birthday,
				street,
				city,
				postal_code,
				province,
				province_name,
				group_num,
				group_name,
				troop_name,
				email,
				comment,
				arrival,
				departure,
				question,
				fry_dinner,
				sat_breakfast,
				sat_lunch,
				sat_dinner,
				sun_breakfast,
				sun_lunch,
				bill
		FROM kk_visitors AS vis
		LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
		LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
		WHERE vis.id='".$id."' AND meeting='".$mid."' AND vis.deleted='0'
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

////ziskani zvolenych programu
$blockSql = "SELECT 	id
			 FROM kk_blocks
			 WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
$blockResult = mysql_query($blockSql);
while($blockData = mysql_fetch_assoc($blockResult)){
	$$blockData['id'] = requested($blockData['id'],0);
	//echo $blockData['id'].":".$$blockData['id']."|";
}

################################ FUNKCE ###################################

function getPrograms($id, $vid){
	$sql = "SELECT 	*
			FROM kk_programs
			WHERE block='".$id."' AND deleted='0'
			LIMIT 10";
	$result = mysql_query($sql);
	$rows = mysql_affected_rows();

	if($rows == 0){
		$html = "";
	}
	else{
		/*$progSql = "SELECT progs.name AS prog_name
					FROM kk_programs AS progs
					LEFT JOIN `kk_visitor-program` AS visprog ON progs.id = visprog.program
					LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
					WHERE vis.id = '".$id."'";
			$progResult = mysql_query($progSql);*/


		$html = "<div>\n";
		$html .= "<input checked type='radio' name='".$id."' value='0' /> Nebudu přítomen <br />\n";
		while($data = mysql_fetch_assoc($result)){
			//// resim kapacitu programu a jeho naplneni navstevniky
			$fullProgramSql = " SELECT COUNT(visitor) AS visitors FROM `kk_visitor-program` AS visprog
								LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
								WHERE program = '".$data['id']."' AND vis.deleted = '0'";
			$fullProgramResult = mysql_query($fullProgramSql);
			$fullProgramData = mysql_fetch_assoc($fullProgramResult);
			
			$programSql = "SELECT * FROM `kk_visitor-program` WHERE program = '".$data['id']."' AND visitor = '".$vid."'";
			$programResult = mysql_query($programSql);
			$rows = mysql_affected_rows();
			if($rows == 1) $checked = "checked";
			else $checked = "";
			//$programData = mysql_fetch_assoc($programResult);
		
			if($fullProgramData['visitors'] >= $data['capacity']){
				$html .= "<input ".$checked." disabled type='radio' name='".$id."' value='".$data['id']."' />\n";
				$fullProgramInfo = " (NELZE ZAPSAT - kapacita programu je již naplněna!)";
			}
			else {
				$html .= "<input ".$checked." type='radio' name='".$id."' value='".$data['id']."' /> \n";
				$fullProgramInfo = "";
			}
			$html .= $data['name'];
			$html .= $fullProgramInfo;
			$html .= "<br />\n";
		}
		$html .= "</div>\n";
	}
	return $html;
}

################################# PROGRAMY ################################

$programs = "  <div style='border-bottom:1px solid black;text-align:right;'>výběr programů</div>";

$progSql = "SELECT 	id,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`,
					name,
					program
			FROM kk_blocks
			WHERE deleted = '0' AND program='1' AND meeting='".$mid."'
			ORDER BY `day` ASC";

$progResult = mysql_query($progSql);
$progRows = mysql_affected_rows();

if($progRows == 0){
	$programs .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
}
else{	
	while($progData = mysql_fetch_assoc($progResult)){
		$programs .= "<div>".$progData['day'].", ".$progData['from']." - ".$progData['to']." : ".$progData['name']."</div>\n";
		if($progData['program'] == 1) $programs .= "<div>".getPrograms($progData['id'], $data['id'])."</div>";
		$programs .= "<br />";
	}
}

######################### KONTROLA ########################################



$cms = requested("cms","");
$redir = requested("redir","");

$name = requested("name",$data['name']);
$surname = requested("surname",$data['surname']);
$nick = requested("nick",$data['nick']);
$birthday = requested("birthday",$data['birthday']);
$street = requested("street",$data['street']);
$city = requested("city",$data['city']);
$postal_code = requested("postal_code",$data['postal_code']);
$province = requested("province",$data['province']);
$group_num = requested("group_num",$data['group_num']);
$group_name = requested("group_name",$data['group_name']);
$troop_name = requested("troop_name",$data['troop_name']);
$bill = requested("bill",$data['bill']);
$email = requested("email",$data['email']);
$comment = requested("comment",$data['comment']);
$arrival = requested("arrival",$data['arrival']);
$departure = requested("departure",$data['departure']);
$question = requested("question",$data['question']);

$fry_dinner = requested("fry_dinner",$data['fry_dinner']);
$sat_breakfast = requested("sat_breakfast",$data['sat_breakfast']);
$sat_lunch = requested("sat_lunch",$data['sat_lunch']);
$sat_dinner = requested("sat_dinner",$data['sat_dinner']);
$sun_breakfast = requested("sun_breakfast",$data['sun_breakfast']);
$sun_lunch = requested("sun_lunch",$data['sun_lunch']);

$disabled = requested("disabled","");

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

######################## ZPRACOVANI ####################################
//prihlasovaci udaje
if($cms == "update"){
		$sql = "UPDATE `kk_visitors`
				SET `name` = '".$name."',
					`surname` = '".$surname."', 
					`nick` = '".$nick."', 
					`birthday` = '".$birthday."', 
					`street` = '".$street."', 
					`city` = '".$city."', 
					`postal_code` = '".$postal_code."', 
					`province` = '".$province."', 
					`group_num` = '".$group_num."', 
					`group_name` = '".$group_name."', 
					`troop_name` = '".$troop_name."',
					`bill` = '".$bill."', 
					`email` = '".$email."', 
					`comment` = '".$comment."',
					`arrival` = '".$arrival."',
					`departure` = '".$departure."',
					`question` = '".$question."'
				WHERE id = '".$id."'
				LIMIT 1";
		$result = mysql_query($sql);
		
		//zmena jidla
		$mealSql = "UPDATE `kk_meals` SET `fry_dinner` = '".$fry_dinner."', `sat_breakfast` = '".$sat_breakfast."', `sat_lunch` = '".$sat_lunch."', `sat_dinner` = '".$sat_dinner."', `sun_breakfast` = '".$sun_breakfast."', `sun_lunch` = '".$sun_lunch."' WHERE `visitor` = '".$id."' LIMIT 1";
		$mealResult = mysql_query($mealSql);
		
		/* zmena programu */
		//$result = true;
		if(!$result){
			$error = "error";
		}
		else {
			$error = "ok";
				
			$blockSql = "SELECT 	id
		 				 FROM kk_blocks
		 				 WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
			$blockResult = mysql_query($blockSql);
			
			$usrOldProgSql = "SELECT id, program FROM `kk_visitor-program` WHERE `visitor` = ".$data['id']."";
			$usrOldProgResult = mysql_query($usrOldProgSql);
			
			//$delProgSql = "DELETE FROM `kk_visitor-program` WHERE visitor = '".$data['id']."'";
			//$delProgResult = mysql_query($delProgSql);
			
			while($blockData = mysql_fetch_assoc($blockResult) and $usrOldProgData = mysql_fetch_assoc($usrOldProgResult)){
				//echo "ahoj";
				//echo "ID:".$usrOldProgData['id']." old:".$usrOldProgData['id']." new:".$$blockData['id']."<br />";
				//die();
				$usrProgSql = "	UPDATE `kk_visitor-program` 
								SET `program` = ".$$blockData['id']." 
								WHERE visitor = ".$data['id']." AND id = ".$usrOldProgData['id'].";";
				/*$usrProgSql = "INSERT INTO `kk_visitor-program` (`visitor`, `program`)
							   VALUES ('".$data['id']."', '".$$blockData['id']."');";*/
				$usrProgResult = mysql_query($usrProgSql);
			}
			redirect("index.php?error=".$error."&mid=".$mid."");
		}
};

////meal roll
// poradi mus byt nejdrive NE a potom ANO, podle toho, co chci, aby se defaultne zobrazilo ve formulari
$meal_array = array("ne" => "ne","ano" => "ano");
$mealDayArray = array("páteční večeře"=>"fry_dinner",
					  "sobotní snídaně"=>"sat_breakfast",
					  "sobotní oběd"=>"sat_lunch",
					  "sobotní večeře"=>"sat_dinner",
					  "nedělní snídaně"=>"sun_breakfast",
					  "nedělní oběd"=>"sun_lunch");

$meal_roll = "";
foreach($mealDayArray as $mealDayKey => $mealDayVal){
	if(preg_match("/breakfast/", $mealDayVal)) $mealIcon = "breakfast";
	if(preg_match("/lunch/", $mealDayVal)) $mealIcon = "lunch";
	if(preg_match("/dinner/", $mealDayVal)) $mealIcon = "dinner";
	
	$meal_roll .= "<span style='display:block;font-size:11px;'>".$mealDayKey.":</span><img style='width:18px;' src='".$ICODIR."small/".$mealIcon.".png' /><select ".$disabled." style='width:195px; font-size:11px;margin-left:5px;' name='".$mealDayVal."'>\n";
	foreach ($meal_array as $meal_key => $v2){
		if($meal_key == $$mealDayVal){
			$sel2 = "selected";
		}
		else $sel2 = "";
		$meal_roll .= "<option value='".$meal_key."' ".$sel2.">".$v2."</option>";
	}
	$meal_roll .= "</select><br />\n";
}

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

##################################################################

?>
<link href="../styles/css/default.css" rel="stylesheet" type="text/css" />

<div class='siteContentRibbon'>Správa účastníků</div>
<?php printError($error); ?>
<div class='pageRibbon'>úprava účastníka</div>

<form action='update.php' method='post'>

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
  <td><?php echo $province_roll; ?></td>
 </tr>
<!-- <tr>
  <td class='label'><label>Stravování:</label></td>
  <td>
   <div>páteční večeře: <span style="font-weight:bold;"><?php echo $fry_dinner; ?></span></div>
   <div>sobotní snídaně: <span style="font-weight:bold;"><?php echo $sat_breakfast; ?></span></div>
   <div>sobotní oběd: <span style="font-weight:bold;"><?php echo $sat_lunch; ?></span></div>
   <div>sobotní večeře: <span style="font-weight:bold;"><?php echo $sat_dinner; ?></span></div>
   <div>nedělní snídaně: <span style="font-weight:bold;"><?php echo $sun_breakfast; ?></span></div>
   <div>nedělní oběd: <span style="font-weight:bold;"><?php echo $sun_lunch; ?></span></div>
  </td>
 </tr>-->
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

<?php echo $programs; ?>

 <input type='hidden' name='cms' value='update'>
 <input type='hidden' name='mid' value='<?php echo $_SESSION['meetingID']; ?>'>
 <input type='hidden' name='id' value='<?php echo $id; ?>'>
 
 <div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('index.php')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>
 
</form>

<?php
################################ PROGRAMY ###################################

//$programs = "  <div style='border-bottom:1px solid black;text-align:right;'>výběr programů</div>";


//echo $programs;

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>