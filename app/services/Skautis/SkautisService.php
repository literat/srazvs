<?php

namespace App\Services\Skautis;

use Nette\SmartObject;
use Skautis\Skautis;

abstract class SkautisService
{
	use SmartObject;

	/**
	 * @var \Skautis\Skautis
	 */
	protected $skautis;


	/**
	 * Use local storage (cache)?
	 * @var bool
	 */
	private $useCache = true;


	/**
	 * Short term storage for saving Skautis answers.
	 * @var array
	 */
	private static $storage = [];

	public function __construct(Skautis $skautIS = null)
	{
		$this->setSkautis($skautIS);
	}


	/**
	 * Get user information.
	 *
	 * @param  void
	 * @return array Login ID, Role ID, Unit ID
	 */
	public function getInfo()
	{
		$skautis = $this->getSkautis();

		return [
			"ID_Login" => $skautis->getUser()->getLoginId(),
			"ID_Role"  => $skautis->getUser()->getRoleId(),
			"ID_Unit"  => $skautis->getUser()->getUnitId(),
		];
	}


	/**
	 * Save value to local storage.
	 *
	 * @param  mixed $id
	 * @param  mixed $val
	 * @return mixed
	 */
	protected function save($id, $val)
	{
		if ($this->useCache) {
			self::$storage[$id] = $val;
		}
		return $val;
	}


	/**
	 * Get object from local storage.
	 *
	 * @param  string|int  $id
	 * @return mixed|FALSE
	 */
	protected function load($id)
	{
		if ($this->useCache && array_key_exists($id, self::$storage)) {
			return self::$storage[$id];
		}
		return false;
	}

	public function getSkautis(): Skautis
	{
		return $this->skautis;
	}

	public function setSkautis(Skautis $skautis): self
	{
		$this->skautis = $skautis;

		return $this;
	}
}
