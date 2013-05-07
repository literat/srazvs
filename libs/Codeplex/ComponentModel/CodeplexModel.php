<?php

/**
 * CodeplexModel
 *
 * Default Model for base/default application Model (MVC)
 *
 * @author      Tomas Litera    <tomaslitera@hotmail.com>
 * @copyright   2013-03-11      Tomas Litera
 * @version     1.0
 */ 
abstract class CodeplexModel implements IModel
{
    public $logfile = LOGDIR.'/CodeplexModel.class.log';
    
	/** Constructor */
	public function __construct()
	{
        /**
         * in child class use this for automatic path setting to logfile by class name
         * parent::__construct();
         */
        $this->logfile = LOGDIR."/".get_class($this).".class.log";
	}
	
	/**
	 * Create new or return existing instance of class
	 *
	 * @param   void
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
