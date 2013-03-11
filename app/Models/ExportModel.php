<?php
/**
 * Export Model
 * 
 * class for exporting materials for printing
 *
 * @created 2012-09-21
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ExportModel extends CodeplexModel
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
	
	/** Constructor */
	public function __construct($meetingId, PdfFactory $PdfFactory, View $View, Program $Program, ExcelFactory $ExcelFactory)
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
	}
	
	public function setGraphHeight($height)
	{
		$this->graphHeight = $height;	
	}
	
	public function getGraphHeight()
	{
		return $this->graphHeight;
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
		
		$query = "SELECT	vis.id AS id,
				name,
				surname,
				nick,
				DATE_FORMAT(birthday, '%Y-%m-%d') AS birthday,
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
				fry_dinner,
				sat_breakfast,
				sat_lunch,
				sat_dinner,
				sun_breakfast,
				sun_lunch,
				bill,
				place,
				DATE_FORMAT(start_date, '%d. -') AS start_date,
				DATE_FORMAT(end_date, '%d. %m. %Y') AS end_date
		FROM kk_visitors AS vis
		LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
		LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
		LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
		WHERE meeting='".$this->meetingId."' AND vis.deleted='0'
		";
		$result = mysql_query($query);

		// load and prepare template
		$this->View->loadTemplate('exports/meal_ticket');
		$this->View->assign('result', $result);
		$template = $this->View->render(false);

		$this->Pdf = $this->createPdf();

		// write html
		$this->Pdf->WriteHTML($template, 0);
		
		/* debugging */
		if(defined('DEBUG') && DEBUG === true){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$this->Pdf->Output($outputFilename, "D");
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
		
		$query = "SELECT	vis.id AS id,
						name,
						surname,
						nick,
						DATE_FORMAT(birthday, '%d. %m. %Y') AS birthday,
						street,
						city,
						postal_code,
						group_num,
						group_name,
						place,
						DATE_FORMAT(start_date, '%Y') AS year
				FROM kk_visitors AS vis
				LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
				WHERE meeting='".$this->meetingId."' AND vis.deleted='0'
				ORDER BY surname ASC
				";
		$result = mysql_query($query);

		// load and prepare template
		$this->View->loadTemplate('exports/attendance');
		$this->View->assign('result', $result);
		$template = $this->View->render(false);
		
		// prepare header
		$header_data = mysql_fetch_assoc($result);
		$attendance_header = $header_data['place']." ".$header_data['year'];

		$this->Pdf = $this->createPdf();
	
		// set header
		$this->Pdf->SetHeader($attendance_header.'|sraz VS|Prezenční listina');
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
		
		$query = "SELECT	vis.id AS id,
					name,
					surname,
					street,
					city,
					postal_code,
					bill,
					place,
					UPPER(LEFT(place, 2)) AS abbr_place,
					DATE_FORMAT(start_date, '%d. %m. %Y') AS date,
					DATE_FORMAT(start_date, '%Y') AS year,
					cost - bill AS balance,
					DATE_FORMAT(birthday, '%d. %m. %Y') AS birthday,
					group_num,
					numbering
			FROM kk_visitors AS vis
			LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
			WHERE ".$specific_visitor." meeting='".$this->meetingId."' AND vis.deleted='0'
			ORDER BY surname, name
			".$evidence_limit."";
		$result = mysql_query($query);

		// summary header
		$hkvs_header = "Junák ČR, Kapitanát vodních skautů | ";
		$hkvs_header .= "Senovážné náměstí 977/24, Praha 1, 116 47 | ";
		$hkvs_header .= "IČ: 65991753, ČÚ: 2300183549/2010";

		$this->Pdf = $this->createPdf();

		// load and prepare template
		$this->View->loadTemplate('exports/evidence_header');
		$this->View->assign('header', $this->View->render(false));
		// multiple evidence type/template
		switch($evidence_type){
			case "summary":
				$this->View->loadTemplate('exports/evidence_summary');
				// specific mPDF settings
				$this->Pdf->defaultfooterfontsize = 16;
				$this->Pdf->defaultfooterfontstyle = 'B';
				$this->Pdf->SetHeader($hkvs_header);
				break;
			case "confirm":
				$this->View->loadTemplate('exports/evidence_confirm');
				break;
			default:
				$this->View->loadTemplate('exports/evidence');
				break;
		}
		$this->View->assign('LOGODIR', $GLOBALS['LOGODIR']);
		$this->View->assign('result', $result);
		$template = $this->View->render(false);
		
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
	 * Print Program into PDF file
	 *
	 * @param	string	type of evidence
	 * @param	int		ID of visitor
	 * @param	string	file type
	 * @return	file	PDF file
	 */
	public function printProgramCards($evidence_type, $visitor_id = NULL, $file_type = "pdf")
	{
		$sql = "SELECT	vis.id AS id,
						name,
						surname,
						nick,
						DATE_FORMAT(birthday, '%Y-%m-%d') AS birthday,
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
						fry_dinner,
						sat_breakfast,
						sat_lunch,
						sat_dinner,
						sun_breakfast,
						sun_lunch,
						bill,
						place,
						DATE_FORMAT(start_date, '%d. -') AS start_date,
						DATE_FORMAT(end_date, '%d. %m. %Y') AS end_date
				FROM kk_visitors AS vis
				LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
				LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
				LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
				WHERE meeting='".$this->meetingId."' AND vis.deleted='0'
				";
		$result = mysql_query($sql);
		//$data = mysql_fetch_assoc($result);
		
		////ziskani zvolenych programu
		$blockSql = "SELECT 	id
					 FROM kk_blocks
					 WHERE meeting='".$mid."' AND program='1' AND deleted='0'";
		$blockResult = mysql_query($blockSql);
		while($blockData = mysql_fetch_assoc($blockResult)){
			$$blockData['id'] = requested($blockData['id'],0);
			//echo $blockData['id'].":".$$blockData['id']."|";
		}
	
		// load and prepare template
		$this->View->loadTemplate('exports/program_cards');
		$this->View->assign('result', $result);
		//$this->View->assign('blocks' getBlocks($data['id']));
		$template = $this->View->render(false);
		
		// prepare header
		$header_data = mysql_fetch_assoc($result);
		$attendance_header = $header_data['place']." ".$header_data['year'];

		$this->Pdf = $this->createPdf();

		$this->Pdf->SetWatermarkImage($GLOBALS['LOGODIR'].'watermark.jpg', 0.1, '');
		$this->Pdf->showWatermarkImage = true;
		
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
	
	public function printNameBadges()
	{
		$output_filename = "jmenovky.pdf";
		
		$sql = "SELECT	vis.id AS id,
						nick
				FROM kk_visitors AS vis
				WHERE meeting='".$this->meetingId."' AND vis.deleted='0'
				";
		$result = mysql_query($sql);
		
		// load and prepare template
		$this->View->loadTemplate('exports/name_badge');
		$this->View->assign('result', $result);
		$template = $this->View->render(FALSE);
		
		$this->PdfFactory->setMargins(15, 15, 10, 5);
		$this->Pdf = $this->PdfFactory->create();
		
		// set watermark
		$this->Pdf->SetWatermarkImage($GLOBALS['LOGODIR'].'watermark-waves.jpg', 0.1, '');
		$this->Pdf->showWatermarkImage = TRUE;
		
		// write html
		$this->Pdf->WriteHTML($template, 0);
		
		/* debugging */
		if(defined('DEBUG') && DEBUG === TRUE){
			echo $template;
			exit('DEBUG_MODE');
		} else {
			// download
			$this->Pdf->Output($output_filename, "D");
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

		$graph_query = "SELECT DATE_FORMAT(reg_daytime, '%d. %m. %Y') AS day, 
							   COUNT(reg_daytime) AS reg_count
						FROM `kk_visitors` AS vis
						LEFT JOIN kk_meetings AS meet ON meet.id = vis.meeting 
						WHERE meet.id = ".$this->meetingId." AND vis.deleted='0'
						GROUP BY day
						ORDER BY reg_daytime ASC";
		$graph_result = mysql_query($graph_query);
		
		$max_query = "SELECT MAX( reg_count ) AS max
					  FROM (
						SELECT DATE_FORMAT( reg_daytime, '%d. %m. %Y' ) AS
							DAY , COUNT( reg_daytime ) AS reg_count
						FROM `kk_visitors` AS vis
						LEFT JOIN kk_meetings AS meet ON meet.id = vis.meeting
						WHERE meet.id = '".$this->meetingId."'
							AND vis.deleted = '0'
						GROUP BY DAY
					  ) AS cnt";
		$max_result = mysql_query($max_query);
		$max_data = mysql_fetch_assoc($max_result);
		
		$reg_graph = "<table style='width:100%;'>";
		
		$graph_height = 0;
		
		while($graph_data = mysql_fetch_array($graph_result)){
				// trojclenka pro zjisteni pomeru sirky grafu...(aby nam to nevylezlo mimo obrazovku)
		/*var_dump($max);
		var_dump($graph_width);
		var_dump($graph_data['reg_count']);
		echo 	$width = ceil(($graph_width/$max)*$graph_data['reg_count'])."\n";*/
			$width = ceil($graph_width/$max_data['max']*$graph_data['reg_count']);
			$reg_graph .= "<tr><td align='right' style='width:60px;'>".$graph_data['day']."</td><td><img src='../images/graph.png' alt='".$graph_data['reg_count']."' style='width:".$width."%;' height='12' border='0'>".$graph_data['reg_count']."</td>";
			
			$graph_height += 21.5; 
		}
				   
		$reg_graph .= "</table>";
		
		if($graph_height < 260) $graph_height = 260;
		
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
		$sql = "SELECT 	progs.id AS id,
						progs.name AS name,
						progs.material AS material
				FROM `kk_programs` AS progs
				LEFT JOIN `kk_blocks` AS bls ON progs.block = bls.id
				WHERE progs.deleted = '0'
					AND bls.meeting = '".$this->meetingId."'
					AND bls.deleted = '0'";
		$result = mysql_query($sql);
		
		$html = "";
		while($data = mysql_fetch_assoc($result)){
			if($data['material'] == "") $material = "(žádný)";
			else $material = $data['material'];
			$html .= "<div><a rel='programDetail' href='../programs/process.php?id=".$data['id']."&cms=edit' title='".$data['name']."'>".$data['name'].":</a>\n</div>";
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
		$query = "SELECT SUM(bill) AS account,
							COUNT(bill) * cost AS suma,
							COUNT(bill) * cost - SUM(bill) AS balance
					FROM kk_visitors AS vis
					LEFT JOIN kk_meetings AS meets ON vis.meeting = meets.id
					WHERE meeting = '".$this->meetingId."' AND vis.deleted = '0'";
		$result = mysql_query($query);
		$data = mysql_fetch_assoc($result);	
		
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
	
	public function getMealCount($meal)
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
	}
}