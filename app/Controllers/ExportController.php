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
	public $template = 'exports';

	private $container;
	private $export;
	private $latte;
	private $program;

	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->export = $this->container->createServiceExports();
		$this->program = $this->container->createServiceProgram();
		$this->latte = $this->container->getService('latte');
		$this->templateDir = 'exports';
	}

	/**
	 * This is the default function that will be called by router.php
	 *
	 * @param 	array 	$getVars 	the GET variables posted to index.php
	 */
	public function init()
	{
		// program detail and program public must be public
		if(!$this->router->getParameter('program-public') && !$this->router->getParameter('program-details')) {
			include_once(INC_DIR.'access.inc.php');
		}

		if($mid = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $mid;
		} else {
			$mid = $_SESSION['meetingID'];
		}

		$this->export->setMeetingId($mid);
		$this->program->setMeetingId($mid);

		switch($this->router->getParameter('action')){
			case 'attendance':
				$this->export->printAttendance();
				break;
			case 'evidence':
				//if(!empty($this->requested('vid'))) {
				if($this->requested('vid')) {
					$this->export->printEvidence($this->requested('type'), intval($this->requested('vid')));
				} else {
					$this->export->printEvidence($this->requested('type'));
				}
				break;
			case 'visitorExcel':
				$this->export->printVisitorsExcel();
				break;
			case 'mealTicket':
				$this->export->printMealTicket();
				break;
			case 'nameBadges':
				$names =$this->requested('names', '');
				$this->export->printNameBadges($names);
				break;
			case 'programDetails':
				$this->export->printProgramDetails();
				break;
			case 'programCards':
				$this->export->printProgramCards();
				break;
			case 'programLarge':
				$this->export->printLargeProgram();
				break;
			case 'programBadge':
				$this->export->printProgramBadges();
				break;
			case 'programPublic':
				$this->export->printPublicProgram();
				break;
			case 'nameList':
				$this->export->printNameList();
				break;
		}

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'wwwDir'	=> HTTP_DIR,
			'user'		=> $this->getUser($_SESSION[SESSION_PREFIX.'user']),
			'meeting'	=> $this->getPlaceAndYear($_SESSION['meetingID']),
			'menu'		=> $this->generateMenu(),
			'graph'		=> $this->export->renderGraph(),
			'graphHeight'	=> $this->export->getGraphHeight(),
			'account'	=> $this->export->getMoney('account'),
			'balance'	=> $this->export->getMoney('balance'),
			'suma'		=> $this->export->getMoney('suma'),
			'programs'	=> $this->program->renderExportPrograms(),
			'materials'	=> $this->export->getMaterial(),
			'meals'		=> $this->export->renderMealCount(),
		];

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}

	public function renderEvidence($type, $visitorId = null)
	{
		$filename = 'faktura.pdf';

		// summary header
		$hkvsHeader = "Junák - český skaut, Kapitanát vodních skautů, z. s. | ";
		$hkvsHeader .= "Senovážné náměstí 977/24, Praha 1, 110 00 | ";
		$hkvsHeader .= "IČ: 65991753, ČÚ: 2300183549/2010";

		$pdf = $this->pdf->create();
		$data = $this->model->evidence($visitorId);

		switch($type){
			case "summary":
				$this->template = 'evidence_summary';
				// specific mPDF settings
				$pdf->defaultfooterfontsize = 16;
				$pdf->defaultfooterfontstyle = 'B';
				$pdf->SetHeader($hkvsHeader);
				break;
			case "confirm":
				$this->template = 'evidence_confirm';
				break;
			default:
				$this->template = 'evidence';
				break;
		}

		$parameters = [
			'imgDir'	=> IMG_DIR,
			'result'	=> $data,
		];

		$template = $this->latte->renderToString(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);

		$this->publish($pdf, $filename, $template);
	}
}
