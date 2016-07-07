<?php
/**
 * Export Controller
 *
 * This file handles the retrieval and serving of exports
 */
class ExportController extends BaseController
{
	/**
	 * This template variable will hold the 'view' portion of our MVC for this
	 * controller
	 */
	public $template = 'export';

	private $container;
	private $export;
	private $view;
	private $program;

	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->export = $this->container->createServiceExports();
		$this->view = $this->container->createServiceView();
		//$this->program = $this->container->createServicePrograms();
	}

	/**
	 * This is the default function that will be called by router.php
	 *
	 * @param 	array 	$getVars 	the GET variables posted to index.php
	 */
	public function init(array $getVars)
	{
		// program detail and program public must be public
		if((key($_GET) != 'program-public') && (key($_GET) != 'program-details')) {
			include_once(INC_DIR.'access.inc.php');
		}

		if($mid = requested("mid","")){
			$_SESSION['meetingID'] = $mid;
		} else {
			$mid = $_SESSION['meetingID'];
		}

		$this->export->setMeetingId($mid);
		$Container = new Container($GLOBALS['cfg'], $mid, $this->database);
		$this->program = $Container->createProgram();

		switch(key($_GET)){
			case 'attendance':
				$this->export->printAttendance();
				break;
			case 'evidence':
				if(isset($_GET['vid']) && $_GET['vid'] != ''){
					$this->export->printEvidence($_GET['evidence'], intval($_GET['vid']));
				} else {
					$this->export->printEvidence($_GET['evidence']);
				}
				break;
			case 'visitor-excel':
				$this->export->printVisitorsExcel();
				break;
			case 'meal-ticket':
				$this->export->printMealTicket();
				break;
			case 'name-badges':
				$names = requested('names', '');
				$this->export->printNameBadges($names);
				break;
			case 'program-details':
				$this->export->printProgramDetails();
				break;
			case 'program-cards':
				$this->export->printProgramCards();
				break;
			case 'program-large':
				$this->export->printLargeProgram();
				break;
			case 'program-badge':
				$this->export->printProgramBadges();
				break;
			case 'program-public':
				$this->export->printPublicProgram();
				break;
			case 'name-list':
				$this->export->printNameList();
				break;
		}

		/* HTTP Header */
		$this->view->loadTemplate('http_header');
		$this->view->assign('config',		$GLOBALS['cfg']);
		$this->view->render(TRUE);

		/* Application Header */
		$this->view->loadTemplate('header');
		$this->view->assign('config',		$GLOBALS['cfg']);
		$this->view->assign('database',		$this->database);
		$this->view->render(TRUE);

		// load and prepare template
		$this->view->loadTemplate('exports/exports');
		$this->view->assign('graph',		$this->export->renderGraph());
		$this->view->assign('graphHeight',	$this->export->getGraphHeight());
		$this->view->assign('account',		$this->export->getMoney('account'));
		$this->view->assign('balance',		$this->export->getMoney('balance'));
		$this->view->assign('suma',			$this->export->getMoney('suma'));
		$this->view->assign('programs',		$this->program->renderExportPrograms());
		$this->view->assign('materials',	$this->export->getMaterial());
		$this->view->assign('meals',		$this->export->renderMealCount());
		$this->view->render(TRUE);

		/* Footer */
		$this->view->loadTemplate('footer');
		$this->view->render(TRUE);
	}
}
