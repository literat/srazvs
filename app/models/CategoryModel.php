<?php

namespace App\Models;

use Tracy\Debugger;
use Nette\Utils\Strings;
use Nette\Database\Context;
use Nette\Caching\Cache;

/**
 * Category
 *
 * class for handling category and category styles
 *
 * @created 2012-03-06
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class CategoryModel extends BaseModel
{
	/** @var array */
	public $dbColumns = [
		'name',
		'bgcolor',
		'bocolor',
		'focolor',
	];

	/** @var string */
	protected $table = 'kk_categories';

	/**
	 * @param Nette\Database\Context $database
	 */
	public function __construct(Context $database, Cache $cache)
	{
		$this->setDatabase($database);
		$this->setCache($cache);
	}

	/**
	 * @param	array  $data
	 * @return	boolean
	 */
	public function create(array $data)
	{
		$data['style'] = $this->getStyleFromName($data['name']);

		return parent::create($data);
	}

	/**
	 * @param	integer $id
	 * @param	array  $data
	 * @return	boolean
	 */
	public function update($id, array $data)
	{
		$data['style'] = $this->getStyleFromName($data['name']);

		return parent::update($id, $data);
	}

	/**
	 * @param  integer $id
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function find($id)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $id)
			->limit(1)
			->fetch();
	}

	/**
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function all()
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
