<?php
/**
 * Category
 * 
 * class for handling category and category styles
 *
 * @created 2012-03-06
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class Category extends Component
{
	/**
	 * Array of database block table columns
	 *
	 * @var array	DB_columns[]
	 */
	public $DB_columns = array();
	
	/** Constructor */
	public function __construct()
	{
		$this->DB_columns = array("name", "bgcolor", "bocolor", "focolor");
		$this->dbTable = "kk_categories";
	}
	
	/**
	 * Render a table of categories
	 *
	 * @return	string	html table
	 */
	public function render()
	{		
		$query = "SELECT * FROM kk_categories WHERE deleted = '0' ORDER BY name";
		$result = mysql_query($query);

		$html_row = "";

		while($DB_data = mysql_fetch_assoc($result)){
			$html_row .= "<tr class='radek1'>\n";
			$html_row .= "<td><a href='process.php?id=".$DB_data['id']."&amp;cms=edit' title='Upravit kategorii'>\n";
			$html_row .= "<img class='edit' src='".$GLOBALS['ICODIR']."small/edit.gif' /></a></td>\n";
			$html_row .= "<td><a href=\"javascript:confirmation('?id=".$DB_data['id']."&amp;cms=del', 'opravdu smazat kategorii ".$DB_data['name']."? jste si jisti?')\" title='Odstranit'>\n";
			$html_row .= "<img class='edit' src='".$GLOBALS['ICODIR']."small/delete.gif' /></a></td>\n";
			$html_row .= "<td>".$DB_data['name']."</td>\n";
			$html_row .= "<td>\n";
			$html_row .= "<div class='cat-".$DB_data['style']."'>".$DB_data['style']."</div>\n";
			$html_row .= "</td>\n";
        	$html_row .= "</tr>\n";
		}
		
		// table head
		$html_thead = "<tr>\n";
		$html_thead .= "<th></th>\n";
		$html_thead .= "<th></th>\n";
		$html_thead .= "<th class='tab1'>název</th>\n";
		$html_thead .= "<th class='tab1'>styl</th>\n";
		$html_thead .= "</tr>\n";
		
		// table foot
		$html_tfoot = $html_thead;

		// table
		$html_table = "<table id='CategoryTable' class='list tablesorter'>\n";
		$html_table .= "<thead>\n";
		$html_table .= $html_thead;
		$html_table .= "</thead>\n";
		$html_table .= "<tfoot>\n";
		$html_table .= $html_tfoot;
		$html_table .= "</tfoot>\n";
		$html_table .= "<tbody>\n";
		$html_table .= $html_row;
		$html_table .= "</tbody>\n";
		$html_table .= "</table>\n";
		
		return $html_table;
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
	public function getStyles()
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
	public function renderHtmlSelect($selected_category)
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