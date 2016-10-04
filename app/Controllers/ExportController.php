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
	private $model;
	private $pdf;
	private $excel;

	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->model = $this->container->createServiceExports();
		$this->program = $this->container->createServiceProgram();
		$this->latte = $this->container->getService('latte');
		$this->templateDir = 'exports';
		$this->pdf = $this->container->createServicePdffactory();
		$this->debugMode = $this->container->parameters['debugMode'];
		$this->excel = $this->container->createServiceExcelfactory();
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

		$this->model->setMeetingId($mid);
		$this->program->setMeetingId($mid);

		switch($this->router->getParameter('action')){
			case 'attendance':
				$this->renderAttendance();
				break;
			case 'evidence':
				//if(!empty($this->requested('vid'))) {
				if($this->requested('vid')) {
					$this->renderEvidence($this->requested('type'), intval($this->requested('vid')));
				} else {
					$this->renderEvidence($this->requested('type'));
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
			'graph'		=> $this->model->renderGraph(),
			'graphHeight'	=> $this->model->getGraphHeight(),
			'account'	=> $this->model->getMoney('account'),
			'balance'	=> $this->model->getMoney('balance'),
			'suma'		=> $this->model->getMoney('suma'),
			'programs'	=> $this->program->renderExportPrograms(),
			'materials'	=> $this->model->getMaterial(),
			'meals'		=> $this->model->renderMealCount(),
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

	/**
	 * Print Attendance into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function renderAttendance()
	{
		// output file name
		$filename = "attendance_list.pdf";
		$this->template = 'attendance';

		$pdf = $this->pdf->create();
		$data = $this->model->attendance();

		// prepare header
		$attendanceHeader = $data[0]['place'] . ' ' . $data[0]['year'];

		// set header
		$pdf->SetHeader($attendanceHeader.'|sraz VS|Prezenční listina');

		$parameters = [
			'result' => $data,
		];

		$template = $this->latte->renderToString(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);

		$this->publish($pdf, $filename, $template);
	}

	protected function publish($pdf, $filename, $template)
	{
		/* debugging */
		if($this->debugMode){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// write html
			$pdf->WriteHTML($template, 0);
			// download
			$pdf->Output($filename, "D");
		}
	}

}
