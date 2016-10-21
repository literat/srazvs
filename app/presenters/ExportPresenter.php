<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\DI\Container;
use Nette\Utils\Strings;

/**
 * Export Controller
 *
 * This file handles the retrieval and serving of exports
 */
class ExportPresenter extends BasePresenter
{
	/**
	 * This template variable will hold the 'view' portion of our MVC for this
	 * controller
	 */
	protected $templateName = 'exports';

	private $container;
	private $export;
	private $program;
	private $model;
	private $pdf;
	private $excel;
	private $filename;
	private $parameters;
	private $category;

	public function __construct(Context $database, Container $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->model = $this->container->createServiceExports();
		$this->program = $this->container->createServiceProgram();
		$this->latte = $this->container->getService('latte');
		$this->templateDir = 'exports';
		$this->pdf = $this->container->createServicePdffactory()->create();
		$this->debugMode = $this->container->parameters['debugMode'];
		$this->excel = $this->container->createServiceExcelfactory()->create();
		$this->category = $this->container->createServiceCategory();
	}

	/**
	 * This is the default function that will be called by router.php
	 *
	 * @param 	array 	$getVars 	the GET variables posted to index.php
	 */
	public function init()
	{
		if($this->meetingId = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $this->meetingId;
		} else {
			$this->meetingId = $_SESSION['meetingID'];
		}

		$this->model->setMeetingId($this->meetingId);
		$this->program->setMeetingId($this->meetingId);

		switch($this->router->getParameter('action')){
			case 'attendance':
				$this->renderAttendance();
				break;
			case 'evidence':
				$type = $this->requested('id');
				if($this->requested('vid')) {
					$this->renderEvidence($type, intval($this->requested('vid')));
				} else {
					$this->renderEvidence($type);
				}
				break;
			case 'visitorExcel':
				$this->renderVisitorsExcel();
				break;
			case 'mealTicket':
				$this->renderMealTicket();
				break;
			case 'nameBadges':
				$names =$this->requested('names', '');
				$this->renderNameBadges($names);
				break;
			case 'programDetails':
				$this->renderProgramDetails();
				break;
			case 'programCards':
				$this->renderProgramCards();
				break;
			case 'programLarge':
				$this->renderLargeProgram();
				break;
			case 'programBadge':
				$this->renderProgramBadges();
				break;
			case 'programPublic':
				$this->renderPublicProgram();
				break;
			case 'nameList':
				$this->renderNameList();
				break;
			case 'programVisitors':
				$id = $this->requested('id');
				$this->renderProgramVisitors($id);
				break;
		}

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'wwwDir'	=> HTTP_DIR,
			'user'		=> $this->getSunlightUser($_SESSION[SESSION_PREFIX.'user']),
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

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->templateName . '.latte', $parameters);
	}

	public function renderEvidence($type, $visitorId = null)
	{
		$this->filename = 'faktura.pdf';

		// summary header
		$hkvsHeader = "Junák - český skaut, Kapitanát vodních skautů, z. s. | ";
		$hkvsHeader .= "Senovážné náměstí 977/24, Praha 1, 110 00 | ";
		$hkvsHeader .= "IČ: 65991753, ČÚ: 2300183549/2010";

		$data = $this->model->evidence($visitorId);

		switch($type){
			case "summary":
				$this->templateName = 'evidence_summary';
				// specific mPDF settings
				$this->pdf->defaultfooterfontsize = 16;
				$this->pdf->defaultfooterfontstyle = 'B';
				$this->pdf->SetHeader($hkvsHeader);
				break;
			case "confirm":
				$this->templateName = 'evidence_confirm';
				break;
			default:
				$this->templateName = 'evidence';
				break;
		}

		$this->parameters = [
			'imgDir'	=> IMG_DIR,
			'result'	=> $data,
		];

		$this->publish();
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
		$this->filename = "attendance_list.pdf";
		$this->templateName = 'attendance';

		$data = $this->model->attendance();

		// prepare header
		$attendanceHeader = $data[0]['place'] . ' ' . $data[0]['year'];

		// set header
		$this->pdf->SetHeader($attendanceHeader.'|sraz VS|Prezenční listina');

		$this->parameters = [
			'result' => $data,
		];

		$this->publish();
	}

	/**
	 * Print meal tickets into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function renderMealTicket()
	{
		// output file name
		$this->filename= 'vlastni_stravenky.pdf';
		$this->templateName = 'meal_ticket';

		$data = $this->model->mealTicket();

		$this->parameters = [
			'imgDir'	=> IMG_DIR,
			'result'	=> $data,
		];

		$this->publish();
	}

	/**
	 * Print name list into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function renderNameList()
	{
		// output file name
		$this->filename = 'name_list.pdf';
		$this->templateName = 'name_list';

		$data = $this->model->nameList();

		// prepare header
		$namelistHeader = $data[0]['place'] . " " . $data[0]['year'];

		// set header
		$this->pdf->SetHeader($namelistHeader.'|sraz VS|Jméno, Příjmení, Přezdívka');

		$this->parameters = [
			'result'	=> $data,
		];

		$this->publish();
	}

	/**
	 * Print program cards into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function renderProgramCards()
	{
		$this->filename = 'vlastni_programy.pdf';
		$this->templateName = 'program_cards';

		$data = $this->model->programCards();

		// prepare header
		$attendanceHeader = $data[0]['place']." ".$data[0]['year'];

		$this->pdf->SetWatermarkImage(IMG_DIR . 'logos/watermark.jpg', 0.1, '');
		$this->pdf->showWatermarkImage = true;

		$this->parameters = [
			'result'	=> $data,
			'database'	=> $this->database,
		];

		$this->publish();
	}

	/**
	 * Print large program into PDF file
	 *
	 * @param	voide
	 * @return	file	PDF file
	 */
	public function renderLargeProgram()
	{
		$this->filename = Strings::toAscii($data['place'] . $data['year'] . '-program') . '.pdf';
		$this->templateName = 'program_large';

		// prepare header
		$data = $this->model->largeProgram();

		$meetingHeader = $data['place']." ".$data['year'];

		$this->pdf->paperFormat = 'B1';

		$this->parameters = [
			'header'		=> $meetingHeader,
			'meeting_id'	=> $this->meetingId,
			'result'		=> $data,
			'database'		=> $this->database,
		];

		$this->publish();

	}

	/**
	 * Print public program into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function renderPublicProgram()
	{
		$this->templateName = 'program_public';
		$this->filename = Strings::toAscii($data['place'] . $data['year'] . '-program' . '.pdf');

		$data = $this->model->publicProgram();

		$meetingHeader = $data['place'] . ' ' . $data['year'];

		$this->parameters = [
			'header'		=> $meetingHeader,
			'meeting_id'	=> $this->meetingId,
			'result'		=> $data,
			'database'		=> $this->database,
			'styles'		=> $this->category->getStyles(),
		];

		$this->publish();
	}

	/**
	 * Print program badges into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function renderProgramBadges()
	{
		$this->filename = 'program-badge.pdf';
		$this->templateName = 'program_badge';

		$data = $this->model->programBadges();

		$this->pdf->setMargins(5, 5, 9, 5);

		$this->parameters = [
			'meeting_id'	=> $this->meetingId,
			'result'		=> $data,
			'database'		=> $this->database,
		];

		$this->publish();
	}

	/**
	 * Print name badges into PDF file
	 *
	 * @param	string 	comma separated values
	 * @return	file	PDF file
	 */
	public function renderNameBadges($names = null)
	{
		$this->filename = 'jmenovky.pdf';
		$this->templateName = 'name_badge';

		$badges = array();

		if(empty($names)) {
			$data = $this->model->nameBadges();

			foreach($data as $row) {
				array_push($badges, $row);
			}
		} else {
			$names = preg_replace('/\s+/','',$names);

			$values = explode(',',$names);
			foreach($values as $value) {
				$row['nick'] = $value;
				array_push($badges, $row);
			}
		}

		$this->pdf->setMargins(15, 15, 10, 5);

		// set watermark
		$this->pdf->SetWatermarkImage(IMG_DIR . 'logos/watermark-waves.jpg', 0.1, '');
		$this->pdf->showWatermarkImage = TRUE;

		$this->parameters = [
			'result' => $badges,
		];

		$this->publish();
	}

