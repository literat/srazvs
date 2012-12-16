<?php

/**
 * IComponent
 *
 * interface for default application component (MVC)
 *
 * @created 2012-12-16
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
interface IComponent extends IModel
{
	/**
	 * Create a new record
	 *
	 * @return	boolean
	 */
	public function create($dbData);

	/**
	 * Modify record
	 *
	 * @param	int		ID of record
	 * @return	bool
	 */	
	public function modify($id, $dbData);
	
	/**
	 * Delete record
	 *
	 * @param	int		ID of record
	 * @return	boolean 
	 */
	public function delete($id);
	
	/**
	 * Render data in a table
	 *
	 * @return	string	html of a table
	 */
	public function renderData();
}