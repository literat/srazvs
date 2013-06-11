<?php
/**
 * Category
 * 
 * class for handling category and category styles
 *
 * @created 2012-03-06
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class CategoryModel extends Component
{
	/**
	 * Array of database block table columns
	 *
	 * @var array	DB_columns[]
	 */
	public $dbColumns = array();
	
	/** Constructor */
	public function __construct()
	{
		$this->dbColumns = array("name", "bgcolor", "bocolor", "focolor");
		$this->dbTable = "kk_categories";
	}
	
	/**
	 * Render a table of categories
	 *
	 * @return	string	html table
	 */
	public function getData()
	{		
		$query = "SELECT * FROM kk_categories WHERE deleted = '0' ORDER BY name";
		$result = mysql_query($query);
		$rows = mysql_affected_rows();

		if($rows == 0) {
			return 0;
		} else {
			return $result;
		}
	}

	/**
	 * Create new category
	 *
	 * @param	array	Data to DB
	 * @return	boolean
	 */
	public function create(array $DB_data)
	{
		$style = removeDiacritic($DB_data['name']);

		$query_key_set = "";
		$query_value_set = "";

		foreach($DB_data as $key => $value) {
			$query_key_set .= "`".$key."`,";
			$query_value_set .= "'".$value."',";
		}
		$query_key_set = substr($query_key_set, 0, -1);
		$query_value_set = substr($query_value_set, 0, -1);	

    	$query = "INSERT INTO `kk_categories` 
     				 (".$query_key_set.", `style`) 
     				 VALUES (".$query_value_set.", '".$style."');";
					 var_dump($query);
    	$result = mysql_query($query);
		
		return $result;
	}
	
	/**
	 * Modify category details
	 *
	 * @param	int		ID of category
	 * @param	array	Data to DB
	 * @return	boolean
	 */
	public function modify($id, array $DB_data)
	{
		$style = removeDiacritic($DB_data['name']);
   		$style = str_replace(" ", "_", $style); 
     
	 	$query_set = "";
	 	foreach($DB_data as $key => $value) {
			$query_set .= "`".$key."` = '".$value."',";	
		}
	 	$query_set = substr($query_set, 0, -1);	
		
    	$query = "UPDATE `kk_categories` 
					SET ".$query_set.", `style` = '".$style."'
					WHERE `id`='".$id."' LIMIT 1";
    	$result = mysql_query($query);
		
		return $result;
	}
		
	/**
	 * Render CSS coding of styles
	 *
	 * @return	string	CSS
	 */
	public static function getStyles()
	{
		$style = "";

		$query = "SELECT * FROM kk_categories WHERE deleted = '0'";
		$result = mysql_query($query);
		
		while($DB_data = mysql_fetch_assoc($result)){
			$style .= ".cat-".$DB_data['style']." {
							border: 2px solid #".$DB_data['bocolor'].";
							background-color: #".$DB_data['bgcolor'].";
							color: #".$DB_data['focolor'].";
							padding: 0px;
							margin-bottom: 1px;
							min-width:125px;
						}";
		}
		
		return $style;
	}
	
	/**
	 * Render HTML <select>
	 *
	 * @param	int	ID of selected category
	 * @return	string	html <select>
	 */
	public static function renderHtmlSelect($selected_category)
	{
		$html_select = "<select style='width: 225px; font-size: 10px' class='field' name='category'>\n";

		$query = "SELECT * FROM kk_categories WHERE 1";
		$result = mysql_query($query);

		while($DB_data = mysql_fetch_assoc($result)){
			if($DB_data['id'] == $selected_category) $selected = "selected";
			else $selected = "";
		
			$html_select .= "<option ".$selected." class='cat-".$DB_data['style']."' value='".$DB_data['id']."'>".$DB_data['name']."</option>\n";
		}
		
		$html_select .= "</select>\n";
		
		return $html_select;
	}
}