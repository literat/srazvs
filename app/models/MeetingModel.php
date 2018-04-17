<?php

namespace App\Models;

use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

class MeetingModel extends BaseModel
{
	/**
	 * @var array
	 */
	public $formNames = [];

	/**
	 * @var array
	 */
	public $dbColumns = [];

	/**
	 * @var \DateTime
	 */
	public $regOpening = null;

	/**
	 * @var \DateTime
	 */
	public $regClosing = null;

	/**
	 * @var string
	 */
	public $regHeading = '';

	/**
	 * @var int
	 */
	public $eventId;

	/**
	 * @var int
	 */
	public $courseId;

	/**
	 * @var ProgramModel
	 */
	protected $program;

	/**
	 * @var string
	 */
	protected $httpEncoding;

	/**
	 * @var string
	 */
	protected $table = 'kk_meetings';

	/**
	 * @var array
	 */
	protected $weekendDays = [];

	public function __construct(Context $database, ProgramModel $program)
	{
		$this->weekendDays = ["pátek", "sobota", "neděle"];
		$this->formNames = [
			"place",
			"start_date",
			"end_date",
			"open_reg",
			"close_reg",
			"contact",
			"email",
			"gsm",
			"cost",
			"advance",
			"numbering",
			'skautis_event_id',
			'skautis_course_id',
		];
		$this->dbColumns = [
			"place",
			"start_date",
			"end_date",
			"open_reg",
			"close_reg",
			"contact",
			"email",
			"gsm",
			"cost",
			"advance",
			"numbering",
			'skautis_event_id',
			'skautis_course_id',
		];
		$this->setDatabase($database);
		$this->program = $program;
	}

	/**
	 * @param string $encoding
	 */
	public function setHttpEncoding($encoding)
	{
		$this->httpEncoding = $encoding;
	}

	public function all(): ActiveRow
	{
		return $this->getDatabase()
				->table($this->getTable())
				->where('deleted', '0')
				->fetchAll();
	}

	public function find($id): ActiveRow
	{
		return $this->getDatabase()
				->table($this->getTable())
				->where('deleted ? AND id ?', '0', $id)
				->fetch();
	}

	/**
	 * Create a new record.
	 *
	 * @param  array   $data
	 * @return boolean
	 */
	public function create(array $data)
	{
		$data['guid'] = md5(uniqid());
		$result = $this->getDatabase()->query('INSERT INTO ' . $this->getTable(), $data);

		return $result;
	}

	/**
	 * Modify record.
	 *
	 * @param  int   $id   ID of record
	 * @param  array $data array of data
	 * @return bool
	 */
	public function update($id, array $data)
	{
		$result = $this->getDatabase()->table($this->getTable())->where('id', $id)->update($data);

		return $result;
	}

	/**
	 * Delete one or multiple record/s.
	 *
	 * @param  int     $ids ID/s of record
	 * @return boolean
	 */
	public function delete($ids)
	{
		$data = ['deleted' => '1'];
		$result = $this->getDatabase()->table($this->getTable())->where('id', $ids)->update($data);

		return $result;
	}

	/**
	 * Return meeting data.
	 */
	public function getData($meetingId = null): ActiveRow
	{
		if (isset($meetingId)) {
			$data = $this->find($meetingId);
		} else {
			$data = $this->all();
		}

		if (!$data) {
			return 0;
		} else {
			return $data;
		}
	}

	/**
	 * @param  string  $priceType cost|advance
	 * @return integer
	 */
	public function getPrice($priceType)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select($priceType)
			->where('id', $this->getMeetingId())
			->limit(1)
			->fetchField();
	}

	public function setRegistrationHandlers(int $meetingId = 1): self
	{
		$meeting = $this->getDatabase()
			->table($this->getTable())
			->select('id')
			->select('place')
			->select('DATE_FORMAT(start_date, "%Y") AS year')
			->select('UNIX_TIMESTAMP(open_reg) AS open_reg')
			->select('UNIX_TIMESTAMP(close_reg) AS close_reg')
			->where('id', $meetingId)
			->order('id DESC')
			->limit(1)
			->fetch();

		$this->setRegHeading($meeting->place . ' ' . $meeting->year);
		$this->setRegClosing($meeting->close_reg);
		$this->setRegOpening($meeting->open_reg);

		return $this;
	}

	public function getRegOpening(): string
	{
		return $this->regOpening;
	}

	public function setRegOpening(string $value = ''): self
	{
		$this->regOpening = $value;

		return $this;
	}

	public function getRegClosing(): string
	{
		return $this->regClosing;
	}

	public function setRegClosing(string $value = ''): self
	{
		$this->regClosing = $value;

		return $this;
	}

	public function getRegHeading(): string
	{
		return $this->regHeading;
	}

	public function setRegHeading(string $value = ''): self
	{
		$this->regHeading = $value;

		return $this;
	}

	public function isRegOpen($debug = false): bool
	{
		return ($this->getRegOpening() < time()) && (time() < $this->getRegClosing()) || $debug;
	}

	public function getProvinceNameById(int $id): string
	{
		return $this->getDatabase()
			->table('kk_provinces')
			->select('province_name')
			->where('id', $id)
			->limit(1)
			->fetchField('province_name');
	}

	public function findEventId(): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $this->getMeetingId())
			->limit(1)
			->fetchField('skautis_event_id');
	}

	public function findCourseId(): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $this->getMeetingId())
			->limit(1)
			->fetchField('skautis_course_id');
	}

	/**
	 * @param  integer|string $meetingId
	 * @return ActiveRow
	 */
	public function getPlaceAndYear($meetingId)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('place')
			->select('DATE_FORMAT(start_date, "%Y") AS year')
			->where('id = ? AND deleted = ?', $meetingId, '0')
			->limit(1)
			->fetch();
	}

	/**
	 * @return ActiveRow
	 */
	public function getMenuItems()
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('id AS mid')
			->select('place')
			->select('DATE_FORMAT(start_date, "%Y") AS year')
			->where('deleted', '0')
			->order('id DESC')
			->fetchAll();
	}

	/**
	 * @return integer
	 */
	public function getLastMeetingId()
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('id')
			->order('id DESC')
			->limit(1)
			->fetchField();
	}
}
