<?php

//header
require_once('../inc/define.inc.php');

########################### AKTUALNI SRAZ ##############################

if(defined('DEBUG') && DEBUG === TRUE){
  $mid = 1;
  $disabled = "";
  $where = "WHERE id = ".$mid;  
} else {
  $where = "";  
}


$sql = "SELECT  id,
        place,
        DATE_FORMAT(start_date, '%Y') AS year,
        UNIX_TIMESTAMP(open_reg) AS open_reg,
        UNIX_TIMESTAMP(close_reg) as close_reg
    FROM kk_meetings
    ".$where."
    ORDER BY id DESC
    LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

$mid = $data['id'];
$meetingHeader = $data['place']." ".$data['year'];



////otevirani a uzavirani prihlasovani
if(($data['open_reg'] < time()) && (time() < $data['close_reg']) || DEBUG === TRUE){
  $disabled = "";
  $display_registration = TRUE;
} else {
  $disabled = "disabled";
  $display_registration = FALSE;
}


$Container = new Container($GLOBALS['cfg'], $mid);
$ProgramHandler = $Container->createProgram();

################################ FUNKCE ###################################

################################# PROGRAMY ################################

$programs = "  <div style='border-bottom:1px solid black;text-align:right;'>výběr programů</div>";
$programs .= "  <p>info: Rozkliknutím programu zobrazíte jeho detail.</p>";

$progSql = "SELECT  id,
          day,
          DATE_FORMAT(`from`, '%H:%i') AS `from`,
          DATE_FORMAT(`to`, '%H:%i') AS `to`,
          name,
          program
      FROM kk_blocks
      WHERE deleted = '0' AND program='1' AND meeting='".$mid."'
      ORDER BY `day`, `from` ASC";

$progResult = mysql_query($progSql);
$progRows = mysql_affected_rows();

if($progRows == 0){
  $programs .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
}
else{
  //// prasarnicka kvuli programu raftu - resim obsazenost dohromady u dvou polozek
  //$raftCountSql = "SELECT COUNT(visitor) AS raft FROM `kk_visitor-program` WHERE program='56|57'";
  //$raftCountResult = mysql_query($raftCountSql);
  //$raftCountData = mysql_fetch_assoc($raftCountResult);
  
  while($progData = mysql_fetch_assoc($progResult)){
    //nemoznost volit predsnemovni dikusi
    if($progData['id'] == 63) $notDisplayed = "style='display:none;'";
    //obsazenost raftu
    //elseif($raftCountData['raft'] >= 25){
    //  if($progData['id'] == 86) $notDisplayed = "style='display:none;'";
    //  else $notDisplayed = "";
    //}
    else $notDisplayed = "";
    $programs .= "<div ".$notDisplayed.">".$progData['day'].", ".$progData['from']." - ".$progData['to']." : ".$progData['name']."</div>\n";
    if($progData['program'] == 1) $programs .= "<div ".$notDisplayed.">".$ProgramHandler->getProgramsRegistration($progData['id'], $disabled)."</div>";
    $programs .= "<br />";
  }
}

######################### KONTROLA ########################################

////inicializace promenych
$error = "";
$error_name = "";
$error_surname = "";
$error_nick = "";
$error_postal_code = "";
$error_email = "";
$error_street = "";
$error_city = "";
$error_group_num = "";
$error_group_name = "";
$error_birthday = "";
$error_bill = "";

//$mid = requested("mid","");
$cms = requested("cms","");
//$error = requested("error","");

////ziskani zvolenych programu
$blockSql = "SELECT   id
       FROM kk_blocks
       WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
$blockResult = mysql_query($blockSql);
while($blockData = mysql_fetch_assoc($blockResult)){
  $$blockData['id'] = requested($blockData['id'],0);
  //echo $blockData['id'].":".$$blockData['id']."|";
}

$name = requested("name","");
$surname = requested("surname","");
$nick = requested("nick","");
$birthday = requested("birthday","");
$street = requested("street","");
$city = requested("city","");
$postal_code = requested("postal_code","");
$province = requested("province","");
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

######################## ZPRACOVANI ####################################

