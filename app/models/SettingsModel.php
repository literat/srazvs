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

		$result = $this->updateByName($mailData, 'mail_' . $type);

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
	 * @param  mixed  $value
	 * @param  string $name
	 * @return ActiveRow
	 */
	public function updateByName($value, $name)
	{
		//dd($value, $this->encodeValue($value));
		return $this->getDatabase()
			->table($this->getTable())
			->where('name', $name)
			->update($this->encodeValue($value));
	}

	/**
	 * @param  mixed $data
	 * @return Row
	 */
	public function updateDebugRegime($data)
	{
		return $this->updateByName($data, 'debug');
	}

	/**
	 * @param  mixed $value
	 * @return array
	 */
	protected function encodeValue($value): array
	{
		return [
			'value' => Json::encode($value)
		];
	}

}
