<?php

namespace App\Models;

use App\Models\BaseModel;
use Nette\Utils\Json;
use Nette\Database\Context;
use Tracy\Debugger;
use \Exception;

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

	/** @var string */
	protected $table = 'kk_settings';

	/**
	 * @param Context $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	/**
	 * Get all settings
	 *
	 * @return	string	html table
	 */
	public function getData()
	{
		$data = $this->all();

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
		$mailData = [
			'subject' => $subject,
			'message' => $message
		];

		$data = [
			'value' => Json::encode($mailData)
		];

		$result = $this->updateByName($data, 'mail_' . $type);

		if(!$result) {
			Debugger::log('Settings: mail type ' . $type . ' modification failed!', Debugger::ERROR);
			throw new Exception('Mail modification failed!');
		}

		Debugger::log('Settings: mail type ' . $type . ' successfully modified!', Debugger::INFO);

		return $result;
	}

	/**
	 * @param  string $type
	 * @return object
	 */
	public function getMailJSON($type)
	{
		$data = $this->findByName('mail_' . $type);

		return Json::decode($data->value);
	}

	/**
	 * @return ActiveRow
	 */
	public function all()
	{
		return $this->database
			->table($this->getTable())
			->where('deleted', 0)
			->order('name')
			->fetchAll();
	}

	/**
	 * @param  string $name
	 * @return ActiveRow
	 */
	public function findByName($name)
	{
		return $this->database
			->table($this->getTable())
			->where('name', $name)
			->fetch();
	}

	/**
	 * @param  array  $data
	 * @param  string $name
	 * @return ActiveRow
	 */
	public function updateByName(array $data, $name)
	{
		return $this->database
			->table($this->getTable())
			->where('name', $name)
			->update($data);
	}

}
