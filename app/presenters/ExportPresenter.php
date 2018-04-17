<?php

namespace App\Presenters;

use App\Components\MaterialsControl;
use App\Components\MealControl;
use App\Components\RegistrationGraphControl;
use App\Factories\ExcelFactory;
use App\Factories\PdfFactory;
use App\Models\BlockModel;
use App\Models\ExportModel;
use App\Models\MealModel;
use App\Models\ProgramModel;
use Mpdf\Mpdf;
use Nette\Utils\Strings;
use PHPExcel;

class ExportPresenter extends BasePresenter
{
	const TEMPLATE_DIR = __DIR__ . '/../templates/Export/';
	const TEMPLATE_EXT = 'latte';

	/**
	 * @var ProgramModel
	 */
	protected $programModel;

	/**
	 * @var BlockModel
	 */
	protected $blockModel;

	/**
	 * @var \Mpdf\Mpdf
	 */
	protected $pdf;

	/**
	 * @var \PHPExcel
	 */
	protected $excel;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var RegistrationGraphControl
	 */
	private $registrationGraphControl;

	/**
	 * @var MaterialsControl
	 */
	private $materialControl;

	/**
	 * @var MealControl
	 */
	private $mealControl;

	/**
	 * @param ExportModel              $export
	 * @param ProgramModel             $program
	 * @param ExcelFactory             $excel
	 * @param PdfFactory               $pdf
	 * @param RegistrationGraphControl $control
	 * @param MaterialsControl         $materialControl
	 */
	public function __construct(
		ExportModel $export,
		ProgramModel $program,
		BlockModel $block,
		ExcelFactory $excel,
		PdfFactory $pdf,
		RegistrationGraphControl $control,
		MaterialsControl $materialControl,
		MealControl $mealControl
	) {
		$this->setModel($export);
		$this->setProgramModel($program);
		$this->setBlockModel($block);
		$this->setExcel($excel->create());
		$this->setPdf($pdf->create());
		$this->setRegistrationGraphControl($control);
		$this->setMaterialControl($materialControl);
		$this->setMealControl($mealControl);
	}

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$this->getProgramModel()->setMeetingId($this->getMeetingId());
	}

	public function renderDefault()
	{
		$this->allowAdminAccessOnly();

		$settingsModel = $this->getModel();
		$template = $this->getTemplate();

		$template->graphHeight = $this->calculateGraphHeight();
		$template->account = $settingsModel->getMoney('account');
		$template->balance = $settingsModel->getMoney('balance');
		$template->suma = $settingsModel->getMoney('suma');
		$template->programs = $this->getProgramModel()->renderExportPrograms();
		$template->meals = MealModel::$dayMeal;
	}

	public function renderEvidence($type, $id = null)
	{
		$this->allowAdminAccessOnly();

		$this->filename = 'faktura.pdf';

		// summary header
		$hkvsHeader = "Junák - český skaut, Kapitanát vodních skautů, z. s. | ";
		$hkvsHeader .= "Senovážné náměstí 977/24, Praha 1, 110 00 | ";
		$hkvsHeader .= "IČ: 65991753, ČÚ: 2300183549/2010";

		$evidences = $this->getModel()->evidence($id);

		if (!$evidences) {
			$this->logError('No data for evidence export.');
			$this->flashMessage('No data.');
			$this->redirect('Export:listing');
		}

		switch ($type) {
			case "summary":
				$templateName = 'evidence_summary';
				// specific mPDF settings
				$this->getPdf()->defaultfooterfontsize = 16;
				$this->getPdf()->defaultfooterfontstyle = 'B';
				$this->getPdf()->SetHeader($hkvsHeader);
				break;
			case "confirm":
				$templateName = 'evidence_confirm';
				break;
			default:
				$templateName = 'evidence';
				break;
		}

		$parameters = [
			'result' => $evidences,
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print Attendance into PDF file.
	 */
	public function renderAttendance()
	{
		$this->allowAdminAccessOnly();
		// output file name
		$this->filename = "attendance_list.pdf";
		$templateName = 'attendance';

		$attendances = $this->getModel()->attendance();

		// prepare header
		$attendanceHeader = $attendances[0]['place'] . ' ' . $attendances[0]['year'];

		// set header
		$this->getPdf()->SetHeader($attendanceHeader . '|sraz VS|Prezenční listina');

		$parameters = [
			'result' => $attendances,
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print meal tickets into PDF file.
	 */
	public function renderMealTicket()
	{
		$this->allowAdminAccessOnly();
		// output file name
		$this->filename = 'vlastni_stravenky.pdf';
		$templateName = 'meal_ticket';

		$mealTickets = $this->getModel()->mealTicket();

		$parameters = [
			'result' => $mealTickets,
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print name list into PDF file.
	 */
	public function renderNameList()
	{
		$this->allowAdminAccessOnly();
		// output file name
		$this->filename = 'name_list.pdf';
		$templateName = 'name_list';

		$nameList = $this->getModel()->nameList();

		// prepare header
		$namelistHeader = $nameList[0]['place'] . " " . $nameList[0]['year'];

		// set header
		$this->getPdf()->SetHeader($namelistHeader . '|sraz VS|Jméno, Příjmení, Přezdívka');

		$parameters = [
			'result' => $nameList,
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	public function renderProgram(string $type, int $id)
	{
		$programMethod = 'renderProgram' . Strings::firstUpper($type);

		call_user_func([$this, $programMethod], $id);
	}

	/**
	 * Print program cards into PDF file.
	 */
	protected function renderProgramCards()
	{
		$this->allowAdminAccessOnly();
		$this->filename = 'vlastni_programy.pdf';
		$templateName = 'program_cards';

		$this->getPdf()->SetWatermarkImage(IMG_DIR . 'logos/watermark.jpg', 0.1, '');
		$this->getPdf()->showWatermarkImage = true;

		$parameters = [
			'result' => $this->getModel()->programCards(),
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print large program into PDF file.
	 */
	protected function renderProgramLarge()
	{
		$this->allowAdminAccessOnly();
		$largeProgram = $this->getModel()->largeProgram();

		$this->filename = Strings::toAscii($largeProgram['place'] . $largeProgram['year'] . '-program') . '.pdf';
		$templateName = 'program_large';

		$meetingHeader = $largeProgram['place'] . " " . $largeProgram['year'];

		$this->getPdf()->paperFormat = 'B1';

		$parameters = [
			'header'	=> $meetingHeader,
			'export'	=> $this->getModel(),
			'program'	=> $this->getProgramModel(),
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print public program into PDF file.
	 */
	protected function renderProgramPublic()
	{
		$templateName = 'program_public';
		$publicProgram = $this->getModel()->publicProgram();
		$this->filename = Strings::toAscii($publicProgram['place'] . $publicProgram['year'] . '-program.pdf');

		$meetingHeader = $publicProgram['place'] . ' ' . $publicProgram['year'];

		$parameters = [
			'header'		=> $meetingHeader,
			'export'		=> $this->getModel(),
			'program'		=> $this->getProgramModel(),
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print program badges into PDF file.
	 */
	protected function renderProgramBadges()
	{
		$this->allowAdminAccessOnly();
		$this->filename = 'program-badge.pdf';
		$templateName = 'program_badge';

		$programBadges = $this->getModel()->programBadges();

		$this->getPdf()->setMargins(5, 5, 9, 5);

		$days = [
			'PÁTEK',
			'SOBOTA',
			'NEDĚLE',
		];

		$exportBlocks = [];
		foreach ($days as $day) {
			$exportBlocks[$day] = $this->getBlockModel()->getExportBlocks($this->meetingId, $day);
		}

		$parameters = [
			'meeting_id'	=> $this->meetingId,
			'result'		=> $programBadges,
			'days'			=> $days,
			'exportBlocks'	=> $exportBlocks,
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print visitors on program into PDF file.
	 */
	protected function renderProgramVisitors(int $id)
	{
		$this->allowAdminAccessOnly();
		$this->filename = 'ucastnici-programu.pdf';
		$templateName = 'program_visitors';

		$programs = $this->getModel()->programVisitors($id);

		$programHeader = $programs[0]['program'];

		$this->getPdf()->SetHeader($programHeader . '|sraz VS|Účastnící programu');

		$parameters = [
			'result' => $programs,
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print details of program into PDF file.
	 */
	protected function renderProgramDetails()
	{
		$this->allowAdminAccessOnly();
		$this->filename = 'vypis-programu.pdf';
		$templateName = 'program_details';

		$programDetails = $this->getModel()->programDetails();

		$this->getPdf()->SetHeader('Výpis programů|sraz VS');

		$parameters = [
			'result' => $programDetails,
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	public function actionNameBadges()
	{
		$this->allowAdminAccessOnly();
		$names = $this->getHttpRequest()->getPost()['names'];
		$this->renderNameBadges($names);
	}

	/**
	 * Print name badges into PDF file.
	 *
	 * @param  string                            $namesStringified comma separated values
	 * @throws \Nette\Application\AbortException
	 */
	public function renderNameBadges(string $namesStringified)
	{
		$this->allowAdminAccessOnly();
		$this->filename = 'jmenovky.pdf';
		$templateName = 'name_badge';

		$badges = [];
		if (!$namesStringified) {
			$badges = $this->getModel()->nameBadges();
		} else {
			$namesStringified = preg_replace('/\s+/', '', $namesStringified);

			$names = explode(',', $namesStringified);
			foreach ($names as $name) {
				$badge['nick'] = $name;
				$badges[] = $badge;
			}
		}

		$this->getPdf()->setMargins(15, 15, 10, 5);
		$this->getPdf()->SetWatermarkImage(IMG_DIR . 'logos/watermark-waves.jpg', 0.1, '');
		$this->getPdf()->showWatermarkImage = true;

		$parameters = [
			'result' => $badges,
		];

		$this->forgeView($templateName, $parameters);
		$this->publish();
	}

	/**
	 * Print data of visitors into excel file.
	 */
	public function renderVisitorsExcel()
	{
		$this->allowAdminAccessOnly();
		$excel = $this->getExcel();

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

		foreach ($cells as $key => $value) {
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

		foreach ($dimensions as $key => $value) {
			$excel->getActiveSheet()->getColumnDimension($key)->setWidth($value);
		}

		$visitors = $this->getModel()->visitorsExcel();

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
			'T' => 'question2',
			'U' => 'all',
			'V' => 'fry_dinner',
			'W' => 'sat_breakfast',
			'X' => 'sat_lunch',
			'Y' => 'sat_dinner',
			'Z' => 'sun_breakfast',
			'AA' => 'sun_lunch',
		];

		$i = 2;
		foreach ($visitors as $data) {
			foreach ($cellValues as $cell => $value) {
				$list->setCellValue($cell . $i, $data[$value]);
			}
			$i++;
		}

		// stahnuti souboru
		$filename = 'export-MS-' . date('Y-m-d', time()) . '.xlsx';

		$excel->setActiveSheetIndex(0);

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$excelWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$excelWriter->save('php://output');
		exit;
	}

	protected function forgeView(string $name = '', array $parameters = []): self
	{
		$template = $this->getTemplate();

		foreach ($parameters as $parameter => $value) {
			$template->{$parameter} = $value;
		}

		$this->setView($name);
		$template->setFile(
			sprintf(
				'%s%s.%s',
				self::TEMPLATE_DIR,
				$name,
				self::TEMPLATE_EXT
			)
		);

		return $this;
	}

	protected function publish()
	{
		$template = $this->getTemplate();

		/* debugging */
		if ($this->debugMode) {
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// write html
			$this->getPdf()->WriteHTML((string) $template, 0);
			// download
			$this->getPdf()->Output($this->filename, "D");
			exit;
		}
	}

	protected function createComponentRegistrationGraph(): RegistrationGraphControl
	{
		return $this->registrationGraphControl->setMeetingId($this->getMeetingId());
	}

	protected function setRegistrationGraphControl(RegistrationGraphControl $control): self
	{
		$this->registrationGraphControl = $control;

		return $this;
	}

	protected function createComponentMaterials(): MaterialsControl
	{
		return $this->materialControl->setMeetingId($this->getMeetingId());
	}

	protected function setMaterialControl(MaterialsControl $control): self
	{
		$this->materialControl = $control;

		return $this;
	}

	protected function createComponentMeal(): MealControl
	{
		return $this->mealControl->setMeetingId($this->getMeetingId());
	}

	protected function setMealControl(MealControl $control): self
	{
		$this->mealControl = $control;

		return $this;
	}

	protected function calculateGraphHeight(): int
	{
		$graphHeight = RegistrationGraphControl::GRAPH_HEIGHT_INIT;

		foreach ($this->getModel()->graph() as $graph) {
			$graphHeight += RegistrationGraphControl::GRAPH_HEIGHT_STEP;
		}

		if ($graphHeight < RegistrationGraphControl::GRAPH_HEIGHT_MIN) {
			$graphHeight = RegistrationGraphControl::GRAPH_HEIGHT_MIN;
		}

		return $graphHeight;
	}

	protected function getProgramModel(): ProgramModel
	{
		return $this->programModel;
	}

	protected function setBlockModel(BlockModel $model): self
	{
		$this->blockModel = $model;

		return $this;
	}

	protected function getBlockModel(): BlockModel
	{
		return $this->blockModel;
	}

	protected function setProgramModel(ProgramModel $model): self
	{
		$this->programModel = $model;

		return $this;
	}

	protected function getExcel(): PHPExcel
	{
		return $this->excel;
	}

	protected function setExcel(PHPExcel $excel): self
	{
		$this->excel = $excel;

		return $this;
	}

	protected function getPdf(): Mpdf
	{
		return $this->pdf;
	}

	protected function setPdf(Mpdf $pdf): self
	{
		$this->pdf = $pdf;

		return $this;
	}
}
