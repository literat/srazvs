<?php

namespace App\Models;

use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

class ProvinceModel extends BaseModel
{

	/**
	 * @var string
	 */
	protected $table = 'kk_provinces';

	/**
	 * @param Context $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		$provinces = $this->getDatabase()
			->table($this->getTable())
			->select('id')
			->select('province_name')
			->where('deleted', '0')
			->fetchAll();

		array_walk($provinces, function(&$province, $key) {
			$province = $province->province_name;
		});

		unset($provinces[0]);

		return $provinces;
	}

}
