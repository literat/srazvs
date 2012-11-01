<?php

require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

###########################################################################

$sql = "SELECT	vis.id AS id,
				name,
				surname,
				nick,
				DATE_FORMAT(birthday, '%d. %m. %Y') AS birthday,
				street,
				city,
				postal_code,
				province_name,
				group_num,
				group_name,
				troop_name,
				email,
				comment,
				arrival,
				departure,
				fry_dinner,
				sat_breakfast,
				sat_lunch,
				sat_dinner,
				sun_breakfast,
				sun_lunch
		FROM kk_visitors AS vis
		LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
		LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
		WHERE vis.id='1' AND meeting='1' AND deleted='0'
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

################################ PROGRAMY ###################################

$programs = "  <div style='border-bottom:1px solid black;text-align:right;'>vybrané programy</div>";

$progSql = "SELECT  progs.name AS prog_name,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`
			FROM kk_programs AS progs
			LEFT JOIN `kk_visitor-program` AS visprog ON progs.id = visprog.program
			LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
			LEFT JOIN kk_blocks AS blocks ON progs.block = blocks.id
			WHERE vis.id = '1'";
$progResult = mysql_query($progSql);
while($progData = mysql_fetch_assoc($progResult)){
	$programs .= $progData['day'].", ".$progData['from']." - ".$progData['to']."";
	$programs .= "<div style='padding:5px 0px 5px 20px;'>- ".$progData['prog_name']."</div>";
}

$name = requested("name",$data['name']);
$surname = requested("surname",$data['surname']);
$nick = requested("nick",$data['nick']);
$birthday = requested("birthday",$data['birthday']);
$street = requested("street",$data['street']);
$city = requested("city",$data['city']);
$postal_code = requested("postal_code",$data['postal_code']);
$province = requested("province",$data['province_name']);
$group_num = requested("group_num",$data['group_num']);
$group_name = requested("group_name",$data['group_name']);
$troop_name = requested("troop_name",$data['troop_name']);
$email = requested("email",$data['email']);
$comment = requested("comment",$data['comment']);
$arrival = requested("arrival",$data['arrival']);
$departure = requested("departure",$data['departure']);


$fry_dinner = requested("fry_dinner",$data['fry_dinner']);
$sat_breakfast = requested("sat_breakfast",$data['sat_breakfast']);
$sat_lunch = requested("sat_lunch",$data['sat_lunch']);
$sat_dinner = requested("sat_dinner",$data['sat_dinner']);
$sun_breakfast = requested("sun_breakfast",$data['sun_breakfast']);
$sun_lunch = requested("sun_lunch",$data['sun_lunch']);

// multiple recipients
$to = $data['email']; // note the comma

// subject
$subject = 'Sraz VS: registrovane udaje';

// message
$message = "
<html>
<head>
 <title>Registrační údaje na sraz VS</title>
</head>
<body>
 <table class='form'>
 <tr>
  <td class='label'>Jméno:</td>
  <td>".$name."</td>
 </tr>
 <tr>
  <td class='label'>Příjmení:</td>
  <td>".$surname."</td>
 </tr>
 <tr>
  <td class='label'>Přezdívka:</td>
  <td>".$nick."</td>
 </tr>
 <tr>
  <td class='label'>E-mail:</td>
  <td>".$email."</td>
 </tr>
 <tr>
  <td class='label'>Datum narození:</td>
  <td>".$birthday."</td>
 </tr>
 <tr>
  <td class='label'>Ulice:</td>
  <td>".$street."</td>
 </tr>
 <tr>
  <td class='label'>Město:</td>
  <td>".$city."</td>
 </tr>
 <tr>
  <td class='label'>PSČ:</td>
  <td>".$postal_code."</td>
 </tr>
 <tr>
  <td class='label'>Číslo střediska/přístavu:</td>
  <td>".$group_num."</td>
 </tr>
 <tr>
  <td class='label'>Název střediska/přístavu:</td>
  <td><div style='margin:2px 0px 2px 0px; font-weight:bold;'>Junák - svaz skautů a skautek ČR</div>
   ".$group_name."
  </td>
 </tr>
 <tr>
  <td class='label'>Název oddílu:</td>
  <td>".$troop_name."</td>
 </tr>
 <tr>
  <td class='label'>Kraj:</td>
  <td>".$province."</td>
 </tr>
 <tr>
  <td class='label'>Stravování:</td>
  <td>
   <div>páteční večeře: <span style='font-weight:bold;'>".$fry_dinner."</span></div>
   <div>sobotní snídaně: <span style='font-weight:bold;'>".$sat_breakfast."</span></div>
   <div>sobotní oběd: <span style='font-weight:bold;'>".$sat_lunch."</span></div>
   <div>sobotní večeře: <span style='font-weight:bold;'>".$sat_dinner."</span></div>
   <div>nedělní snídaně: <span style='font-weight:bold;'>".$sun_breakfast."</span></div>
   <div>nedělní oběd: <span style='font-weight:bold;'>".$sun_lunch."</span></div>
  </td>
 </tr>
 <tr>
  <td class='label'>Dotazy, přání, připomínky, stížnosti:</td>
  <td>".$comment."</td>
 </tr>
 <tr>
  <td class='label'>Informace o příjezdu:</td>
  <td>".$arrival."</td>
 </tr>
 <tr>
  <td class='label'>Informace o odjezdu:</td>
  <td>".$departure."</td>
 </tr>
</table>
".$programs."
</body>
</html>
";

// To send HTML mail, the Content-type header must be set
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

// Additional headers
$headers .= "To: ".$data['name']." ".$data['surname']." <".$data['email'].">" . "\r\n";
$headers .= 'From: Sraz VS <srazvs@vodni.skauting.cz>' . "\r\n";
//$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

// Mail it
echo "mail(".$to.", ".$subject.", ".$message.", ".$headers.")";
var_dump(mail($to, $subject, $message, $headers));
?> 