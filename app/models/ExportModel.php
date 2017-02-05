<?php

namespace App\Models;

use Nette\Database\Context;
use App\Models\CategoryModel;
use App\Models\BaseModel;

/**
 * Export Model
 *
 * class for exporting materials for printing
 *
 * @created 2012-09-21
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ExportModel extends BaseModel
{

	/** @var int graph height */
	private $graphHeight;

	/** @var CategoryModel categories */
	private $category;

	private static $connection;

	/** Constructor */
	public function __construct(Context $database, CategoryModel $category)
	{
		$this->setDatabase($database);
		$this->category = $category;
		self::$connection = $this->getDatabase();
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
	 * Return data for meal tickets
	 *
	 * @param	void
	 * @return	array
	 */
	public function mealTicket()
	{
		return $this->database->query('SELECT	vis.id AS id,
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
	}

	/**
	 * Print Attendance into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function attendance()
	{
		return $this->database->query('SELECT	vis.id AS id,
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
	}

	/**
	 * Return data for name list
	 *
	 * @param	void
	 * @return	array    data
	 */
	public function nameList()
	{
		return $this->database->query('SELECT	vis.id AS id,
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
	}

	/**
	 * Return data for evidence
	 *
	 * @param	int		$visitroId		ID of visitor
	 * @return	array	data from database
	 */
	public function evidence($visitorId = null)
	{
		$evidenceLimit = '';
		$specificVisitor = '';

		if(isset($visitorId) && $visitorId != NULL){
			$evidenceLimit = 'LIMIT 1';
			$specificVisitor = "vis.id='" . $visitorId . "' AND";
		}

		return $this->getDatabase()->query('SELECT	vis.id AS id,
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
			WHERE ' . $specificVisitor . ' meeting = ? AND vis.deleted = ?
			ORDER BY surname, name
			' . $evidenceLimit, $this->getMeetingId(), '0')->fetchAll();
	}

	public static function getPdfBlocks($vid, $database)
	{
		$programs = "<tr>";
		$programs .= " <td class='progPart'>";

		$data = self::$connection->query('SELECT 	id,
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

					if($progData['program'] == 1) $programs .= "<div>".ProgramModel::getPdfPrograms($progData['id'], $vid, self::$connection)."</div>";
				}
			}
		}

		$programs .= "</td>";
		$programs .= "</tr>";

		return $programs;
	}

	/**
	 * Return data for program cards
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function programCards()
	{
		return $this->getDatabase()->query('SELECT	vis.id AS id,
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
	}

	public function getLargeProgramData($day)
	{
		return $this->database->query('SELECT 	blocks.id AS id,
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
			ORDER BY `from` ASC', '0', $day, $this->meetingId)->fetchAll();
	}

	/**
	 * Return data for large program
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function largeProgram()
	{
		return $this->database->query('SELECT	id,
						place,
						DATE_FORMAT(start_date, "%Y") AS year,
						UNIX_TIMESTAMP(open_reg) AS open_reg,
						UNIX_TIMESTAMP(close_reg) as close_reg
				FROM kk_meetings
				WHERE id = ?
				ORDER BY id DESC
				LIMIT 1', $this->meetingId)->fetch();
	}

	/**
	 * Return data for public program
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function publicProgram()
	{
		return $this->database->query('SELECT	id,
						place,
						DATE_FORMAT(start_date, "%Y") AS year,
						UNIX_TIMESTAMP(open_reg) AS open_reg,
						UNIX_TIMESTAMP(close_reg) as close_reg
				FROM kk_meetings
				WHERE id = ?
				ORDER BY id DESC
				LIMIT 1', $this->meetingId)->fetch();
	}

	/**
	 * Return data for program badges
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function programBadges()
	{
		return $this->database->query('SELECT	*
			FROM kk_visitors AS vis
			WHERE meeting = ? AND vis.deleted = ?', $this->meetingId, '0')->fetchAll();
	}

	/**
	 * Return data for name badges
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function nameBadges()
	{
		return $this->database->query('SELECT	vis.id AS id,
						nick
				FROM kk_visitors AS vis
				WHERE meeting = ? AND vis.deleted = ?', $this->meetingId, '0')->fetchAll();
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
	 * Return data for visitors's program
	 *
	 * @param	int		program id
	 * @return	file	PDF file
	 */
	public function programVisitors($programId)
	{
		return $this->database
			->query('SELECT vis.name AS name,
							vis.surname AS surname,
							vis.nick AS nick,
							prog.name AS program
					FROM kk_visitors AS vis
					LEFT JOIN `kk_visitor-program` AS visprog ON vis.id = visprog.visitor
					LEFT JOIN `kk_programs` AS prog ON prog.id = visprog.program
					WHERE visprog.program = ? AND vis.deleted = ?', $programId, '0')->fetchAll();
	}

	/**
	 * Return data for details of program
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function programDetails()
	{
		return $this->database
			->query('SELECT prog.name AS name,
						 prog.description AS description,
						 prog.tutor AS tutor,
						 prog.email AS email
					FROM kk_programs AS prog
					LEFT JOIN `kk_blocks` AS block ON block.id = prog.block
					WHERE block.meeting = ?
						AND prog.deleted = ?
						AND block.deleted = ?', $this->meetingId, '0', '0')->fetchAll();
	}

	/**
	 * Return data for visitor excel
	 *
	 * @param   void
	 * @return	array	data
	 */
	public function visitorsExcel()
	{
		return $this->database
			->query('
		SELECT vis.id AS id,
			code,
			vis.name,
			surname,
			nick,
			DATE_FORMAT(birthday, "%d. %m. %Y") AS birthday,
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
		WHERE vis.deleted = ? AND meeting = ?', '0', $this->meetingId)->fetchAll();
	}

}
