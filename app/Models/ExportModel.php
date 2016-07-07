<?php
/**
 * Export Model
 *
 * class for exporting materials for printing
 *
 * @created 2012-09-21
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ExportModel extends NixModel
{
	/** @var int meeting ID */
	private $meetingId;

	/** @var MpdfFactory */
	private $PdfFactory;

	/** @var Pdf */
	public $Pdf;

	/** @var View */
	private $View;

	/** @var Program Programs class */
	public $Program;

	/** @var PHPExcel PHPExcel class */
	private $Excel;

	/** @var int graph height */
	private $graphHeight;

	/** @var Connection database */
	private $database;

	/** Constructor */
	public function __construct($meetingId, PdfFactory $PdfFactory, View $View, ProgramModel $Program, ExcelFactory $ExcelFactory, $database)
	{
		$this->meetingId = $meetingId;
		// use PdfFactory
		$this->PdfFactory = $PdfFactory;
		// create mPdf with default settings
		//$this->Pdf = $PdfFactory->create();
		// templating system
		$this->View = $View;
		$this->Program = $Program;
		$this->Excel = $ExcelFactory->create();
		$this->database = $database;
	}

	public function setGraphHeight($height)
	{
		$this->graphHeight = $height;
	}

	public function getGraphHeight()
	{
		return $this->graphHeight;
	}

	public function setMeetingId($id)
	{
		$this->meetingId = $id;
	}

	/**
	 * Create PDF
	 *
	 * @return	mPDF
	 */
	public function createPdf()
	{
		return $this->PdfFactory->create();
	}

	/**
	 * Print Attendance into PDF file
	 *
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printMealTicket($fileType = "pdf")
	{
		// output file name
		$outputFilename= "vlastni_stravenky.".$fileType;

		$data = $this->database->query('SELECT	vis.id AS id,
				name,
				surname,
				nick,
				DATE_FORMAT(birthday, "%Y-%m-%d") AS birthday,
				street,
				city,
				postal_code,
				province,
				province_name,
				group_num,
				group_name,
				troop_name,
				comment,
				arrival,
				departure,
				question,
				question2,
				fry_dinner,
				sat_breakfast,
				sat_lunch,
				sat_dinner,
				sun_breakfast,
				sun_lunch,
				bill,
				place,
				DATE_FORMAT(start_date, "%d. -") AS start_date,
				DATE_FORMAT(end_date, "%d. %m. %Y") AS end_date
		FROM kk_visitors AS vis
		LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
		LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
		LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
		WHERE meeting = ? AND vis.deleted = ?
		', $this->meetingId, '0')->fetchAll();

		// load and prepare template
		$this->View->loadTemplate('exports/meal_ticket');
		$this->View->assign('result', $data);
		$template = $this->View->render(false);

		$pdf = $this->createPdf();

		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$pdf->Output($outputFilename, "D");
		}
	}

	/**
	 * Print Attendance into PDF file
	 *
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printAttendance($file_type = "pdf")
	{
		// output file name
		$output_filename = "attendance_list.".$file_type;

		$data = $this->database->query('SELECT	vis.id AS id,
						name,
						surname,
						nick,
						DATE_FORMAT(birthday, "%d. %m. %Y") AS birthday,
						street,
						city,
						postal_code,
						group_num,
						group_name,
						place,
						DATE_FORMAT(start_date, "%Y") AS year
				FROM kk_visitors AS vis
				LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
				WHERE meeting = ? AND vis.deleted = ?
				ORDER BY surname ASC',
				$this->meetingId, '0')->fetchAll();

		// load and prepare template
		$this->View->loadTemplate('exports/attendance');
		$this->View->assign('result', $data);
		$template = $this->View->render(false);

		// prepare header
		$attendance_header = $data[0]['place']." ".$data[0]['year'];

		$pdf = $this->createPdf();

		// set header
		$pdf->SetHeader($attendance_header.'|sraz VS|Prezenční listina');
		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$pdf->Output($output_filename, "D");
		}
	}

	/**
	 * Print name list into PDF file
	 *
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printNameList($file_type = "pdf")
	{
		// output file name
		$output_filename = "name_list.".$file_type;

		$data = $this->database->query('SELECT	vis.id AS id,
						name,
						surname,
						nick,
						DATE_FORMAT(birthday, "%d. %m. %Y") AS birthday,
						street,
						city,
						postal_code,
						group_num,
						group_name,
						place,
						DATE_FORMAT(start_date, "%Y") AS year
				FROM kk_visitors AS vis
				LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
				WHERE meeting = ? AND vis.deleted = ?
				ORDER BY nick ASC
				', $this->meetingId, '0')->fetchAll();

		// load and prepare template
		$this->View->loadTemplate('exports/name_list');
		$this->View->assign('result', $data);
		$template = $this->View->render(false);

		// prepare header
		$namelist_header = $data[0]['place']." ".$data[0]['year'];

		$pdf = $this->createPdf();

		// set header
		$pdf->SetHeader($namelist_header.'|sraz VS|Jméno, Příjmení, Přezdívka');
		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$pdf->Output($output_filename, "D");
		}
	}

	/**
	 * Print Evidence into PDF file
	 *
	 * @param	string	type of evidence
	 * @param	int		ID of visitor
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printEvidence($evidence_type, $visitor_id = NULL, $file_type = "pdf")
	{
		$evidence_limit = "";
		$specific_visitor = "";

		if(isset($visitor_id) && $visitor_id != NULL){
			$evidence_limit = "LIMIT 1";
			$specific_visitor = "vis.id='".$visitor_id."' AND";
		}

		// output file name
		$output_filename = "faktura.".$file_type;

		$data = $this->database->query('SELECT	vis.id AS id,
					name,
					surname,
					street,
					city,
					postal_code,
					bill,
					place,
					UPPER(LEFT(place, 2)) AS abbr_place,
					DATE_FORMAT(start_date, "%d. %m. %Y") AS date,
					DATE_FORMAT(start_date, "%Y") AS year,
					vis.cost - bill AS balance,
					DATE_FORMAT(birthday, "%d. %m. %Y") AS birthday,
					group_num,
					numbering
			FROM kk_visitors AS vis
			LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
			WHERE ' . $specific_visitor . ' meeting = ? AND vis.deleted = ?
			ORDER BY surname, name
			' . $evidence_limit, $this->meetingId, '0')->fetchAll();

		// summary header
		$hkvs_header = "Junák - český skaut, Kapitanát vodních skautů, z. s. | ";
		$hkvs_header .= "Senovážné náměstí 977/24, Praha 1, 110 00 | ";
		$hkvs_header .= "IČ: 65991753, ČÚ: 2300183549/2010";

		$pdf = $this->createPdf();

		// load and prepare template
		$this->View->loadTemplate('exports/evidence_header');
		$this->View->assign('header', $this->View->render(false));
		// multiple evidence type/template
		switch($evidence_type){
			case "summary":
				$this->View->loadTemplate('exports/evidence_summary');
				// specific mPDF settings
				$pdf->defaultfooterfontsize = 16;
				$pdf->defaultfooterfontstyle = 'B';
				$pdf->SetHeader($hkvs_header);
				break;
			case "confirm":
				$this->View->loadTemplate('exports/evidence_confirm');
				break;
			default:
				$this->View->loadTemplate('exports/evidence');
				break;
		}
		$this->View->assign('LOGODIR', IMG_DIR.'logos/');
		$this->View->assign('result', $data);
		$template = $this->View->render(false);

		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$pdf->Output($output_filename, "D");
		}
	}

	public static function getPdfBlocks($vid, $database)
	{
		$programs = "<tr>";
		$programs .= " <td class='progPart'>";

		$data = $database->query('SELECT 	id,
							day,
							DATE_FORMAT(`from`, "%H:%i") AS `from`,
							DATE_FORMAT(`to`, "%H:%i") AS `to`,
							name,
							program
					FROM kk_blocks
					WHERE deleted = ? AND program = ? AND meeting = ?
					ORDER BY `day` ASC', '0', '1', $_SESSION['meetingID'])->fetchAll();

		if(!$data){
			$programs .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
		} else {
			foreach($data as $progData) {
				// zbaveni se predsnemovni diskuse
				if($progData['id'] == 63) $programs .= "";
				else {
					$programs .= "<div class='block'>".$progData['day'].", ".$progData['from']." - ".$progData['to']." : ".$progData['name']."</div>\n";

					if($progData['program'] == 1) $programs .= "<div>".\ProgramModel::getPdfPrograms($progData['id'], $vid, $database)."</div>";
				}
			}
		}

		$programs .= "</td>";
		$programs .= "</tr>";

		return $programs;
	}

	/**
	 * Print Program into PDF file
	 *
	 * @param	string	type of evidence
	 * @param	int		ID of visitor
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printProgramCards($file_type = "pdf")
	{
		$filename = "vlastni_programy";
		$fileaddr = "../tmp/".$filename.".".$file_type;

		$data = $this->database->query('SELECT	vis.id AS id,
						name,
						surname,
						nick,
						DATE_FORMAT(birthday, "%Y-%m-%d") AS birthday,
						street,
						city,
						postal_code,
						province,
						province_name,
						group_num,
						group_name,
						troop_name,
						comment,
						arrival,
						departure,
						question,
						question2,
						fry_dinner,
						sat_breakfast,
						sat_lunch,
						sat_dinner,
						sun_breakfast,
						sun_lunch,
						bill,
						place,
						DATE_FORMAT(start_date, "%d. -") AS start_date,
						DATE_FORMAT(end_date, "%d. %m. %Y") AS end_date,
						DATE_FORMAT(start_date, "%Y") AS year
				FROM kk_visitors AS vis
				LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
				LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
				LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
				WHERE meeting = ? AND vis.deleted = ?
				ORDER BY nick ASC
				', $this->meetingId, '0')->fetchAll();

		////ziskani zvolenych programu
		$blocks = $this->database->query('SELECT 	id
					 FROM kk_blocks
					 WHERE meeting = ? AND program = ? AND deleted = ?', $this->meetingId, '1', '0')->fetchAll();

		foreach($blocks as $block){
			$$block['id'] = requested($block['id'],0);
			//echo $blockData['id'].":".$$blockData['id']."|";
		}

		// load and prepare template
		$this->View->loadTemplate('exports/program_cards');
		$this->View->assign('result', $data);
		$this->View->assign('database', $this->database);
		//$this->View->assign('blocks' getBlocks($data['id']));
		$template = $this->View->render(false);

		// prepare header
		$attendance_header = $data[0]['place']." ".$data[0]['year'];

		$pdf = $this->createPdf();

		$pdf->SetWatermarkImage(IMG_DIR.'logos/watermark.jpg', 0.1, '');
		$pdf->showWatermarkImage = true;

		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$output_filename = $filename.'.'.$file_type;
			$pdf->Output($output_filename, "D");
		}

	}

	public static function getLargeProgramData($meeting_id, $day_val, $database)
	{
		return $database->query('SELECT 	blocks.id AS id,
						day,
						DATE_FORMAT(`from`, "%H:%i") AS `from`,
						DATE_FORMAT(`to`, "%H:%i") AS `to`,
						blocks.name AS name,
						program,
						display_progs,
						style
			FROM kk_blocks AS blocks
			LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
			WHERE blocks.deleted = ? AND day = ? AND meeting = ?
			ORDER BY `from` ASC', '0', $day_val, $meeting_id)->fetchAll();
	}

	/**
	 * Print Program into PDF file
	 *
	 * @param	string	type of evidence
	 * @param	int		ID of visitor
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printLargeProgram($file_type = "pdf")
	{
		// prepare header
		$data = $this->database->query('SELECT	id,
						place,
						DATE_FORMAT(start_date, "%Y") AS year,
						UNIX_TIMESTAMP(open_reg) AS open_reg,
						UNIX_TIMESTAMP(close_reg) as close_reg
				FROM kk_meetings
				WHERE id = ?
				ORDER BY id DESC
				LIMIT 1', $this->meetingId)->fetch();

		$meeting_header = $data['place']." ".$data['year'];
		$filename = removeDiacritic($data['place'].$data['year']."-program");

		// load and prepare template
		$this->View->loadTemplate('exports/program_large');
		$this->View->assign('header', $meeting_header);
		$this->View->assign('meeting_id', $this->meetingId);
		$this->View->assign('database', $this->database);

		$template = $this->View->render(false);

		$this->pdf->setPaperFormat('B1');
		$pdf = $this->pdf->create();

		$pdf->useOnlyCoreFonts = true;
		$pdf->SetDisplayMode('fullpage');
		$pdf->autoScriptToLang = false;

		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$output_filename = $filename.'.'.$file_type;
			$pdf->Output($output_filename, "D");
		}

	}

	/**
	 * Print Program into PDF file
	 *
	 * @param	string	type of evidence
	 * @param	int		ID of visitor
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printPublicProgram($file_type = "pdf")
	{
		// prepare header
		$data = $this->database->query('SELECT	id,
						place,
						DATE_FORMAT(start_date, "%Y") AS year,
						UNIX_TIMESTAMP(open_reg) AS open_reg,
						UNIX_TIMESTAMP(close_reg) as close_reg
				FROM kk_meetings
				WHERE id = ?
				ORDER BY id DESC
				LIMIT 1', $this->meetingId)->fetch();

		$meeting_header = $data['place']." ".$data['year'];
		$filename = removeDiacritic($data['place'].$data['year']."-program");

		// load and prepare template
		$this->View->loadTemplate('exports/program_public');
		$this->View->assign('header', $meeting_header);
		$this->View->assign('meeting_id', $this->meetingId);
		$this->View->assign('database', $this->database);
		$this->View->assign('styles', $this->category->getStyles());

		$template = $this->View->render(false);

		$pdf = $this->pdf->create();

		$pdf->useOnlyCoreFonts = true;
		$pdf->SetDisplayMode('fullpage');
		$pdf->autoScriptToLang = false;

		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$output_filename = $filename.'.'.$file_type;
			$pdf->Output($output_filename, "D");
		}

	}

	/**
	 * Print Program into PDF file
	 *
	 * @param	string	type of evidence
	 * @param	int		ID of visitor
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printProgramBadges($file_type = "pdf")
	{
		// prepare header
		$data = $this->database->query('SELECT	*
			FROM kk_visitors AS vis
			WHERE meeting = ? AND vis.deleted = ?', $this->meetingId, '0')->fetchAll();

		$filename = 'program-badge';

		// load and prepare template
		$this->View->loadTemplate('exports/program_badge');
		$this->View->assign('meeting_id', $this->meetingId);
		$this->View->assign('result', $data);
		$this->View->assign('database', $this->database);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true) {
			$template = $this->View->render(true);
			exit('DEBUG_MODE');
		} else {
			$template = $this->View->render(false);
		}

		$this->pdf->setMargins(5, 5, 9, 5);
		$pdf = $this->pdf->create();

		$pdf->useOnlyCoreFonts = true;
		$pdf->SetDisplayMode('fullpage');
		$pdf->autoScriptToLang = false;

		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(!defined('DEBUG') || DEBUG === FALSE){
			// download
			$output_filename = $filename.'.'.$file_type;
			$pdf->Output($output_filename, "D");
		}

	}

	public function printNameBadges($names = NULL)
	{
		$output_filename = "jmenovky.pdf";

		$_data = array();

		if(empty($names)) {
			$data = $this->database->query('SELECT	vis.id AS id,
							nick
					FROM kk_visitors AS vis
					WHERE meeting = ? AND vis.deleted = ?', $this->meetingId, '0')->fetchAll();

			foreach($data as $row) {
				array_push($_data, $row);
			}
		} else {
			$names = preg_replace('/\s+/','',$names);

			$values = explode(',',$names);
			foreach($values as $value) {
				$row['nick'] = $value;
				array_push($_data, $row);
			}
		}

		// load and prepare template
		$this->View->loadTemplate('exports/name_badge');
		$this->View->assign('result', $_data);
		$template = $this->View->render(FALSE);

		$this->pdf->setMargins(15, 15, 10, 5);
		$pdf = $this->pdf->create();

		// set watermark
		$pdf->SetWatermarkImage(IMG_DIR.'logos/watermark-waves.jpg', 0.1, '');
		$pdf->showWatermarkImage = TRUE;

		// write html
		$pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === TRUE){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$pdf->Output($output_filename, "D");
		}
	}

	/**
	 * Generate registration graph
	 *
	 * @return	mixed	html
	 */
	public function renderGraph()
	{
		$graph_width = 94;

		$graph = $this->database
			->query('SELECT DATE_FORMAT(reg_daytime, "%d. %m. %Y") AS day,
							   COUNT(reg_daytime) AS reg_count
						FROM `kk_visitors` AS vis
						LEFT JOIN kk_meetings AS meet ON meet.id = vis.meeting
						WHERE meet.id = ? AND vis.deleted = ?
						GROUP BY day
						ORDER BY reg_daytime ASC',
						$this->meetingId, '0')->fetchAll();

		$graphMax = $this->database
			->query('SELECT MAX( reg_count ) AS max
					  FROM (
						SELECT DATE_FORMAT( reg_daytime, "%d. %m. %Y" ) AS
							DAY , COUNT( reg_daytime ) AS reg_count
						FROM `kk_visitors` AS vis
						LEFT JOIN kk_meetings AS meet ON meet.id = vis.meeting
						WHERE meet.id = ?
							AND vis.deleted = ?
						GROUP BY DAY
					  ) AS cnt',
					  $this->meetingId, '0')->fetch();

		$reg_graph = "<table style='width:100%;'>";

		$graph_height = 0;

		foreach($graph as $graphRow) {
				// trojclenka pro zjisteni pomeru sirky grafu...(aby nam to nevylezlo mimo obrazovku)
		/*var_dump($max);
		var_dump($graph_width);
		var_dump($graph_data['reg_count']);
		echo 	$width = ceil(($graph_width/$max)*$graph_data['reg_count'])."\n";*/
			$width = ceil($graph_width/$graphMax['max']*$graphRow['reg_count']);
			$reg_graph .= "<tr><td align='right' style='width:60px;'>".$graphRow['day']."</td><td><img src='".IMG_DIR."graph.png' alt='".$graphRow['reg_count']."' style='width:".$width."%;' height='12' border='0'>".$graphRow['reg_count']."</td>";

			$graph_height += 21.5;
		}

		$reg_graph .= "</table>";

		if($graph_height < 290) $graph_height = 290;

		$this->setGraphHeight($graph_height);

		return $reg_graph;
	}

	/**
	 * Get materials for each program
	 *
	 * @return	mixed	html
	 */
	public function getMaterial()
	{
		$data = $this->database
			->query('SELECT	progs.id AS id,
						progs.name AS name,
						progs.material AS material
				FROM `kk_programs` AS progs
				LEFT JOIN `kk_blocks` AS bls ON progs.block = bls.id
				WHERE progs.deleted = ?
					AND bls.meeting = ?
					AND bls.deleted = ?',
					'0', $this->meetingId, '0')->fetchAll();

		$html = "";
		foreach($data as $item){
			if($item['material'] == "") $material = "(žádný)";
			else $material = $item['material'];
			$html .= "<div><a rel='programDetail' href='".PRJ_DIR."program/?id=".$item['id']."&cms=edit&page=export' title='".$item['name']."'>".$item['name']."</a>:\n</div>";
			$html .= "<div style='margin-left:10px;font-size:12px;font-weight:bold;'>".$material."</div>";
		}

		return $html;
	}

	/**
	 * Get materials for each program
	 *
	 * @param	string	type of money (account|balance|suma)
	 * @return	mixed	amount of money or false if error
	 */
	public function getMoney($type)
	{
		$data = $this->database
			->query('SELECT SUM(bill) AS account,
							COUNT(bill) * vis.cost AS suma,
							COUNT(bill) * vis.cost - SUM(bill) AS balance
					FROM kk_visitors AS vis
					LEFT JOIN kk_meetings AS meets ON vis.meeting = meets.id
					WHERE meeting = ? AND vis.deleted = ?',
					$this->meetingId, '0')->fetch();

		switch($type){
			case "account":
				return $data['account'];
				break;
			case "balance":
				return $data['balance'];
				break;
			case "suma":
				return $data['suma'];
				break;
			default:
				return FALSE;
				break;
		}
	}

	/**
	 * Get count of meals
	 *
	 * @param	string	name of meal
	 * @return	array	meal name => count
	 */
	public function getMealCount($meal)
	{
		$data = $this->database
			->query('SELECT count(?) AS ?
				FROM `kk_meals` AS mls
				LEFT JOIN `kk_visitors` AS vis ON vis.id = mls.visitor
				WHERE vis.deleted = ?
					AND vis.meeting = ?
					AND ' . $meal . ' = ?',
					$meal, $meal, '0', $_SESSION['meetingID'], 'ano')->fetch();

		return $data[$meal];
	}

	/**
	 * Render data from getMealCount
	 *
	 * @return	mixed	html
	 */
	public function renderMealCount()
	{
		$mealsArr = array("fry_dinner" => "páteční večeře",
						  "sat_breakfast" => "sobotní snídaně",
						  "sat_lunch" => "sobotní oběd",
						  "sat_dinner" => "sobotní večeře",
						  "sun_breakfast" => "nedělní snídaně",
						  "sun_lunch" => "nedělní oběd");

		$meals = "<table>";

		foreach($mealsArr as $mealsKey => $mealsVal){
			$mealCount = $this->getMealCount($mealsKey);

			$meals .= "<tr><td>".$mealsVal.":</td><td><span style='font-size:12px; font-weight:bold;'>".$mealCount."</span></td></tr>";
		}

		$meals .= "</table>";

		return $meals;
	}

	/**
	 * Print visitors on program into PDF file
	 *
	 * @param	string	type of evidence
	 * @param	int		ID of visitor
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printProgramVisitors($programId)
	{
		$output_filename = "ucastnici-programu.pdf";

		$query = "SELECT vis.name AS name,
							vis.surname AS surname,
							vis.nick AS nick,
							prog.name AS program
					FROM kk_visitors AS vis
					LEFT JOIN `kk_visitor-program` AS visprog ON vis.id = visprog.visitor
					LEFT JOIN `kk_programs` AS prog ON prog.id = visprog.program
					WHERE visprog.program = '".$programId."' AND vis.deleted = '0'";
		$result = mysql_query($query);

		// load and prepare template
		$this->View->loadTemplate('exports/program_visitors');
		$this->View->assign('result', $result);
		$template = $this->View->render(false);

		// prepare header
		$result = mysql_query($query);
		$header_data = mysql_fetch_assoc($result);
		$program_header = $header_data['program'];

		$this->Pdf = $this->createPdf();

		// set header
		$this->Pdf->SetHeader($program_header.'|sraz VS|Účastnící programu');
		// write html
		$this->Pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$this->Pdf->Output($output_filename, "D");
		}
	}

	/**
	 * Print details of program into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function printProgramDetails()
	{
		$output_filename = "vypis-programu.pdf";

		$query = "SELECT prog.name AS name,
						 prog.description AS description,
						 prog.tutor AS tutor,
						 prog.email AS email
					FROM kk_programs AS prog
					LEFT JOIN `kk_blocks` AS block ON block.id = prog.block
					WHERE block.meeting = '".$this->meetingId."'
						AND prog.deleted = '0'
						AND block.deleted = '0'";
		$result = mysql_query($query);

		// load and prepare template
		$this->View->loadTemplate('exports/program_details');
		$this->View->assign('result', $result);
		$template = $this->View->render(false);

		$this->Pdf = $this->createPdf();

		// set header
		$this->Pdf->SetHeader('Výpis programů|sraz VS');
		// write html
		$this->Pdf->WriteHTML($template, 0);

		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$this->Pdf->Output($output_filename, "D");
		}
	}

	/**
	 * Print data of visitors into excel file
	 *
	 * @return	file	*.xlsx file type
	 */
	public function printVisitorsExcel()
	{
		$this->Excel->getProperties()->setCreator("HKVS Srazy K + K")->setTitle("Návštěvníci");

		// Zde si vyvoláme aktivní list (nastavený nahoře) a vyplníme buňky A1 a A2

		$list = $this->Excel->setActiveSheetIndex(0);

		$list->setCellValue('A1', 'ID');
		$list->setCellValue('B1', 'symbol');
		$list->setCellValue('C1', 'Jméno');
		$list->setCellValue('D1', 'Příjmení');
		$list->setCellValue('E1', 'Přezdívka');
		$list->setCellValue('F1', 'Narození');
		$list->setCellValue('G1', 'E-mail');
		$list->setCellValue('H1', 'Adresa');
		$list->setCellValue('I1', 'Město');
		$list->setCellValue('J1', 'PSČ');
		$list->setCellValue('K1', 'Kraj');
		$list->setCellValue('L1', 'Evidence');
		$list->setCellValue('M1', 'Středisko/Přístav');
		$list->setCellValue('N1', 'Oddíl');
		$list->setCellValue('O1', 'Účet');
		$list->setCellValue('P1', 'Připomínky');
		$list->setCellValue('Q1', 'Příjezd');
		$list->setCellValue('R1', 'Odjezd');
		$list->setCellValue('S1', 'Otázka');

		$this->Excel->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);

		$this->Excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$this->Excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$this->Excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
		$this->Excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
		$this->Excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$this->Excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
		$this->Excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
		$this->Excel->getActiveSheet()->getColumnDimension('M')->setWidth(30);
		$this->Excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
		$this->Excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
		$this->Excel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
		$this->Excel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
		$this->Excel->getActiveSheet()->getColumnDimension('S')->setWidth(20);

		$sql = "
		SELECT vis.id AS id,
			code,
			vis.name,
			surname,
			nick,
			DATE_FORMAT(birthday, '%d. %m. %Y') AS birthday,
			vis.email,
			street,
			city,
			postal_code,
			province_name AS province,
			group_num,
			group_name,
			troop_name,
			bill,
			`comment`,
			arrival,
			departure,
			question,
			question2,
			`all`,
			fry_dinner,
			sat_breakfast,
			sat_lunch,
			sat_dinner,
			sun_breakfast,
			sun_lunch,
			meeting
		FROM `kk_visitors` AS vis
		LEFT JOIN `kk_provinces` AS provs ON provs.id = vis.province
		/*LEFT JOIN `kk_visitor-program` AS visprog ON visprog.visitor = vis.id
		LEFT JOIN `kk_programs` AS progs ON visprog.program = progs.id*/
		LEFT JOIN `kk_meals` AS mls ON mls.visitor = vis.id
		WHERE vis.deleted = '0' AND meeting = '".$this->meetingId."'
		";

		$query = mysql_query($sql);

		$i = 2;
		while($data = mysql_fetch_assoc($query)){
			$list->setCellValue('A'.$i, $data['id']);
			$list->setCellValue('B'.$i, $data['code']);
			$list->setCellValue('C'.$i, $data['name']);
			$list->setCellValue('D'.$i, $data['surname']);
			$list->setCellValue('E'.$i, $data['nick']);
			$list->setCellValue('F'.$i, $data['birthday']);
			$list->setCellValue('G'.$i, $data['email']);
			$list->setCellValue('H'.$i, $data['street']);
			$list->setCellValue('I'.$i, $data['city']);
			$list->setCellValue('J'.$i, $data['postal_code']);
			$list->setCellValue('K'.$i, $data['province']);
			$list->setCellValue('L'.$i, $data['group_num']);
			$list->setCellValue('M'.$i, $data['group_name']);
			$list->setCellValue('N'.$i, $data['troop_name']);
			$list->setCellValue('O'.$i, $data['bill']);
			$list->setCellValue('P'.$i, $data['comment']);
			$list->setCellValue('Q'.$i, $data['arrival']);
			$list->setCellValue('R'.$i, $data['departure']);
			$list->setCellValue('S'.$i, $data['question']);
			$list->setCellValue('S'.$i, $data['question2']);
			$list->setCellValue('T'.$i, $data['all']);
			$list->setCellValue('U'.$i, $data['fry_dinner']);
			$list->setCellValue('V'.$i, $data['sat_breakfast']);
			$list->setCellValue('W'.$i, $data['sat_lunch']);
			$list->setCellValue('X'.$i, $data['sat_dinner']);
			$list->setCellValue('Y'.$i, $data['sun_breakfast']);
			$list->setCellValue('Z'.$i, $data['sun_lunch']);
			$i++;

		}

		// stahnuti souboru
		$filename = 'export-MS-'.date('Y-m-d',time()).'.xlsx';

		$this->Excel->setActiveSheetIndex(0);

		// clean output
		ob_clean();
		flush();

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		$ExcelWriter = PHPExcel_IOFactory::createWriter($this->Excel, 'Excel2007');
		$ExcelWriter->save('php://output');
		exit;
	}
}
