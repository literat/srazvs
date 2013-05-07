<?php

/**
 * IModel
 *
 * interface for base/default application Model (MVC)
 *
 * @created 2012-12-16
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
interface IModel
{
	/**
	 * Create new or return existing instance of class
	 *
	 * @return	mixed	instance of class
	 */
	public static function getInstance();
}