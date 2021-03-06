<?php

namespace App\Models;

use Nette\Database\Context;
use App\Models\ProgramModel;
use App\Models\BaseModel;

/**
 * Meeting
 *
 * class for handling meeting
 *
 * @created 2012-11-09
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class MeetingModel extends BaseModel
{

	/**
	 * @var array
	 */
	private $weekendDays = [];

	/**
	 * @var array
	 */
	public $form_names = [];

	/**
	 * @var array
	 */
	public $dbColumns = [];

	/**
	 * @var DateTime
	 */
	public $regOpening = NULL;

	/**
	 * @var DateTime
	 */
	public $regClosing = NULL;

	/** @var string registration heading text */
	public $regHeading = '';

	public $eventId;
	public $courseId;

	private $configuration;

	private $program;
	private $httpEncoding;
	private $dbTable;

	protected $table = 'kk_meetings';

	/** Constructor */
	public function __construct(Context $database, ProgramModel $program)
	{
		$this->weekendDays = array("pátek", "sobota", "neděle");
		$this->form_names = array(
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
		);
		$this->dbColumns = array(
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
		);
		$this->dbTable = "kk_meetings";
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

	/**
	 * Create new or return existing instance of class
	 *
	 * @return	mixed	instance of class
	 */
	public static function getInstance()
	{
		if(self::$instance === false) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return Nette\Database\Table\IRow
	 */
	public function all()
	{
		return $this->getDatabase()
				->table($this->getTable())
				->where('deleted', '0')
				->fetchAll();
	}

	/**
	 * @param  int $id
	 * @return Nette\Database\Table\IRow
	 */
	public function find($id)
	{
		return $this->getDatabase()
				->table($this->getTable())
				->where('deleted ? AND id ?', '0', $id)
				->fetch();
	}

	/**
	 * Create a new record
	 *
	 * @param	mixed	array of data
	 * @return	boolean
	 */
	public function create(array $data)
	{
		$data['guid'] = md5(uniqid());
		$result = $this->getDatabase()->query('INSERT INTO ' . $this->getTable(), $data);

		return $result;
	}

	/**
	 * Modify record
	 *
	 * @param	int		$id			ID of record
	 * @param	array	$db_data	array of data
	 * @return	bool
	 */
	public function update($id, array $data)
	{
		$result = $this->getDatabase()->table($this->getTable())->where('id', $id)->update($data);

		return $result;
	}

	/**
	 * Delete one or multiple record/s
	 *
	 * @param	int		ID/s of record
	 * @return	boolean
	 */
	public function delete($ids)
	{
		$data = array('deleted' => '1');
		$result = $this->getDatabase()->table($this->getTable())->where('id', $ids)->update($data);

		return $result;
	}

	/**
	 * Return meeting data
	 *
	 * @return  Nette\Database\Table\IRow
	 */
	public function getData($meetingId = null)
	{
		if(isset($meetingId)) {
			$data = $this->find($meetingId);
		} else {
			$data = $this->all();
		}

		if(!$data) {
			return 0;
		} else {
			return $data;
		}
	}

	/**
	 * @param  string $priceType cost|advance
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

	/**
	 * @param  integer $meetingId
	 * @return $this
	 */
	public function setRegistrationHandlers($meetingId = 1)
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

	/**
	 * @return string
	 */
	public function getRegOpening()
	{
		return $this->regOpening;
	}

	/**
	 * @param  string $value
	 * @return $this
	 */
	public function setRegOpening($value = '')
	{
		$this->regOpening = $value;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRegClosing()
	{
		return $this->regClosing;
	}

	/**
	 * @param  string $value
	 * @return $this
	 */
	public function setRegClosing($value = '')
	{
		$this->regClosing = $value;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRegHeading()
	{
		return $this->regHeading;
	}

	/**
	 * @param  string $value
	 * @return $this
	 */
	public function setRegHeading($value = '')
	{
		$this->regHeading = $value;

		return $this;
	}

	/**
	 * Is registration open?
	 *
	 * @return 	boolean
	 */
	public function isRegOpen($debug = false)
	{
		return (($this->getRegOpening() < time()) && (time() < $this->getRegClosing()) || $debug);
	}

	/**
	 * @param  integer $id
	 * @return string
	 */
	public function getProvinceNameById($id)
	{
		return $this->getDatabase()
			->table('kk_provinces')
			->select('province_name')
			->where('id', $id)
			->limit(1)
			->fetchField('province_name');
	}

	/**
	 * @return Row
	 */
	public function findEventId()
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $this->getMeetingId())
			->limit(1)
			->fetchField('skautis_event_id');
	}

	/**
	 * @return Row
	 */
	public function findCourseId()
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
