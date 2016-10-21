<?php

/**
 * Base service
 */
abstract class BaseService extends Nette\Object
{

	/**
	 * Class Table reference
	 * @var instance of BaseTable
	 */
	protected $table;


	/**
	 * Holds SkautIS instance
	 * @var Skautis\Skautis
	 */
	protected $skautis;


	/**
	 * Use local storage (cache)?
	 * @var bool
	 */
	private $useCache = TRUE;


	/**
	 * Short term storage for saving SkautIS answers
	 * @var type
	 */
	private static $storage;


	/**
	 * Construct
	 */
	public function __construct(Skautis\Skautis $skautIS = NULL)
	{
		$this->skautis = $skautIS;
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
		return array(
			"ID_Login" => $this->skautis->getUser()->getLoginId(),
			"ID_Role" => $this->skautis->getUser()->getRoleId(),
			"ID_Unit" => $this->skautis->getUser()->getUnitId(),
		);
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

}
