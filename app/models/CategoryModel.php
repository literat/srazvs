<?php

namespace App;

use Tracy\Debugger;
use Nette\Utils\Strings;

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
	public function __construct($database)
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
	public function create(array $dbData)
	{
		$style = Strings::toAscii($dbData['name']);
		$dbData['style'] = $style;
		$result = $this->getDatabase()
			->table($this->getTable())
			->insert($dbData);

		return $result;
	}

	/**
	 * Modify category details
	 *
	 * @param	int		ID of category
	 * @param	array	Data to DB
	 * @return	boolean
	 */
	public function modify($id, array $dbData)
	{
		$style = Strings::toAscii($dbData['name']);
		$style = str_replace(" ", "_", $style);
		$dbData['style'] = $style;
		$result = $this->getDatabase()
			->table($this->getTable())
			->where('id', $id)
			->update($dbData);

		return $result;
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
	 * @param  int $id
	 * @return ActiveRow
	 */
	public function find($id)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $id)
			->limit(1)
			->fetch();
	}

	public function all()
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('deleted', '0')
			->order('name')
			->fetchAll();
	}

}
