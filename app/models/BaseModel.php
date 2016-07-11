<?php

namespace App;

/**
 * BaseModel
 *
 * Base model is a base class for all components.
 *
 * @created 2012-12-16
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
abstract class BaseModel
{
	/** Table in Database */
	protected $dbTable;

	/** Database connection */
	protected $database;

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
		$result = $this->database->table($this->dbTable)->where('id', $ids)->update($data);

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
}
