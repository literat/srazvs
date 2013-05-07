<?php
/**
 * Blocks
 *
 * class for handling program blocks
 *
 * @created 2012-09-14
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
class Block extends Component
{
	/** @var int meeting ID */
	private $meeting_ID;
	
	/**
	 * Array of database block table columns
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
		$this->DB_columns = array(
			"name",
			"day",
			"from",
			"to",
			"program",
			"display_progs",
			"description",
			"tutor",
			"email",
			"category",
			"material",
			"capacity",
			"meeting"
		);
		$this->form_names = array(
			"name",
			"day",
			"start_hour",
			"end_hour",
			"start_minute",
			"end_minute",
			"program",
			"display_progs",
			"description",
			"tutor",
			"email",
			"category",
			"material",
			"capacity"
		);
		$this->dbTable = "kk_blocks";
	}
	
	/**
	 * Render data in a table
	 *
	 * @return	string	html of a table
	 */
	public function renderData()
	{
		$sql = "SELECT 	blocks.id AS id,
						blocks.name AS name,
						cat.name AS cat_name,
						day,
						DATE_FORMAT(`from`, '%H:%i') AS `from`,
						DATE_FORMAT(`to`, '%H:%i') AS `to`,
						description,
						tutor,
						email,
						style
				FROM kk_blocks AS blocks
				LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
				WHERE meeting = '".$this->meeting_ID."' AND blocks.deleted = '0'
				ORDER BY day, `from` ASC";
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
				$html_row .= "<td><a href='process.php?id=".$data['id']."&cms=edit&page=blocks' title='Upravit'><img class='edit' src='".$GLOBALS['ICODIR']."small/edit.gif' /></a></td>\n";
				$html_row .= "<td><a href=\"javascript:confirmation('?id=".$data['id']."&amp;cms=del', 'blok: ".$data['name']." ".$data['from']." -> Opravdu SMAZAT tento blok? Jste si jisti?')\" title='Odstranit'><img class='edit' src='".$GLOBALS['ICODIR']."small/delete.gif' /></a></td>\n";
				$html_row .= "<td class='text'>".$data['id']."</td>\n";
				$html_row .= "<td class='text'>".$data['day']."</td>\n";
				$html_row .= "<td class='text'>".$data['from']."</td>\n";
				$html_row .= "<td class='text'>".$data['to']."</td>\n";
				$html_row .= "<td class='text'>".$data['name']."</td>\n";
				$html_row .= "<td class='text'>".shortenText($data['description'], 70, " ")."</td>\n";
				$html_row .= "<td class='text'>".$data['tutor']."</td>\n";
				$html_row .= "<td class='text'>".$data['email']."</td>\n";
				$html_row .= "<td class='text'><div class='cat-".$data['style']."'>".$data['cat_name']."</div></td>\n";
				$html_row .= "</tr>\n";
			}
		}
		
		// table head
		$html_thead = "<tr>\n";
		$html_thead .= "<th></th>\n";
		$html_thead .= "<th></th>\n";
		$html_thead .= "<th class='tab1'>ID</th>\n";
		$html_thead .= "<th class='tab1'>den</th>\n";
		$html_thead .= "<th class='tab1'>od</th>\n";
		$html_thead .= "<th class='tab1'>do</th>\n";
		$html_thead .= "<th class='tab1'>název</th>\n";
		$html_thead .= "<th class='tab1'>popis</th>\n";
		$html_thead .= "<th class='tab1'>lektor</th>\n";
		$html_thead .= "<th class='tab1'>e-mail</th>\n";
		$html_thead .= "<th class='tab1'>kategorie</th>\n";
		$html_thead .= "</tr>\n";
		
		// table foot
		$html_tfoot = $html_thead;

		// table
		$html_table = "<table id='BlocksTable' class='list tablesorter'>\n";
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
	 * Render select box of blocks
	 *
	 * @param	int		selected option
	 * @return	string	html select box
	 */
	public static function renderHtmlSelect($ID_block)
	{
		$query = "SELECT * FROM kk_blocks WHERE meeting='".$_SESSION['meetingID']."' AND program='1' AND deleted='0'";
		$result = mysql_query($query);

		$html_select = "<select style='width: 300px; font-size: 10px' name='block'>\n";

		while($data = mysql_fetch_assoc($result)){
			if($data['id'] == $ID_block) $selected = "selected";
			else $selected = "";
			$html_select .= "<option ".$selected." value='".$data['id']."'>".$data['day'].", ".$data['from']." - ".$data['to']." : ".$data['name']."</option>\n";
		}
		$html_select .= "</select>\n";

		return $html_select;
	}
	
	/**
	 * Return blocks that contents programs
	 *
	 * @param	int		meeting ID
	 * @return	array	result and number of affected rows
	 */
	public function getProgramBlocks($ID_meeting)
	{
		$query = "SELECT 	id,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`,
					name,
					program
				FROM kk_blocks
				WHERE deleted = '0' AND program='1' AND meeting='".$ID_meeting."'
				ORDER BY `day` ASC";

		$result = mysql_query($query);
		$rows = mysql_affected_rows();

		return array('result' => $result, 'rows' => $rows);
	}
}