<?php

namespace App\Models;

use Nette\Caching\Cache;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;

abstract class BaseModel
{
	use SmartObject;

	/**
	 * @var BaseModel
	 */
	protected static $instance;

	/**
	 * @var string
	 */
	protected $table = null;

	/**
	 * @var array
	 */
	protected $columns = [];

	/**
	 * @var Context
	 */
	protected $database;

	/**
	 * @var integer
	 */
	protected $meetingId;

	/**
	 * @var Cache
	 */
	protected $cache;

	public function __construct($table = null, $database = null)
	{
		$this->setTable($table);
		$this->setDatabase($database);
	}

	/**
	 * Create new or return existing instance of class.
	 *
	 * @return mixed instance of class
	 */
	/*
	public static function getInstance()
	{
		if(self::$instance === false) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	*/

	public function all(): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('deleted', '0')
			->fetchAll();
	}

	public function find($id)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id ? AND deleted ?', $id, '0')
			->limit(1)
			->fetch();
	}

	public function findBy(string $column, $value)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where($column . ' ? AND deleted ?', $value, '0')
			->limit(1)
			->fetch();
	}

	/**
	 * Create a new record.
	 *
	 * @param  mixed   array of data
	 * @return boolean
	 */
	public function create(array $data)
	{
		$data['guid'] = $this->generateGuid();
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');

		$result = $this->getDatabase()->table($this->getTable())->insert($data);

		return $result;
	}

	/**
	 * @param  int   $id
	 * @param  array $data
	 * @return bool
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
	 * @param  string         $column
	 * @param  string|integer $value
	 * @param  array          $data
	 * @return bool
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
	 * @param  int     $ids ID/s of record
	 * @return boolean
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
	 * @return self
	 */
	public function setMeetingId($meetingId): self
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

	protected function getDatabase(): Context
	{
		return $this->database;
	}

	/**
	 * @param  \Nette\Database\Context $database
	 * @return self
	 */
	protected function setDatabase(Context $database): self
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
	 * @return self
	 */
	protected function setTable($table): self
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

	/**
	 * @return Cache
	 */
	protected function getCache()
	{
		return $this->cache;
	}

	/**
	 * @param  Cache $cache
	 * @return self
	 */
	protected function setCache(Cache $cache): self
	{
		$this->cache = $cache;

		return $this;
	}
}
