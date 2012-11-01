<?php
/**
 * Program
 *
 * class for handling programs
 *
 * @created 2012-10-01
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
class Program
{
	/** @var int meeting ID */
	private $meeting_ID;
	
	/**
	 * Array of database programs table columns
	 *
	 * @var array	DB_columns[]
	 */
	public $DB_columns = array();
	
	/**
	 * Array of form names
	 *
	 * @var array	form_names[]
	 */
	public $form_names = array();
	
	/** konstruktor */
	public function __construct($meeting_ID)
	{
		$this->meeting_ID = $meeting_ID;
		$this->DB_columns = array("name", "block", "display_in_reg", "description", "tutor", "email", "category", "material", "capacity");
		$this->form_names = array("name", "description", "material", "tutor", "email", "capacity", "display_in_reg", "block", "category");
	}

	/**
	 * Create a new program
	 *
	 * @return	boolean
	 */
	public function create($DB_data)
	{
		$query_key_set = "";
		$query_value_set = "";

		foreach($DB_data as $key => $value) {
			$query_key_set .= "`".$key."`,";
			$query_value_set .= "'".$value."',";
		}
		$query_key_set = substr($query_key_set, 0, -1);
		$query_value_set = substr($query_value_set, 0, -1);	

    	$query = "INSERT INTO `kk_programs` 
     				 (".$query_key_set.") 
     				 VALUES (".$query_value_set.");";
					 var_dump($query);
    	$result = mysql_query($query);
		var_dump($result);
		return $result;
	}
	
	/**
	 * Modify a program
	 *
	 * @param	int	ID of a program
	 *
	 * @return	bool
	 */
	public function modify($id, $DB_data)
	{
	 	$query_set = "";
	 	foreach($DB_data as $key => $value) {
			$query_set .= "`".$key."` = '".$value."',";	
		}
	 	$query_set = substr($query_set, 0, -1);	
		
    	$query = "UPDATE `kk_programs` 
					SET ".$query_set."
					WHERE `id`='".$id."' LIMIT 1";
					var_dump($query);
    	$result = mysql_query($query);
		
		return $result;
	}
	
	/**
	 * Delete program
	 *
	 * @param	int	ID of program
	 * @return	boolean 
	 */
	public function delete($id)
	{
    	$query = "UPDATE kk_programs SET deleted = '1' WHERE id = ".$id."	LIMIT 1";
	    $result = mysql_query($query);
		
		return $result;
	}
	
	/**
	 * Render data in a table
	 *
	 * @return	string	html of a table
	 */
	public function renderData()
	{
		$sql = "SELECT 	programs.id AS id,
						programs.name AS name,
						programs.description AS description,
						programs.tutor AS tutor,
						programs.email AS email,
						blocks.name AS block,
						programs.capacity AS capacity,
						style,
						cat.name AS cat_name
				FROM kk_programs AS programs
				LEFT JOIN kk_blocks AS blocks ON blocks.id = programs.block
				LEFT JOIN kk_categories AS cat ON cat.id = programs.category
				WHERE blocks.meeting = '".$this->meeting_ID."' AND programs.deleted = '0' AND blocks.deleted='0'
				ORDER BY programs.id ASC";
				
		$result = mysql_query($sql);
		$rows = mysql_affected_rows();
		
		$html_row = "";
		
		if($rows == 0){
			$html_row .= "<tr class='radek1'>\n";
			$html_row .= "<td><img class='edit' src='".$GLOBALS['ICODIR']."small/edit2.gif' /></td>\n";
			$html_row .= "<td><img class='edit' src='".$GLOBALS['ICODIR']."small/delete2.gif' /></td>\n";
			$html_row .= "<td colspan='11' class='emptyTable'>Nejsou k dispozici žádné položky.</td>\n";
			$html_row .= "</tr>\n";
		}
		else{
			while($data = mysql_fetch_assoc($result)){			
				$html_row .= "<tr class='radek1'>\n";
				$html_row .= "<td><a href='process.php?id=".$data['id']."&cms=edit&page=programs' title='Upravit'><img class='edit' src='".$GLOBALS['ICODIR']."small/edit.gif' /></a></td>\n";
				$html_row .= "<td><a href=\"javascript:confirmation('?id=".$data['id']."&amp;cms=del', 'program: ".$data['name']." -> Opravdu SMAZAT tento program? Jste si jisti?')\" title='Odstranit'><img class='edit' src='".$GLOBALS['ICODIR']."small/delete.gif' /></a></td>\n";
				$html_row .= "<td class='text'>".$data['id']."</td>\n";
				$html_row .= "<td class='text'>".$data['name']."</td>\n";
				$html_row .= "<td class='text'>".shortenText($data['description'], 70, " ")."</td>\n";
				$html_row .= "<td class='text'>".$data['tutor']."</td>\n";
				$html_row .= "<td class='text'>".$data['email']."</td>\n";
				$html_row .= "<td class='text'>".$data['block']."</td>\n";
				$html_row .= "<td class='text'>".$data['capacity']."</td>\n";
				$html_row .= "<td class='text'><div class='cat-".$data['style']."'>".$data['cat_name']."</div></td>\n";
				$html_row .= "</tr>\n";
			}
		}
		
		// table head
		$html_thead = "<tr>\n";
		$html_thead .= "<th></th>\n";
		$html_thead .= "<th></th>\n";
		$html_thead .= "<th class='tab1'>ID</th>\n";
		$html_thead .= "<th class='tab1'>název</th>\n";
		$html_thead .= "<th class='tab1'>popis</th>\n";
		$html_thead .= "<th class='tab1'>lektor</th>\n";
		$html_thead .= "<th class='tab1'>e-mail</th>\n";
		$html_thead .= "<th class='tab1'>blok</th>\n";
		$html_thead .= "<th class='tab1'>kapacita</th>\n";
		$html_thead .= "<th class='tab1'>kategorie</th>\n";
		$html_thead .= "</tr>\n";
		
		// table foot
		$html_tfoot = $html_thead;

		// table
		$html_table = "<table id='ProgramsTable' class='list tablesorter'>\n";
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
}