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
	/**
	 * Array of database block table columns
	 *
	 * @var array	DB_columns[]
	 */
	public $dbColumns = array();

	/** Constructor */
	public function __construct($database)
	{
		$this->dbColumns = array("name", "bgcolor", "bocolor", "focolor");
		$this->dbTable = "kk_categories";
		$this->database = $database;
	}

	/**
	 * Render a table of categories
	 *
	 * @return	string	html table
	 */
	public function getData()
	{
		$data = $this->database
			->table($this->dbTable)
			->where('deleted', '0')
			->order('name')
			->fetchAll();

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
		$result = $this->database
			->table($this->dbTable)
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
		$result = $this->database
			->table($this->dbTable)
			->where('id', $id)
			->update($dbData);

		return $result;
	}

	/**
	 * Render CSS coding of styles
	 *
	 * @return	string	CSS
	 */
	public function getStyles()
	{
		$style = "";

		$data = $this->database
			->table($this->dbTable)
			->where('deleted', '0')
			->fetchAll();

		foreach($data as $id => $category) {
			$style .= "
				.cat-" . $category->style . " {
					border: 2px solid #" . $category->bocolor . ";
					background-color: #" . $category->bgcolor . ";
					color: #" . $category->focolor . ";
				}
			";
		}

		return $style;
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

		$result = $this->database
			->table('kk_categories')
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
		return $this->database
			->table('kk_categories')
			->where('id', $id)
			->limit(1)
			->fetch();
	}
}
