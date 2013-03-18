<?php

/**
 * CodeplexModel
 *
 * Default Model for base/default application Model (MVC)
 *
 * @created 2013-03-11
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
abstract class CodeplexModel implements IModel
{
	/** Constructor */
	public function __construct()
	{
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
}