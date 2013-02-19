<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

##################### KONTROLA ###################################

if($mid = requested("mid","")){
	$_SESSION['meetingID'] = $mid;
} else {
	$mid = $_SESSION['meetingID'];
}

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
 <?php echo $ExportHandler->renderMealCount(); ?>
</div>

<div style="padding-left:22.5%;padding-right:60%;margin-top:6px;height:<?php echo $ExportHandler->getGraphHeight(); ?>px;">
 <div class='pageRibbon'>Peníze</div>
 <div style="margin-bottom:4px;">Celkem vybráno: <strong><?php echo $ExportHandler->getMoney('account'); ?></strong>,-Kč</div>
 <div style="margin-bottom:4px;">Zbývá vybrat: <strong><?php echo $ExportHandler->getMoney('balance'); ?></strong>,-Kč</div>
 <div style="margin-bottom:4px;">Suma srazu celkem: <strong><?php echo $ExportHandler->getMoney('suma'); ?></strong>,-Kč</div>
</div>

<div style="width:50%;float:left;">
 <div class='pageRibbon'>Programy</div>
 <?php echo $ExportHandler->Program->renderExportPrograms(); ?>
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