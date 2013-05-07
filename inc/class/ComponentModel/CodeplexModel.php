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
    
    /**
     * Logging to file
     *
     * @param   string  $message    text
     * @param   string  $method     (I)nfo|(E)rror|(W)arning|
     * @return  bool
     */
    public function log($message,$method = 'I')
    {
        return error_log(date("Y-m-d H:i:s").' '.$method.' '.$message."\n", 3, $this->logfile);
    }
}