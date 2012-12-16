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
	 * Create a new record
	 *
	 * @return	boolean
	 */
	public function create($db_data)
	{
		$query_key_set = "";
		$query_value_set = "";

		foreach($db_data as $key => $value) {
			$query_key_set .= "`".$key."`,";
			$query_value_set .= "'".$value."',";
		}
		$query_key_set = substr($query_key_set, 0, -1);
		$query_value_set = substr($query_value_set, 0, -1);	

    	$query = "INSERT INTO `".$this->dbTable."` 
     				 (".$query_key_set.") 
     				 VALUES (".$query_value_set.");";
    	$result = mysql_query($query);

		return $result;	
	}
	
	/**
	 * Modify record
	 *
	 * @param	int		ID of record
	 * @return	bool
	 */	
	public function modify($id, $db_data)
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
	 * Delete record
	 *
	 * @param	int		ID of record
	 * @return	boolean 
	 */
	public function delete($id)
	{
		$query = "UPDATE ".$this->dbTable." SET deleted = '1' WHERE id = ".$id."	LIMIT 1";
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