<?php

namespace App\Models;

use App\Entities\BlockEntity;
use App\Entities\IEntity;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Reflection\ClassType;
use Nette\Utils\DateTime;

class BlockModel extends BaseModel implements IModel
{

	/**
	 * @var string
	 */
	protected $table = 'kk_blocks';

	/**
	 * @var array
	 */
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
	 * @param Context $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	public function all(): ActiveRow
	{
		return $this->getDatabase()
			->query(
				'SELECT blocks.guid AS guid,
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
				$this->getMeetingId(),
				'0'
			)->fetchAll();
	}

	/**
	 * @param  int         $id
	 * @return BlockEntity
	 */
	public function find($id): IEntity
	{
		$block = parent::find($id);

		return $this->hydrate($block);
	}

	/**
	 * @param  IEntity    $entity
	 * @return bool|mixed
	 * @throws \Exception
	 */
	public function save(IEntity $entity)
	{
		$this->guardToGreaterThanFrom($entity->from, $entity->to);

		if ($entity->getId() === null) {
			$values = $entity->toArray();

			$id = $this->create($values);
			$result = $this->setIdentity($entity, $id);
		} else {
			$values = $entity->toArray();
			$result = $this->update($entity->getId(), $values);
		}

		return $result;
	}

	/**
	 * Return blocks that contents programs.
	 *
	 * @param  int   $meetingId meeting ID
	 * @return array result and number of affected rows
	 */
	public function getProgramBlocks($meetingId)
	{
		$data = $this->getDatabase()
			->query(
				'SELECT id,
					day,
					DATE_FORMAT(`from`, "%H:%i") AS `from`,
					DATE_FORMAT(`to`, "%H:%i") AS `to`,
					name,
					program
				FROM kk_blocks
				WHERE deleted = ? AND program = ? AND meeting = ?
				ORDER BY `day`, `from` ASC',
				'0',
				'1',
				$meetingId
			)->fetchAll();

		return $data;
	}

	/**
	 * @param  integer   $meetingId
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
	 * @param  integer   $meetingId
	 * @param  string    $dayVal
	 * @return ActiveRow
	 */
	public function getExportBlocks($meetingId, $dayVal)
	{
		$result = $this->getDatabase()
			->query(
				'SELECT blocks.id AS id,
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
				'0',
				$dayVal,
				$meetingId,
				'18'
			)->fetchAll();

		return $result;
	}

	/**
	 * Get tutor e-mail address.
	 */
	public function getTutor(int $blockId): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('guid, email, tutor')
			->where('id ? AND deleted ?', $blockId, '0')
			->limit(1)
			->fetch();
	}

	public function findByMeeting(int $meetingId): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('id, day, from, to, name')
			->where('meeting ? AND program ? AND deleted ?', $meetingId, '1', '0')
			->fetchAll();
	}

	public function findByDay(string $day = ''): ActiveRow
	{
		return $this->getDatabase()
				->query(
					'SELECT	blocks.id AS id,
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
					'0',
					$day,
					$this->getMeetingId()
				)->fetchAll();
	}

	/**
	 * Return blocks that contents programs.
	 *
	 * @param  int   $meetingId meeting ID
	 * @return array result and number of affected rows
	 */
	public function findProgramBlocksByMeeting(int $meetingId)
	{
		return $this->getDatabase()
			->query(
				'SELECT id,
					day,
					DATE_FORMAT(`from`, "%H:%i") AS `from`,
					DATE_FORMAT(`to`, "%H:%i") AS `to`,
					name,
					program
				FROM kk_blocks
				WHERE deleted = ? AND program = ? AND meeting = ?
				ORDER BY `day`, `from` ASC',
				'0',
				'1',
				$meetingId
			)->fetchAll();
	}

	/*
	public function findByProgramId(int $programId)
	{
		return $this->getDatabase()
			->query()
			->fetch();
	}
	*/

	/**
	 * @param  $values
	 * @return BlockEntity
	 */
	public function hydrate($values): BlockEntity
	{
		$entity = new BlockEntity();
		//$this->setIdentity($entity, $values->id);

		// unset($values['id']);
		foreach ($values as $property => $value) {
			$entity->$property = $value;
		}

		return $entity;
	}

	/**
	 * @param  $block
	 * @return mixed
	 */
	public function transform($block)
	{
		$block->from = date('H:i:s', mktime($block->start_hour, $block->start_minute, 0, 0, 0, 0));
		$block->to = date('H:i:s', mktime($block->end_hour, $block->end_minute, 0, 0, 0, 0));
		$block->meeting = $this->getMeetingId();
		$block->program = strval($block->program) ?: '0';
		$block->display_progs = strval($block->display_progs) ?: '0';

		unset($block->start_hour);
		unset($block->end_hour);
		unset($block->start_minute);
		unset($block->end_minute);
		unset($block->backlink);

		return $block;
	}

	/**
	 * @param  $item
	 * @param  $id
	 * @return mixed
	 */
	private function setIdentity($item, $id)
	{
		$ref = new ClassType($item);
		$idProperty = $ref->getProperty('id');
		$idProperty->setAccessible(true);
		$idProperty->setValue($item, $id);

		return $item;
	}

	/**
	 * @throws \Exception
	 */
	private function guardToGreaterThanFrom(DateTime $from, DateTime $to)
	{
		if ($from > $to) {
			throw new \Exception('Starting time is greater then finishing time.');
		}
	}
}
