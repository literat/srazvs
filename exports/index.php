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
$ViewHandler = $Container->createView();

switch(key($_GET)){
	case 'attendance':
		$ExportHandler->printAttendance();
		break;
	case 'evidence':
		if(isset($_GET['vid']) && $_GET['vid'] != ''){
			$ExportHandler->printEvidence($_GET['evidence'], intval($_GET['vid']));
		} else {
			$ExportHandler->printEvidence($_GET['evidence']);
		}
		break;
	case 'visitor-excel':
		$ExportHandler->printVisitorsExcel();
		break;
	case 'meal-ticket':
		$ExportHandler->printMealTicket();
		break;
	case 'name-badges':
		$ExportHandler->printNameBadges();
		break;
}

################## GENEROVANI STRANKY #############################

include_once(INC_DIR.'http_header.inc.php');
include_once(INC_DIR.'header.inc.php');

// load and prepare template
$ViewHandler->loadTemplate('exports/exports');
$ViewHandler->assign('graph',		$ExportHandler->renderGraph());
$ViewHandler->assign('graphHeight',	$ExportHandler->getGraphHeight());
$ViewHandler->assign('account',		$ExportHandler->getMoney('account'));
$ViewHandler->assign('balance',		$ExportHandler->getMoney('balance'));
$ViewHandler->assign('suma',		$ExportHandler->getMoney('suma'));
$ViewHandler->assign('programs',	$ExportHandler->Program->renderExportPrograms());
$ViewHandler->assign('materials',	$ExportHandler->getMaterial());
$ViewHandler->assign('meals',		$ExportHandler->renderMealCount());
$ViewHandler->render(TRUE);

include_once(INC_DIR.'footer.inc.php');