if($cms == "create"){
  $birthday = cleardate2DB($birthday, "Y-m-d");
  
  $error = "";
  //kontrola PSC  
  if(!isZipCode($postal_code)){
    $error = "error";
    $error_postal_code = "zip_code";
  }
  
  //kontrola e-mailu
  //if(!isEmail($email)){
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "error";
    $error_email = "email";
  }
  
  //kontrola e-mailu
  if(!isGroupNumber($group_num)){
    $error = "error";
    $error_group_num = "group_num";
  }
  
  if(isEmpty($name)) $error_name = "empty";
  if(isEmpty($surname)) $error_surname = "empty";
  if(isEmpty($nick)) $error_nick = "empty";
  if(isEmpty($birthday)) $error_birthday = "empty";
  if(isEmpty($street)) $error_street = "empty";
  if(isEmpty($city)) $error_city = "empty";
  if(isEmpty($group_name)) $error_group_name = "empty";
  
  if($error == ""){
    $sql = "INSERT  INTO `kk_visitors` (`name`, `surname`, `nick`, `birthday`, `email`, `street`, `city`, `postal_code`, `province`, `group_num`, `group_name`, `troop_name`, `comment`, `arrival`, `departure`, `bill`, `meeting`, `question`, `code`,`reg_daytime`) 
      VALUES ('".$name."', '".$surname."', '".$nick."', '".$birthday."', '".$email."', '".$street."', '".$city."', '".$postal_code."', '".$province."', '".$group_num."', '".$group_name."', '".$troop_name."', '".$comment."', '".$arrival."', '".$departure."', '".$bill."', '".$mid."', '".$question."', CONCAT(LEFT('".$name."',1),LEFT('".$surname."',1),SUBSTRING('".$birthday."',3,2)),'".date('Y-m-d H:i:s')."')";
      
    $result = mysql_query($sql);
    $vid = mysql_insert_id();
    if(!$result)$error = "error";
    else {$error = "ok";
      //$vid = 5;
      $blockSql = "SELECT   id
             FROM kk_blocks
             WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
      $blockResult = mysql_query($blockSql);
      while($blockData = mysql_fetch_assoc($blockResult)){
        $usrProgSql = "INSERT INTO `kk_visitor-program` (`visitor`, `program`)
                 VALUES ('".$vid."', '".$$blockData['id']."')";
        $usrProgResult = mysql_query($usrProgSql);
      }
      
      $mealSql = "INSERT  INTO `kk_meals` (`visitor`, `fry_dinner`, `sat_breakfast`, `sat_lunch`, `sat_dinner`, `sun_breakfast`, `sun_lunch`) 
      VALUES ('".$vid."', '".$fry_dinner."', '".$sat_breakfast."', '".$sat_lunch."', '".$sat_dinner."', '".$sun_breakfast."', '".$sun_lunch."')";
      $mealResult = mysql_query($mealSql);
    
      ######################## ODESILAM EMAIL ##########################

      // zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
      $code4bank = substr($name, 0, 1).substr($surname, 0, 1).substr($birthday, 2, 2);
      $hash = ((int)$vid.$mid) * 147 + 49873; 
              
      $recipient_mail = $email;
      $recipient_name = $name." ".$surname;
      
      $Container = new Container($GLOBALS['cfg']);
      $Emailer = $Container->createEmailer();
      if($return = $Emailer->sendRegistrationSummary($recipient_mail, $recipient_name, $hash, $code4bank)) {
        redirect("check.php?hash=".$hash."&error=".$error."");
      } else {
        echo 'Došlo k chybě při odeslání e-mailu.';
        echo 'Chybová hláška: ' . $return;
      }
    
    
      ##################################################################
    }
  }
}

########################## ROLLS ####################################  
$province_roll = "<select ".$disabled." style='width: 195px; font-size:11px' name='province'>\n";

$provinceSql = "SELECT  *
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
  
  $meal_roll .= "<span style='display:block;font-size:11px;'>".$mealDayKey.":</span><img style='width:18px;' src='".IMG_DIR."icons/".$mealIcon.".png' /><select ".$disabled." style='width:195px; font-size:11px;margin-left:5px;' name='".$mealDayVal."'>\n";
  foreach ($meal_array as $meal_key => $v2){
    if($meal_key == $$mealDayVal){
      $sel2 = "selected";
    }
    else $sel2 = "";
    $meal_roll .= "<option value='".$meal_key."' ".$sel2.">".$v2."</option>";
  }
  $meal_roll .= "</select><br />\n";
}

$style .= "<style>";
$style .= "
#footer {
    background: url('../../plugins/templates/hkvs2/images/outer-bottom-program.png') no-repeat scroll left top transparent;
}
";
$style .= "</style>";

################## GENEROVANI STRANKY #############################

$page_title = "Registrace srazu VS";

?>

<?php require_once('../inc/vodni_header.inc.php'); ?>

    <!-- content -->
    <div id="content-program">
    <div id="content-pad-program">
<h1>Registrace na Srazy VS</h1>
<h2><?php echo $meetingHeader; ?></h2>
<br />
<?php printError($error); ?>

<?php
/*echo $error;
echo $error_name;
echo $error_surname;
echo $error_postal_code;
echo $error_email;
echo $error_group_num;
echo $error_bill;*/
?>

<?php if($display_registration){ ?>

<script type="text/javascript" src="<?php echo JS_DIR ?>/jquery/jquery.tinytips.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $('a.programLink').tinyTips('light', 'title');
});
</script>

<!-- REGISTRACNI FORMULAR SRAZU -->

<form action='index.php' method='post'>

<div class='button-line'>
 <button <?php echo $disabled; ?> type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onClick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div>

<div style="text-align:center;"><span class="required">*</span>Takto označené položky musí být vyplněné!</div>

<script src='<?php echo JS_DIR; ?>jquery/jquery-ui.js' type='text/javascript'></script>
<script type="text/javascript">
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
    dateFormat: 'dd.mm.yy',
    maxDate: '0',
  });
});
</script>


