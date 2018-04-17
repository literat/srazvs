<?php

namespace App\Models;

use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Json;

class SettingsModel extends BaseModel
{

	/**
	 * @var string
	 */
	protected $table = 'kk_settings';

	/**
	 * @param Context $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	public function allOrFail(): ActiveRow
	{
		$data = $this->all();

		if (!$data) {
			throw new \Exception('Settings: no data found!');
		} else {
			return $data;
		}
	}

	/**
	 * Modify mail subject and message.
	 */
	public function modifyMailJson(string $type, string $subject, string $message): string
	{
		$mailData = [
			'subject' => $subject,
			'message' => $message
		];

		$result = $this->updateByName($mailData, 'mail_' . $type);

		if (!$result) {
			throw new \Exception('Mail modification failed!');
		} else {
			return $result;
		}
	}

	/**
	 * @param  string $type
	 * @return object
	 */
	public function getMailJson($type)
	{
		return $this->findByName('mail_' . $type);
	}

	public function all(): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->order('name')
			->fetchAll();
	}

	/**
	 * @param  string                     $name
	 * @return ActiveRow
	 * @throws \Nette\Utils\JsonException
	 */
	public function findByName($name)
	{
		$result = $this->getDatabase()
			->table($this->getTable())
			->where('name', $name)
			->fetch();

		return Json::decode($result->value);
	}

	/**
	 * @param  mixed     $value
	 * @param  string    $name
	 * @return ActiveRow
	 */
	public function updateByName($value, $name)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('name', $name)
			->update($this->encodeValue($value));
	}

	public function updateDebugRegime($data): ActiveRow
	{
		return $this->updateByName($data, 'debug');
	}

	public function findDebugRegime(): bool
	{
		return (bool) $this->findByName('debug');
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
