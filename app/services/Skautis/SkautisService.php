<?php

namespace App\Services\Skautis;

use Nette\Object;
use Skautis\Skautis;

/**
 * Skautis service
 */
abstract class SkautisService extends Object
{

	/**
	 * Class Table reference
	 * @var instance of BaseTable
	 */
	protected $table;


	/**
	 * Holds Skautis instance
	 * @var Skautis\Skautis
	 */
	protected $skautis;


	/**
	 * Use local storage (cache)?
	 * @var bool
	 */
	private $useCache = TRUE;


	/**
	 * Short term storage for saving Skautis answers
	 * @var type
	 */
	private static $storage;


	/**
	 * Construct
	 */
	public function __construct(Skautis $skautIS = NULL)
	{
		$this->setSkautis($skautIS);
		self::$storage = array();
	}


	/**
	 * Get user information
	 *
	 * @param   void
	 * @return  array  Login ID, Role ID, Unit ID
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
	 * Save value to local storage
	 *
	 * @param   mixed  $id
	 * @param   mixed  $val
	 * @return  mixed
	 */
	protected function save($id, $val)
	{
		if ($this->useCache) {
			self::$storage[$id] = $val;
		}
		return $val;
	}


	/**
	 * Get object from local storage
	 *
	 * @param   string|int   $id
	 * @return  mixed|FALSE
	 */
	protected function load($id)
	{
		if ($this->useCache && array_key_exists($id, self::$storage)) {
			return self::$storage[$id];
		}
		return FALSE;
	}

	/**
	 * @return Skautis\Skautis
	 */
	public function getSkautis(): Skautis
	{
		return $this->skautis;
	}

	/**
	 * @param Skautis\Skautis $skautis
	 * @return self
	 */
	public function setSkautis(Skautis $skautis): self
	{
		$this->skautis = $skautis;

		return $this;
	}

}