<table class='form'>
 <tr>
  <td class='label'><label><span class="required">*</span>Jméno:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='name' size='30' value='<?php echo $name; ?>' /><?php printError($error_name); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Příjmení:</label></td>
  <td><input <?php echo $disabled; ?> type="text" name='surname' size="30" value='<?php echo $surname; ?>' /><?php printError($error_surname); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Přezdívka:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='nick' size='30' value='<?php echo $nick; ?>' /><?php printError($error_nick); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>E-mail:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='email' size='30' value='<?php echo $email; ?>' /><?php printError($error_email); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Datum narození:</label></td>
  <td><div class="picker"><input <?php echo $disabled; ?> id="birthday" class="datePicker" type='text' name='birthday' size='30' value='<?php echo formatDateFromDB($birthday,"d.m.Y"); ?>' /></div> (datum ve formátu dd.mm.rrrr) <?php printError($error_birthday); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Ulice:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='street' size='30' value='<?php echo $street; ?>' /><?php printError($error_street); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Město:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='city' size='30' value='<?php echo $city; ?>' /><?php printError($error_city); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>PSČ:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='postal_code' size='10' value='<?php echo $postal_code; ?>' /> (formát: 12345)<?php printError($error_postal_code); ?></td>
 </tr>
 <tr>
  <td></td>
  <td><div style="margin:2px 0px 2px 0px; font-weight:bold;">Junák - svaz skautů a skautek ČR</div></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Číslo střediska/přístavu:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='group_num' size='10' value='<?php echo $group_num; ?>' /> (formát: 214[tečka]02)<?php printError($error_group_num); ?></td>
 </tr>
 <tr>
  <td class='label'><label><span class="required">*</span>Název střediska/přístavu:</label></td>
  <td>
   <input <?php echo $disabled; ?> type='text' name='group_name' size='30' value='<?php echo $group_name; ?>' /> (2. přístav Poutníci Kolín) <?php printError($error_group_name); ?>
  </td>
 </tr>
 <tr>
  <td class='label'><label>Název oddílu:</label></td>
  <td><input <?php echo $disabled; ?> type='text' name='troop_name' size='30' value='<?php echo $troop_name; ?>' /> (22. oddíl Galeje)</td>
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
  <td colspan="2">Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) přijedete na místo srazu a v kolik hodin (přibližně) a jakým prostředkem sraz opustíte.
  </td>
 </tr>
 <tr>
  <td style="font-weight:bold;" colspan="2">Pokud máte volná místa v autě a jste ochotni někoho vzít, vyplňte prosím odkud jedete a kolik máte volných míst.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Informace o příjezdu:</label></td>
  <td><textarea <?php echo $disabled; ?> name='arrival' cols="50" rows="3" ><?php echo $arrival; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Informace o odjezdu:</label></td>
  <td><textarea <?php echo $disabled; ?> name='departure' cols="50" rows="3" ><?php echo $departure; ?></textarea></td>
 </tr>
 <tr>
  <td class='label'><label>Dotazy, přání, připomínky, stížnosti:</label></td>
  <td><textarea <?php echo $disabled; ?> name='comment' cols="50" rows="8" ><?php echo $comment; ?></textarea></td>
 </tr>
<!-- <tr>
  <td style="font-weight:bold;" colspan="2">Máte nezodpovězené otázky ohledně Junáka a celé organizace nebo HKVS? Nerozumíte něčemu? Trápí vás to? Nevyznáte se v něčem? Potřebujete poradit? Zde napiště svoji otázku a my vám na ní odpovíme! Odpovědi najdete na jarním srazu nebo později na webu HKVS.
  </td>
 </tr>
 <tr>
  <td class='label'><label>Vaše otázka:</label></td>
  <td><textarea <?php echo $disabled; ?> name='question' cols="50" rows="8" ><?php echo $question; ?></textarea></td>
 </tr>-->
</table>

 <input <?php echo $disabled; ?> type='hidden' name='cms' value='create' />
 <input <?php echo $disabled; ?> type='hidden' name='mid' value='<?php echo $mid;  ?>' />
 <input <?php echo $disabled; ?> type="hidden" name="bill" value="0" />
 
 <?php echo $programs; ?>
 
 <div class='button-line'>
 <button <?php echo $disabled; ?> type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
 <button type='button' onClick="window.location.replace('<?php echo HTTP_DIR ?>sraz-kk-setkani-cinovniku.p77.html')">
  <img src='<?php echo IMG_DIR; ?>icons/storno.png'  /> Storno</button>
</div>
 
</form>


<!-- REGISTRACNI FORMULAR SRAZU -->

<?php } else { ?>
<div>Registrace není otevřena, sraz se stále ještě připravuje!</div>
<?php } ?>

<p> </p>
<p style="text-align: center;"> </p>    </div>
    </div>
    <div class="cleaner"></div>
  </div>
</div>

<?php require_once('../inc/vodni_footer.inc.php'); ?>