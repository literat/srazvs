<?php

namespace App\Models;

use Tracy\Debugger;
use Nette\Utils\Strings;
use Nette\Database\Context;

/**
 * Sunlight
 *
 * class for handling sunlight CMS data
 *
 * @created 2016-12-06
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class SunlightModel extends BaseModel
{

	/** @var string */
	protected $table = 'sunlight-users';

	/**
	 * @param Nette\Database\Context $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	/**
	 * @param  int $uid
	 * @return ActiveRow
	 */
	public function findUser($uid)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $uid)
			->fetch();
	}

}
