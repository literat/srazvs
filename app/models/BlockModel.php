<?php

namespace App\Models;

use Nette\Database\Context;

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

	/** @var string */
	protected $table = 'kk_blocks';

	/** @var array */
	public $columns = [
		'guid',
		'name',
		'day',
		'from',
		'to',
		'program',
		'display_progs',
		'description',
		'tutor',
		'email',
		'category',
		'material',
		'capacity',
		//"meeting",
	];

	/**
	 * @param Context  $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	/**
	 * @return	ActiveRow
	 */
	public function all()
	{
		return $this->getDatabase()
			->query('SELECT blocks.guid AS guid,
						blocks.id AS id,
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
				$this->getMeetingId(), '0')
			->fetchAll();
	}

	/**
	 * Render select box of blocks
	 *
	 * @param	int		selected option
	 * @return	string	html select box
	 */
	public function renderHtmlSelect($blockId)
	{
		$result = $this->database
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
		$data = $this->getDatabase()
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

	public function idsFromCurrentMeeting($meetingId)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('id')
			->where('meeting ? AND program ? AND deleted ?', $meetingId, '1', '0')
			->fetchAll();
	}

	public static function getExportBlocks($meetingId, $dayVal, $database)
	{
		$result = $this->getDatabase()
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
		return $this->getDatabase()
			->table($this->getTable())
			->select('guid, email, tutor')
			->where('id ? AND deleted ?', $blockId, '0')
			->limit(1)
			->fetch();
	}

	/**
	 * @param  int $meetingId
	 * @return Database
	 */
	public function findByMeeting($meetingId)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('id')
			->where('meeting ? AND program ? AND deleted ?', $meetingId, '1', '0')
			->fetchAll();
	}

}
