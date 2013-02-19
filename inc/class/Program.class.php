<?php
/**
 * Program
 *
 * class for handling programs
 *
 * @created 2012-10-01
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
class Program extends Component
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
	
	/** Constructor */
	public function __construct($meeting_ID)
	{
		$this->meeting_ID = $meeting_ID;
		$this->DB_columns = array("name", "block", "display_in_reg", "description", "tutor", "email", "category", "material", "capacity");
		$this->form_names = array("name", "description", "material", "tutor", "email", "capacity", "display_in_reg", "block", "category");
		$this->dbTable = "kk_programs";
	}

	/**
	 * Get programs
	 *
	 * @param	int		ID of program
	 * @param	int		ID of visitor
	 * @return	boolean 
	 */
	public function getPrograms($id, $vid)
	{
		$sql = "SELECT 	*
				FROM kk_programs
				WHERE block='".$id."' AND deleted='0'
				LIMIT 10";
		$result = mysql_query($sql);
		$rows = mysql_affected_rows();
	
		if($rows == 0){
			$html = "";
		} else {
			/*$progSql = "SELECT progs.name AS prog_name
						FROM kk_programs AS progs
						LEFT JOIN `kk_visitor-program` AS visprog ON progs.id = visprog.program
						LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
						WHERE vis.id = '".$id."'";
				$progResult = mysql_query($progSql);*/
	
	
			$html = "<div>\n";
			
			$checked_flag = false;
			$html_input = "";
			while($data = mysql_fetch_assoc($result)){
				//// resim kapacitu programu a jeho naplneni navstevniky
				$full_program_query = "SELECT COUNT(visitor) AS visitors FROM `kk_visitor-program` AS visprog
									LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
									WHERE program = '".$data['id']."' AND vis.deleted = '0'";
				$full_program_result = mysql_query($full_program_query);
				$DB_full_program = mysql_fetch_assoc($full_program_result);
				
				$program_query = "SELECT * FROM `kk_visitor-program` WHERE program = '".$data['id']."' AND visitor = '".$vid."'";
				$program_result = mysql_query($full_program_query );
				$rows = mysql_affected_rows();
				if($rows == 1){
					$checked = "checked='checked'";
					$checked_flag = true;
				} else {
					$checked = "";
				}
				//$programData = mysql_fetch_assoc($programResult);
			
				if($DB_full_program['visitors'] >= $data['capacity']){
					$html_input .= "<input ".$checked." disabled type='radio' name='".$id."' value='".$data['id']."' />\n";
					$fullProgramInfo = " (NELZE ZAPSAT - kapacita programu je již naplněna!)";
				} else {
					$html_input .= "<input ".$checked." type='radio' name='".$id."' value='".$data['id']."' /> \n";
					$fullProgramInfo = "";
				}
				$html_input .= $data['name'];
				$html_input .= $fullProgramInfo;
				$html_input .= "<br />\n";
			}
			
			// pokud uz jednou bylo zaskrtnuto, nezaskrtavam znovu
			if(!$checked_flag) $checked = "checked='checked'";
			else $checked = "";
			
			$html .= "<input ".$checked." type='radio' name='".$id."' value='0' /> Nebudu přítomen <br />\n";
			$html .= $html_input;
			
			$html .= "</div>\n";
		}
		return $html;
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