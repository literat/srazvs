<?php

use Nette\Utils\Json;

/**
 * Settings
 * 
 * class for handling settings
 *
 * @created 2012-03-06
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class SettingsModel extends Component
{
	/**
	 * Array of database block table columns
	 *
	 * @var array	DB_columns[]
	 */
	public $dbColumns = array();
	
	/** Constructor */
	public function __construct()
	{
		$this->dbColumns = array("name", "bgcolor", "bocolor", "focolor");
		$this->dbTable = "kk_settings";
	}
	
	/**
	 * Get all settings
	 *
	 * @return	string	html table
	 */
	public function getData()
	{		
		$query = "SELECT * FROM kk_settings WHERE deleted = '0' ORDER BY name";
		$result = mysql_query($query);
		$rows = mysql_affected_rows();

		if($rows == 0) {
			return 0;
		} else {
			return $result;
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

		global $database;
		$result = $database->table('kk_settings')->where('name', 'mail_' . $type)->update($value);

		if($result){
			$error = 'E_UPDATE_NOTICE';
			$error = 'ok';
		}
		else {
			$error = 'E_UPDATE_ERROR';
		}
		
		return $error;
	}

	public static function getMailJSON($type) {
		global $database;
		$mailJson = $database->query('SELECT * FROM kk_settings WHERE name = ?', 'mail_' . $type)->fetch();

		return json_decode($mailJson->value);
	}
}