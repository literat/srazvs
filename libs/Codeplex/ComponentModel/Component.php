<?php

/**
 * Component
 *
 * Component is a base class for all components.
 * Components are object implementing IModel.
 *
 * @created 2012-12-16
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
abstract class Component implements IComponent
{
	/** Table in Database */
	protected $dbTable;
	
	/** Constructor */
	public function __construct($dbTable = NULL)
	{
		$this->dbTable = $dbTable;
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
	public function create(array $db_data)
	{
		$query_key_set = "";
		$query_value_set = "";

		foreach($db_data as $key => $value) {
			$query_key_set .= "`".$key."`,";
			$query_value_set .= "'".$value."',";
		}
		$query_key_set = substr($query_key_set, 0, -1);
		$query_value_set = substr($query_value_set, 0, -1);	

    	echo $query = "INSERT INTO `".$this->dbTable."` 
     				 (".$query_key_set.") 
     				 VALUES (".$query_value_set.");";
    	$result = mysql_query($query);

		return $result;	
	}
	
	/**
	 * Modify record
	 *
	 * @param	int		$id			ID of record
	 * @param	array	$db_data	array of data
	 * @return	bool
	 */	
	public function update($id, array $db_data)
	{
		$query_set = "";
	 	foreach($db_data as $key => $value) {
			$query_set .= "`".$key."` = '".$value."',";	
		}
	 	$query_set = substr($query_set, 0, -1);	
		
    	$query = "UPDATE `".$this->dbTable."` 
					SET ".$query_set."
					WHERE `id`='".$id."' LIMIT 1";
    	$result = mysql_query($query);
		
		return $result;
	}
	
	/**
	 * Delete one or multiple record/s
	 *
	 * @param	int		ID/s of record
	 * @return	boolean 
	 */
	public function delete($id)
	{
    	$query = "UPDATE ".$this->dbTable." SET deleted = '1' WHERE id IN (".$id.")";
	    $result = mysql_query($query);
		
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