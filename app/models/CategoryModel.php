<?php

namespace App\Models;

use Nette\Caching\Cache;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;

class CategoryModel extends BaseModel
{
	/**
	 * @var array
	 */
	public $dbColumns = [
		'name',
		'bgcolor',
		'bocolor',
		'focolor',
	];

	/**
	 * @var string
	 */
	protected $table = 'kk_categories';

	/**
	 * @param Context $database
	 * @param Cache   $cache
	 */
	public function __construct(Context $database, Cache $cache)
	{
		$this->setDatabase($database);
		$this->setCache($cache);
	}

	/**
	 * @param  array   $data
	 * @return boolean
	 */
	public function create(array $data)
	{
		$data['style'] = $this->getStyleFromName($data['name']);

		return parent::create($data);
	}

	/**
	 * @param  integer $id
	 * @param  array   $data
	 * @return boolean
	 */
	public function update($id, array $data)
	{
		$data['style'] = $this->getStyleFromName($data['name']);

		return parent::update($id, $data);
	}

	/**
	 * @param  integer                         $id
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function find($id): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $id)
			->limit(1)
			->fetch();
	}

	public function all(): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('deleted', '0')
			->order('name')
			->fetchAll();
	}

	/**
	 * @param  string $name
	 * @return string
	 */
	protected function getStyleFromName($name)
	{
		$style = Strings::toAscii($name);
		$style = str_replace(" ", "_", $style);

		return $style;
	}
}
