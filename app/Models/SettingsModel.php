<?php

namespace App;

use Nette\Utils\Json;
use Tracy\Debugger;

/**
 * Settings
 *
 * class for handling settings
 *
 * @created 2012-03-06
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class SettingsModel extends BaseModel
{
	/**
	 * Array of database block table columns
	 *
	 * @var array	DB_columns[]
	 */
	public $dbColumns = array();

	/** Constructor */
	public function __construct($database)
	{
		$this->dbTable = "kk_settings";
		$this->database = $database;
	}

	/**
	 * Get all settings
	 *
	 * @return	string	html table
	 */
	public function getData()
	{
		$data = $this->database
			->table($this->dbTable)
			->where('deleted', 0)
			->order('name')
			->fetchAll();

		if(!$data) {
			Debugger::log('Settings: no data found!', Debugger::ERROR);
			return NULL;
		} else {
			return $data;
		}
	}

	/**
	 * Modify mail subject and message
	 *
	 * @param	string 	$subject 	e-mail subject
	 * @param	string	$message 	e-mail message
	 * @return	string 				error code
	 */
	public function modifyMailJSON($type, $subject, $message)
	{
		$mailData = array('subject' => $subject, 'message' => $message);

		$value = array('value' => Json::encode($mailData));

		$result = $this->database
			->table($this->dbTable)
			->where('name', 'mail_' . $type)
			->update($value);

		if($result) {
			Debugger::log('Settings: mail type ' . $type . ' successfully modified!', Debugger::INFO);
			$error = 'E_UPDATE_NOTICE';
			$error = 'ok';
		} else {
			Debugger::log('Settings: mail type ' . $type . ' modification failed!', Debugger::ERROR);
			$error = 'E_UPDATE_ERROR';
		}

		return $error;
	}

	public function getMailJSON($type)
	{
		$data = $this->database
			->table($this->dbTable)
			->where('name', 'mail_' . $type)
			->fetch();

		return Json::decode($data->value);
	}
}
