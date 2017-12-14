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
				ORDER BY `day`, `from` ASC',
				'0', '1', $meetingId)->fetchAll();

		return $data;
	}

	/**
	 * @param  integer $meetingId
	 * @return ActiveRow
	 */
	public function idsFromCurrentMeeting($meetingId)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('id')
			->where('meeting ? AND program ? AND deleted ?', $meetingId, '1', '0')
			->fetchAll();
	}

	/**
	 * @param  integer $meetingId
	 * @param  string  $dayVal
	 * @return ActiveRow
	 */
	public function getExportBlocks($meetingId, $dayVal)
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
			->select('id, day, from, to, name')
			->where('meeting ? AND program ? AND deleted ?', $meetingId, '1', '0')
			->fetchAll();
	}

	/**
	 * @param  string $day
	 * @return Row
	 */
	public function findByDay($day = '')
	{
		return $this->getDatabase()
				->query('SELECT	blocks.id AS id,
							day,
							DATE_FORMAT(`from`, "%H:%i") AS `from`,
							DATE_FORMAT(`to`, "%H:%i") AS `to`,
							blocks.name AS name,
							program,
							style
					FROM kk_blocks AS blocks
					LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
					WHERE blocks.deleted = ? AND day = ? AND blocks.meeting = ?
					ORDER BY `from` ASC',
					'0', $day, $this->getMeetingId())
				->fetchAll();
	}

	/**
	 * Return blocks that contents programs
	 *
	 * @param	int		meeting ID
	 * @return	array	result and number of affected rows
	 */
	public function findProgramBlocksByMeeting(int $meetingId)
	{
		return $this->getDatabase()
			->query('SELECT id,
					day,
					DATE_FORMAT(`from`, "%H:%i") AS `from`,
					DATE_FORMAT(`to`, "%H:%i") AS `to`,
					name,
					program
				FROM kk_blocks
				WHERE deleted = ? AND program = ? AND meeting = ?
				ORDER BY `day`, `from` ASC',
				'0', '1', $meetingId)->fetchAll();
	}

	public function findByProgramId(int $programId)
	{
		return $this->getDatabase()
			->query()
			->fetch();
	}

}