/**
	 * Print visitors on program into PDF file
	 *
	 * @param	int		program id
	 * @return	file	PDF file
	 */
	public function renderProgramVisitors($programId)
	{
		$this->filename = 'ucastnici-programu.pdf';
		$this->templateName = 'program_visitors';

		$data = $this->model->programVisitors($programId);

		$programHeader = $data[0]['program'];

		$this->pdf->SetHeader($programHeader.'|sraz VS|Účastnící programu');

		$this->parameters = [
			'result' => $data,
		];

		$this->publish();
	}

	/**
	 * Print details of program into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function renderProgramDetails()
	{
		$this->filename = 'vypis-programu.pdf';
		$this->templateName = 'program_details';

		$data = $this->model->programDetails();

		$this->pdf->SetHeader('Výpis programů|sraz VS');

		$this->parameters = [
			'result' => $data,
		];

		$this->publish();
	}

	/**
	 * Print data of visitors into excel file
	 *
	 * @return	file	*.xlsx file type
	 */
	public function renderVisitorsExcel()
	{
		$excel = $this->excel;

		$excel->getProperties()->setCreator("HKVS Srazy K + K")->setTitle("Návštěvníci");

		// Zde si vyvoláme aktivní list (nastavený nahoře) a vyplníme buňky A1 a A2
		$list = $excel->setActiveSheetIndex(0);

		$cells = [
			'A1' => 'ID',
			'B1' => 'symbol',
			'C1' => 'Jméno',
			'D1' => 'Příjmení',
			'E1' => 'Přezdívka',
			'F1' => 'Narození',
			'G1' => 'E-mail',
			'H1' => 'Adresa',
			'I1' => 'Město',
			'J1' => 'PSČ',
			'K1' => 'Kraj',
			'L1' => 'Evidence',
			'M1' => 'Středisko/Přístav',
			'N1' => 'Oddíl',
			'O1' => 'Účet',
			'P1' => 'Připomínky',
			'Q1' => 'Příjezd',
			'R1' => 'Odjezd',
			'S1' => 'Otázka',
		];

		foreach($cells as $key => $value) {
			$list->setCellValue($key, $value);
		}

		$excel->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);

		$dimensions = [
			'C' => 15,
			'D' => 15,
			'F' => 15,
			'G' => 30,
			'H' => 20,
			'I' => 15,
			'K' => 20,
			'M' => 30,
			'N' => 20,
			'P' => 20,
			'Q' => 20,
			'R' => 20,
			'S' => 20,
		];

		foreach($dimensions as $key => $value) {
			$excel->getActiveSheet()->getColumnDimension($key)->setWidth($value);
		}

		$visitors = $this->model->visitorsExcel();

		$cellValues = [
			'A' => 'id',
			'B' => 'code',
			'C' => 'name',
			'D' => 'surname',
			'E' => 'nick',
			'F' => 'birthday',
			'G' => 'email',
			'H' => 'street',
			'I' => 'city',
			'J' => 'postal_code',
			'K' => 'province',
			'L' => 'group_num',
			'M' => 'group_name',
			'N' => 'troop_name',
			'O' => 'bill',
			'P' => 'comment',
			'Q' => 'arrival',
			'R' => 'departure',
			'S' => 'question',
			'S' => 'question2',
			'T' => 'all',
			'U' => 'fry_dinner',
			'V' => 'sat_breakfast',
			'W' => 'sat_lunch',
			'X' => 'sat_dinner',
			'Y' => 'sun_breakfast',
			'Z' => 'sun_lunch',
		];

		$i = 2;
		foreach($visitors as $data) {
			foreach($cellValues as $cell => $value) {
				$list->setCellValue($cell . $i, $data[$value]);
			}
			$i++;
		}

		// stahnuti souboru
		$filename = 'export-MS-'.date('Y-m-d',time()).'.xlsx';

		$excel->setActiveSheetIndex(0);

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$ExcelWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$ExcelWriter->save('php://output');
		exit;
	}

	protected function publish()
	{
		$template = $this->latte->renderToString(__DIR__ . '/../templates/' . $this->templateDir . '/' . $this->templateName . '.latte', $this->parameters);

		/* debugging */
		if($this->debugMode){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// write html
			$this->pdf->WriteHTML($template, 0);
			// download
			$this->pdf->Output($filename, "D");
		}
	}

}
