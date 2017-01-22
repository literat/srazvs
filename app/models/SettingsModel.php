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
	 * @return	Nette\Database\Table\ActiveRow
	 */
	public function allOrFail()
	{
		$data = $this->all();

		if(!$data) {
			throw new Exception('Settings: no data found!');
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
			throw new Exception('Mail modification failed!');
		} else {
			return $result;
		}
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
		return $this->getDatabase()
			->table($this->getTable())
			->order('name')
			->fetchAll();
	}

	/**
	 * @param  string $name
	 * @return ActiveRow
	 */
	public function findByName($name)
	{
		return $this->getDatabase()
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
		return $this->getDatabase()
			->table($this->getTable())
			->where('name', $name)
			->update($data);
	}

}
