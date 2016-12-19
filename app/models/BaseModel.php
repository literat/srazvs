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
	public function __construct($table = null, $database = null)
	{
		$this->setTable($table);
		$this->setDatabase($database);
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
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function all()
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('deleted', '0')
			->fetchAll();
	}

	/**
	 * @param  integer $id
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function find($id)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id ? AND deleted ?', $id, '0')
			->limit(1)
			->fetch();
	}

	/**
	 * @param  string $column
	 * @param  mixed  $value
	 * @return ActiveRow
	 */
	public function findBy($column, $value)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where($column . ' ? AND deleted ?', $value, '0')
			->limit(1)
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
		$data['guid'] = $this->generateGuid();
		//$result = $this->getDatabase()->query('INSERT INTO ' . $this->getTable(), $data);
		$result = $this->getDatabase()->table($this->getTable())->insert($data);

		return $result;
	}

	/**
	 * @param	int	   $id
	 * @param	array  $data
	 * @return	bool
	 */
	public function update($id, array $data)
	{
		$result = $this->getDatabase()
			->table($this->getTable())
			->where('id', $id)
			->update($data);

		return $result;
	}

	/**
	 * @param	string         $column
	 * @param   string|integer $value
	 * @param	array          $data
	 * @return	bool
	 */
	public function updateBy($column, $value, array $data)
	{
		$result = $this->getDatabase()
			->table($this->getTable())
			->where($column, $value)
			->update($data);

		return $result;
	}

	/**
	 * @param	int		ID/s of record
	 * @return	boolean
	 */
	public function delete($ids)
	{
		$data = [
			'deleted' => '1',
		];
		$result = $this->getDatabase()
			->table($this->getTable())
			->where('id', $ids)
			->update($data);

		return $result;
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
