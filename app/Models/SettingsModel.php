<?php
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
	 * Render a table of categories
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
		$json_encoded = array('subject' => $subject, 'message' => $message);
		$json_encoded = json_encode($json_encoded);
		$json_encoded = mysql_real_escape_string($json_encoded);
		
		echo $update_query = "UPDATE kk_settings
						 SET value = '".$json_encoded."'
						 WHERE name = 'mail_".$type."'";
		$update_result = mysql_query($update_query);
		
		if($update_query){
			$error = 'E_UPDATE_NOTICE';
			$error = 'ok';
		}
		else {
			$error = 'E_UPDATE_ERROR';
		}
		
		return $error;
	}

	public static function getMailJSON($type) {
		$query = "SELECT * FROM kk_settings WHERE name = 'mail_".$type."'";
		$result = mysql_query($query);
		$data = mysql_fetch_assoc($result);

		return json_decode($data['value']);
	}
}