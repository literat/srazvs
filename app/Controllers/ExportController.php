<?php
/**
 * Export Controller
 *
 * This file handles the retrieval and serving of exports
 */
class ExportController
{
	/**
	 * This template variable will hold the 'view' portion of our MVC for this 
	 * controller
	 */
	public $template = 'export';

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param 	array 	$getVars 	the GET variables posted to index.php
	 */
	public function init(array $getVars)
	{
		include_once(INC_DIR.'access.inc.php');

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
			case 'program-details':
				$ExportHandler->printProgramDetails();
				break;
			case 'program-cards':
				$ExportHandler->printProgramCards();
				break;
			case 'program-large':
				$ExportHandler->printLargeProgram();
				break;
			case 'program-badge':
				$ExportHandler->printProgramBadges();
				break;
		}

		/* HTTP Header */
		$ViewHandler->loadTemplate('http_header');
		$ViewHandler->assign('config',		$GLOBALS['cfg']);
		$ViewHandler->render(TRUE);

		/* Application Header */
		$ViewHandler->loadTemplate('header');
		$ViewHandler->assign('config',		$GLOBALS['cfg']);
		$ViewHandler->render(TRUE);

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

		/* Footer */
		$ViewHandler->loadTemplate('footer');
		$ViewHandler->render(TRUE);
	}
}