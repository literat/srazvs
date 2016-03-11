<?php
/**
 * Blocks
 *
 * class for handling program blocks
 *
 * @created 2012-09-14
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class BlockModel extends Component
{
	/** @var integer meeting ID */
	private $meetingId;

	/**
	 * Array of database block table columns
	 * @var array
	 */
	public $dbColumns = array();

	/**
	 * Array of form names
	 * @var array
	 */
	public $formNames = array();

	/**
	 * Init variables
	 *
	 * @param int $meeting_ID ID of meeting
	 */
	public function __construct($meeting_ID, $database)
	{
		$this->meetingId = $meeting_ID;
		$this->dbColumns = array(
			"name",
			"day",
			"from",
			"to",
			"program",
			"display_progs",
			"description",
			"tutor",
			"email",
			"category",
			"material",
			"capacity"/*,
			"meeting"*/
		);
		$this->formNames = array(
			"name",
			"day",
			"start_hour",
			"end_hour",
			"start_minute",
			"end_minute",
			"program",
			"display_progs",
			"description",
			"tutor",
			"email",
			"category",
			"material",
			"capacity",
			"day",
			"from",
			"to"
		);
		$this->dbTable = "kk_blocks";

		$this->database = $database;
	}

	/**
	 * Get data from database
	 *
	 * @return	string	html of a table
	 */
	public function getData($block_id = NULL)
	{
		if(isset($block_id)) {
			$data = $this->database->query('SELECT 	name,
								DATE_FORMAT(`from`,"%H") AS start_hour,
								DATE_FORMAT(`to`,"%H") AS end_hour,
								DATE_FORMAT(`from`,"%i") AS start_minute,
								DATE_FORMAT(`to`,"%i") AS end_minute,
								`day`,
								`from`,
								`to`,
								program,
								display_progs,
								description,
								material,
								tutor,
								email,
								capacity,
								category
						FROM kk_blocks
						WHERE id = ? AND deleted = ?
						LIMIT 1',
						$block_id, '0')->fetch();
		} else {
			$data = $this->database->query('SELECT 	blocks.id AS id,
							blocks.name AS name,
							cat.name AS cat_name,
							day,
							DATE_FORMAT(`from`, "%H:%i") AS `from`,
							DATE_FORMAT(`to`, "%H:%i") AS `to`,
							description,
							tutor,
							email,
							style
					FROM kk_blocks AS blocks
					LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
					WHERE blocks.meeting = ? AND blocks.deleted = ?
					ORDER BY day, `from` ASC',
					$this->meetingId, '0')->fetchAll();
		}

		if(!$data) {
			return 0;
		} else {
			return $data;
		}
	}

	/**
	 * Render select box of blocks
	 *
	 * @param	int		selected option
	 * @return	string	html select box
	 */
	public static function renderHtmlSelect($block_id)
	{
		$query = "SELECT * FROM kk_blocks WHERE meeting='".$_SESSION['meetingID']."' AND program='1' AND deleted='0'";
		$result = mysql_query($query);

		$html_select = "<select style='width: 300px; font-size: 10px' name='block'>\n";

		while($data = mysql_fetch_assoc($result)){
			if($data['id'] == $block_id) $selected = "selected";
			else $selected = "";
			$html_select .= "<option ".$selected." value='".$data['id']."'>".$data['day'].", ".$data['from']." - ".$data['to']." : ".$data['name']."</option>\n";
		}
		$html_select .= "</select>\n";

		return $html_select;
	}

	/**
	 * Return blocks that contents programs
	 *
	 * @param	int		meeting ID
	 * @return	array	result and number of affected rows
	 */
	public function getProgramBlocks($meeting_id)
	{
		$query = "SELECT 	id,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`,
					name,
					program
				FROM kk_blocks
				WHERE deleted = '0' AND program='1' AND meeting='".$meeting_id."'
				ORDER BY `day` ASC";

		$result = mysql_query($query);
		$rows = mysql_affected_rows();

		return array('result' => $result, 'rows' => $rows);
	}

	public static function getExportBlocks($meeting_id, $day_val)
	{
		$query = "SELECT blocks.id AS id,
						day,
						DATE_FORMAT(`from`, '%H:%i') AS `from`,
						DATE_FORMAT(`to`, '%H:%i') AS `to`,
						blocks.name AS name,
						program,
						display_progs,
						style,
						cat.id AS category
				FROM kk_blocks AS blocks
				LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
				/* 18 - pauzy */
				WHERE blocks.deleted = '0' AND day='".$day_val."' AND meeting='".$meeting_id."' AND category != '18'
				ORDER BY `from` ASC";

		$result = mysql_query($query);

		return $result;
	}
}
