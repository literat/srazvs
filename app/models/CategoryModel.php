<?php

namespace App;

use Tracy\Debugger;
use Nette\Utils\Strings;
use Nette\Database\Context;

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
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	/**
	 * Render a table of categories
	 *
	 * @return	string	html table
	 */
	public function getData()
	{
		$data = $this->all();

		if(!$data) {
			Debugger::log('Category: no data found!', Debugger::ERROR);
			return NULL;
		} else {
			return $data;
		}
	}

	/**
	 * Create new category
	 *
	 * @param	array	Data to DB
	 * @return	boolean
	 */
	public function create(array $data)
	{
		$data['style'] = $this->getStyleFromName($data['name']);
		$data['guid'] = $this->generateGuid();

		return $this->getDatabase()
			->table($this->getTable())
			->insert($data);
	}

	/**
	 * @param	integer $id
	 * @param	array  $data
	 * @return	boolean
	 */
	public function modify($id, array $data)
	{
		$data['style'] = $this->getStyleFromName($data['name']);

		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $id)
			->update($data);
	}

	/**
	 * Render HTML <select>
	 *
	 * @param	int	ID of selected category
	 * @return	string	html <select>
	 */
	public function renderHtmlSelect($selectedCategory)
	{
		$html_select = "<select style='width: 225px; font-size: 10px' class='field' name='category'>\n";

		$result = $this->getDatabase()
			->table($this->getTable())
			->where(1)
			->fetchAll();

		foreach($result as $data){
			if($data['id'] == $selectedCategory) $selected = "selected";
			else $selected = "";

			$html_select .= "<option ".$selected." class='category cat-".$data['style']."' value='".$data['id']."'>".$data['name']."</option>\n";
		}

		$html_select .= "</select>\n";

		return $html_select;
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
