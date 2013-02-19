<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

##################### KONTROLA ###################################

if($mid = requested("mid","")){
	$_SESSION['meetingID'] = $mid;
}
else {
	$mid = $_SESSION['meetingID'];
}

$id = requested("id","");
$cms = requested("cms","");
$error = requested("error","");

//defaultni hodnoty pro razeni
$_SESSION['order'] = "name";
if(isset($_GET['orderby'])) $order = clearString($_GET['orderby']);
else $order = "name";
if(isset($_GET['way'])) $way = clearString($_GET['way']);
else $way = "asc";

if(!isset($chyba)) $chyba = "";

############################# MEALS #########################

function getMealCount($meal)
{
	$sql = "SELECT count(". $meal.") AS ". $meal."
			FROM `kk_meals` AS mls
			LEFT JOIN `kk_visitors` AS vis ON vis.id = mls.visitor
			WHERE vis.deleted = '0'
				AND vis.meeting = '".$_SESSION['meetingID']."'
				AND vis.deleted = '0'
				AND ". $meal." = 'ano'";
	$result = mysql_query($sql);
	$data = mysql_fetch_assoc($result);
	
	return $data[$meal];
}

$mealsArr = array("fry_dinner" => "páteční večeře",
			  	  "sat_breakfast" => "sobotní snídaně",
			   	  "sat_lunch" => "sobotní oběd",
			  	  "sat_dinner" => "sobotní večeře",
			  	  "sun_breakfast" => "nedělní snídaně",
			   	  "sun_lunch" => "nedělní oběd");

$meals = "<table>";

foreach($mealsArr as $mealsKey => $mealsVal){
	$mealCount = getMealCount($mealsKey);
	
	$meals .= "<tr><td>".$mealsVal.":</td><td><span style='font-size:12px; font-weight:bold;'>".$mealCount."</span></td></tr>";
}

$meals .= "</table>";

############################### PROGRAMS ##########################

function getPrograms($id){
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
		$html = "<table>\n";
		while($data = mysql_fetch_assoc($result)){
			$html .= "<tr>";
			//// resim kapacitu programu a jeho naplneni navstevniky
			$fullProgramSql = "SELECT COUNT(visitor) AS visitors 
							   FROM `kk_visitor-program` AS visprog
							   LEFT JOIN `kk_visitors` AS vis ON vis.id = visprog.visitor
							   WHERE program = '".$data['id']."'
							   		AND vis.deleted = '0'";
			$fullProgramResult = mysql_query($fullProgramSql);
			$fullProgramData = mysql_fetch_assoc($fullProgramResult);
		
			if($fullProgramData['visitors'] >= $data['capacity']){
				//$html .= "<input disabled type='radio' name='".$id."' value='".$data['id']."' />\n";
				$fullProgramInfo = "<span style='font-size:12px; font-weight:bold;'>".$fullProgramData['visitors']."/".$data['capacity']."</span> (kapacita programu je naplněna!)";
			}
			else {
				//$html .= "<input type='radio' name='".$id."' value='".$data['id']."' /> \n";
				$fullProgramInfo = "<span style='font-size:12px; font-weight:bold;'>".$fullProgramData['visitors']."/".$data['capacity']."</span>";
			}
			$html .= "<td style='min-width:270px;'>";
			$html .= "<a rel='programDetail' href='../programs/process.php?id=".$data['id']."&cms=edit' title='".$data['name']."'>".$data['name']."</a>\n";
			$html .= "</td>";
			$html .= "<td>";
			$html .= $fullProgramInfo;
			$html .= "</td>";
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	}
	return $html;
}



################################# PROGRAMY ################################

$programs = "";


$progSql = "SELECT 	id,
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
	$raftCountSql = "SELECT COUNT(visitor) AS raft FROM `kk_visitor-program` WHERE program='56|57'";
	$raftCountResult = mysql_query($raftCountSql);
	$raftCountData = mysql_fetch_assoc($raftCountResult);
	
	while($progData = mysql_fetch_assoc($progResult)){
		//nemoznost volit predsnemovni dikusi
		if($progData['id'] == 63) $notDisplayed = "style='display:none;'";
		//obsazenost raftu
		/*elseif($raftCountData['raft'] >= 18){
			if($progData['id'] == 86) $notDisplayed = "style='display:none;'";
			else $notDisplayed = "";
		}*/
		else $notDisplayed = "";
		$programs .= "<div ".$notDisplayed.">".$progData['day'].", ".$progData['from']." - ".$progData['to']." : ".$progData['name']."</div>\n";
		if($progData['program'] == 1) $programs .= "<div ".$notDisplayed.">".getPrograms($progData['id'])."</div>";
		$programs .= "<br />";
	}
}


/********/
$Container = new Container($GLOBALS['cfg'], $mid);
$ExportHandler = $Container->createExport();

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Exporty</div>
<div style="width:22%;float:left;">
 <div class='pageRibbon'>Tisk</div>
  <div>
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='evidence.pdf.php?type=confirm'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  potvrzení o přijetí zálohy
 </a> 
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='evidence.pdf.php?type=evidence'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  příjmový pokladní doklad
 </a>
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='program_cards.pdf.php'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  osobní program
 </a>
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='program_large.pdf.php'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  program srazu - velký formát
 </a>
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='program_badge.pdf.php'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  program srazu - do visačky
 </a>
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='name_badge.pdf.php'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  jmenovky
 </a>
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='attendance.pdf.php'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  prezenční listina
 </a>
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='meal_ticket.pdf.php'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  stravenky
 </a>
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='feedback.pdf.php'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  zpětná vazba
 </a>
 
 <a style='text-decoration:none; display:block; margin-bottom:4px;' href='evidence.pdf.php?type=summary'>
  <img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/pdf.png' />
  kompletní příjmový pokladní doklad
 </a>
 
  </div>
</div>


<div style="width:44%;padding-left:0.5%;float:right;">
 <div class='pageRibbon'>Graf přihlašování</div>
 <?php echo $ExportHandler->renderGraph(); ?>
</div>

<div style="width:15%;padding-left:0.5%;float:right;">
 <div class='pageRibbon'>Jídlo</div>
 <?php echo $meals; ?>
</div>

<div style="padding-left:22.5%;padding-right:60%;margin-top:6px;height:<?php echo $ExportHandler->getGraphHeight(); ?>px;">
 <div class='pageRibbon'>Peníze</div>
 <div style="margin-bottom:4px;">Celkem vybráno: <strong><?php echo $ExportHandler->getMoney('account'); ?></strong>,-Kč</div>
 <div style="margin-bottom:4px;">Zbývá vybrat: <strong><?php echo $ExportHandler->getMoney('balance'); ?></strong>,-Kč</div>
 <div style="margin-bottom:4px;">Suma srazu celkem: <strong><?php echo $ExportHandler->getMoney('suma'); ?></strong>,-Kč</div>
</div>

<div style="width:50%;float:left;">
 <div class='pageRibbon'>Programy</div>
 <?php echo $programs; ?>
</div>

<div style="width:49.5%;padding-left:50.5%;">
 <div class='pageRibbon'>Materiál</div>
  <div>
	<?php echo $ExportHandler->getMaterial(); ?>
  </div>
 </div>


<!--<div class='pageRibbon'>Něco dalšího</div>-->

<?php

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>