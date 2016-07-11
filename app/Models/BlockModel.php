<?php

namespace App;

/**
 * Blocks
 *
 * class for handling program blocks
 *
 * @created 2012-09-14
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class BlockModel extends BaseModel
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
	public function __construct($database)
	{
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

	public function setMeetingId($id)
	{
		$this->meetingId = $id;
	}

	/**
	 * Get data from database
	 *
	 * @return	string	html of a table
	 */
	public function getData($block_id = NULL)
	{
		if(isset($block_id)) {
			$data = $this->database
				->query('SELECT name,
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
			$data = $this->database
				->query('SELECT 	blocks.id AS id,
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

		return $data;
	}

	/**
	 * Render select box of blocks
	 *
	 * @param	int		selected option
	 * @return	string	html select box
	 */
	public static function renderHtmlSelect($blockId, $database)
	{
		$result = $database
			->table('kk_blocks')
			->where('meeting ? AND program ? AND deleted ?', $_SESSION['meetingID'], '1', '0')
			->fetchAll();

		$html_select = "<select style='width: 300px; font-size: 10px' name='block'>\n";

		foreach($result as $data){
			if($data['id'] == $blockId) $selected = "selected";
			else $selected = "";
			$html_select .= "<option ".$selected." value='".$data['id']."'>".$data['day'].", ".$data->from->format('%H:%I:%S')." - ".$data->to->format('%H:%I:%S')." : ".$data['name']."</option>\n";
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
	public function getProgramBlocks($meetingId)
	{
		$data = $this->database
			->query('SELECT id,
					day,
					DATE_FORMAT(`from`, "%H:%i") AS `from`,
					DATE_FORMAT(`to`, "%H:%i") AS `to`,
					name,
					program
				FROM kk_blocks
				WHERE deleted = ? AND program = ? AND meeting = ?
				ORDER BY `day` ASC',
				'0', '1', $meetingId)->fetchAll();

		return $data;
	}

	public static function getExportBlocks($meetingId, $dayVal, $database)
	{
		$result = $database
			->query('SELECT blocks.id AS id,
						day,
						DATE_FORMAT(`from`, "%H:%i") AS `from`,
						DATE_FORMAT(`to`, "%H:%i") AS `to`,
						blocks.name AS name,
						program,
						display_progs,
						style,
						cat.id AS category
				FROM kk_blocks AS blocks
				LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
				/* 18 - pauzy */
				WHERE blocks.deleted = ? AND day = ? AND meeting = ? AND category != ?
				ORDER BY `from` ASC',
				'0', $dayVal, $meetingId, '18')->fetchAll();

		return $result;
	}

	/**
	 * Get tutor e-mail address
	 *
	 * @param int $blockId id of block item
	 * @return Nette\Database\Table\ActiveRow object with e-mail address
	 */
	public function getTutor($blockId)
	{
		return $this->database
			->table($this->dbTable)
			->select('email, tutor')
			->where('id ? AND deleted ?', $blockId, '0')
			->limit(1)
			->fetch();
	}
}
