<?php

namespace App\Models;

use Nette\Database\Context;

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

	/**
	 * @var integer
	 */
	private $graphHeight;

	/**
	 * @var Context
	 */
	private static $connection;

	/**
	 * @param Context  $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
		self::$connection = $this->getDatabase();
	}

	/**
	 * @param  integer $height
	 * @return $this
	 */
	public function setGraphHeight($height)
	{
		$this->graphHeight = $height;

		return $this;
	}

	/**
	 * @return integer
	 */
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
				DATE_FORMAT(end_date, "%d. %m. %Y") AS end_date
		FROM kk_visitors AS vis
		LEFT JOIN kk_meals AS meals ON meals.visitor = vis.id
		LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
		LEFT JOIN kk_meetings AS meets ON meets.id = vis.meeting
		WHERE meeting = ? AND vis.deleted = ?
		', $this->getMeetingId(), '0')->fetchAll();
	}

	/**
	 * Print Attendance into PDF file
	 *
	 * @param	void
	 * @return	file	PDF file
	 */
	public function attendance()
	{
		return $this->getDatabase()->query('SELECT	vis.id AS id,
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
				$this->getMeetingId(), '0')->fetchAll();
	}

	/**
	 * Return data for name list
	 *
	 * @param	void
	 * @return	array    data
	 */
	public function nameList()
	{
		return $this->getDatabase()->query('SELECT	vis.id AS id,
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
				', $this->getMeetingId(), '0')->fetchAll();
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

		if(isset($visitorId) && $visitorId != NULL) {
			$evidenceLimit = 'LIMIT 1';
			$specificVisitor = "vis.id IN (" . $visitorId . ") AND";
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
		return $this->getDatabase()->query('SELECT 	blocks.id AS id,
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
			ORDER BY `from` ASC', '0', $day, $this->getMeetingId())->fetchAll();
	}

	/**
	 * Return data for large program
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function largeProgram()
	{
		return $this->getDatabase()
			->table('kk_meetings')
			->select('id, place')
			->select('DATE_FORMAT(start_date, "%Y") AS year')
			->select('UNIX_TIMESTAMP(open_reg) AS open_reg')
			->select('UNIX_TIMESTAMP(close_reg) as close_reg')
			->where('id = ?', $this->getMeetingId())
			->order('id DESC')
			->limit(1)
			->fetchField();
	}

	/**
	 * Return data for public program
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function publicProgram()
	{
		return $this->getDatabase()
			->table('kk_meetings')
			->select('id, place')
			->select('DATE_FORMAT(start_date, "%Y") AS year')
			->select('UNIX_TIMESTAMP(open_reg) AS open_reg')
			->select('UNIX_TIMESTAMP(close_reg) as close_reg')
			->where('id = ?', $this->getMeetingId())
			->order('id DESC')
			->limit(1)
			->fetchField();
	}

	/**
	 * Return data for program badges
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function programBadges()
	{
		return $this->getDatabase()
			->table('kk_visitors')
			->where('meeting', $this->getMeetingId())
			->where('deleted', '0')
			->fetchAll();
	}

	/**
	 * Return data for name badges
	 *
	 * @param	void
	 * @return	array	data
	 */
	public function nameBadges()
	{
		return $this->getDatabase()
			->table('kk_visitors')
			->select('id')
			->select('nick')
			->where('meeting', $this->getMeetingId())
			->where('deleted', '0')
			->fetchAll();
	}

	/**
	 * @return ActiveRow
	 */
	public function graph()
	{
		return $this->getDatabase()
			->query('SELECT DATE_FORMAT(reg_daytime, "%d. %m. %Y") AS day,
							   COUNT(reg_daytime) AS reg_count
						FROM `kk_visitors` AS vis
						LEFT JOIN kk_meetings AS meet ON meet.id = vis.meeting
						WHERE meet.id = ? AND vis.deleted = ?
						GROUP BY day
						ORDER BY reg_daytime ASC',
						$this->getMeetingId(), '0')->fetchAll();
	}

	/**
	 * @return integer
	 */
	public function graphMax()
	{
		return $this->getDatabase()
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
					  $this->getMeetingId(), '0')->fetchField();
	}

	/**
	 * @return ActiveRow
	 */
	public function materials()
	{
		return $this->getDatabase()
			->query('SELECT	progs.id AS id,
						progs.name AS name,
						progs.material AS material
				FROM `kk_programs` AS progs
				LEFT JOIN `kk_blocks` AS bls ON progs.block = bls.id
				WHERE progs.deleted = ?
					AND bls.meeting = ?
					AND bls.deleted = ?',
					'0', $this->getMeetingId(), '0')->fetchAll();
	}

	/**
	 * Get materials for each program
	 *
	 * @param	string	type of money (account|balance|suma)
	 * @return	mixed	amount of money or false if error
	 */
	public function getMoney($type)
	{
		$data = $this->getDatabase()
			->query('SELECT SUM(bill) AS account,
							COUNT(bill) * vis.cost AS suma,
							COUNT(bill) * vis.cost - SUM(bill) AS balance
					FROM kk_visitors AS vis
					LEFT JOIN kk_meetings AS meets ON vis.meeting = meets.id
					WHERE meeting = ? AND vis.deleted = ?',
					$this->getMeetingId(), '0')->fetch();

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
		$data = $this->getDatabase()
			->query('SELECT count(?) AS ?
				FROM `kk_meals` AS mls
				LEFT JOIN `kk_visitors` AS vis ON vis.id = mls.visitor
				WHERE vis.deleted = ?
					AND vis.meeting = ?
					AND ' . $meal . ' = ?',
					$meal, $meal, '0', $this->getMeetingId(), 'ano')->fetch();

		return $data[$meal];
	}

	/**
	 * Return data for visitors's program
	 *
	 * @param	int		program id
	 * @return	file	PDF file
	 */
	public function programVisitors($programId)
	{
		return $this->getDatabase()
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
		return $this->getDatabase()
			->query('SELECT prog.name AS name,
						 prog.description AS description,
						 prog.tutor AS tutor,
						 prog.email AS email
					FROM kk_programs AS prog
					LEFT JOIN `kk_blocks` AS block ON block.id = prog.block
					WHERE block.meeting = ?
						AND prog.deleted = ?
						AND block.deleted = ?', $this->getMeetingId(), '0', '0')->fetchAll();
	}

	/**
	 * Return data for visitor excel
	 *
	 * @param   void
	 * @return	array	data
	 */
	public function visitorsExcel()
	{
		return $this->getDatabase()
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
		WHERE vis.deleted = ? AND meeting = ?', '0', $this->getMeetingId())->fetchAll();
	}

}
