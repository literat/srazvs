<?php

namespace App\Models;

use Nette\Database\Context;
use Nette\Object;

/**
 * BaseModel
 *
 * Base model is a base class for all components.
 *
 * @created 2012-12-16
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
abstract class BaseModel extends Object
{
	/** @var string */
	protected $table = null;

	/** @var array */
	protected $columns = [];

	/** @var Nette\Database\Context */
	protected $database;

	/** @var integer */
	protected $meetingId;

	/** Constructor */
	public function __construct($dbTable = NULL, $database = NULL)
	{
		$this->dbTable = $dbTable;
		$this->database = $database;
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
	 * Create a new record
	 *
	 * @param	mixed	array of data
	 * @return	boolean
	 */
	public function create(array $data)
	{
		$data['guid'] = md5(uniqid());
		$result = $this->database->query('INSERT INTO ' . $this->dbTable, $data);

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
		$result = $this->database->table($this->dbTable)->where('id', $id)->update($data);

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
		$result = $this->database->table($this->getTable())->where('id', $ids)->update($data);

		return $result;
	}

	/**
	 * Render data in a table
	 *
	 * @return	string	html of a table
	 */
	public function renderData()
	{

	}

	/**
	 * @param  integer $meetingId
	 * @return $this
	 */
	public function setMeetingId($meetingId)
	{
		$this->meetingId = $meetingId;
		return $this;
	}

	/**
	 * @return integer
	 */
	protected function getMeetingId()
	{
		return $this->meetingId;
	}

	/**
	 * @return Nette\Database\Context
	 */
	protected function getDatabase()
	{
		return $this->database;
	}

	/**
	 * @param  Nette\Database\Context $database
	 * @return $this
	 */
	protected function setDatabase(Context $database)
	{
		$this->database = $database;
		return $this;
	}

	/**
	 * @return string
	 */
	protected function getTable()
	{
		return $this->table;
	}

	/**
	 * @param  string $table
	 * @return $this
	 */
	protected function setTable($table)
	{
		$this->table = $table;
		return $this;
	}

	/**
	 * @return string
	 */
	protected function generateGuid()
	{
		return md5(uniqid());
	}

}
