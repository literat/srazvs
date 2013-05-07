<?php
/**
 * Program
 *
 * class for handling programs
 *
 * @created 2012-10-01
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
class ProgramModel extends Component
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
	 * @param	int		$block_id	ID of block
	 * @param	int		$visitor_id	ID of visitor
	 * @return	string				html
	 */
	public function getPrograms($block_id, $vid)
	{
		$query = "SELECT 	*
				FROM kk_programs
				WHERE block='".$block_id."' AND deleted='0'
				LIMIT 10";
		$result = mysql_query($query);
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
				// full program capacity with visitors
				$full_program_query = "SELECT COUNT(visitor) AS visitors FROM `kk_visitor-program` AS visprog
									LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
									WHERE program = '".$data['id']."' AND vis.deleted = '0'";
				$full_program_result = mysql_query($full_program_query);
				$DB_full_program = mysql_fetch_assoc($full_program_result);
				
				// if the program is checked
				$program_query = "SELECT * FROM `kk_visitor-program` WHERE program = '".$data['id']."' AND visitor = '".$vid."'";
				$program_result = mysql_query($program_query);
				$rows = mysql_affected_rows();
				if($rows == 1){
					$checked = "checked='checked'";
					$checked_flag = true;
				} else {
					$checked = "";
				}
				// if the capacity is full
				if($DB_full_program['visitors'] >= $data['capacity']){
					$html_input .= "<input ".$checked." disabled type='radio' name='".$block_id."' value='".$data['id']."' />\n";
					$fullProgramInfo = " (NELZE ZAPSAT - kapacita programu je již naplněna!)";
				} else {
					$html_input .= "<input ".$checked." type='radio' name='".$block_id."' value='".$data['id']."' /> \n";
					$fullProgramInfo = "";
				}
				$html_input .= $data['name'];
				$html_input .= $fullProgramInfo;
				$html_input .= "<br />\n";
			}
			
			// pokud uz jednou bylo zaskrtnuto, nezaskrtavam znovu
			if(!$checked_flag) $checked = "checked='checked'";
			else $checked = "";
			
			$html .= "<input ".$checked." type='radio' name='".$block_id."' value='0' /> Nebudu přítomen <br />\n";
			$html .= $html_input;
			
			$html .= "</div>\n";
		}
		
		return $html;
	}
	
	public function getExportPrograms($blockId)
	{
		$sql = "SELECT 	*
				FROM kk_programs
				WHERE block='".$blockId."' AND deleted='0'
				LIMIT 10";
		$result = mysql_query($sql);
		$rows = mysql_affected_rows();
	
		if($rows == 0){
			$html = "";
		}
		else{
			$html = "<table>\n";
			while($data = mysql_fetch_assoc($result)){
				$html .= "<tr>";
				//// resim kapacitu programu a jeho naplneni navstevniky
				$fullProgramSql = "SELECT COUNT(visitor) AS visitors 
								   FROM `kk_visitor-program` AS visprog
								   LEFT JOIN `kk_visitors` AS vis ON vis.id = visprog.visitor
								   WHERE program = '".$data['id']."'
										AND vis.deleted = '0'";
				$fullProgramResult = mysql_query($fullProgramSql);
				$fullProgramData = mysql_fetch_assoc($fullProgramResult);
			
				if($fullProgramData['visitors'] >= $data['capacity']){
					//$html .= "<input disabled type='radio' name='".$id."' value='".$data['id']."' />\n";
					$fullProgramInfo = "<span style='font-size:12px; font-weight:bold;'>".$fullProgramData['visitors']."/".$data['capacity']."</span> (kapacita programu je naplněna!)";
				}
				else {
					//$html .= "<input type='radio' name='".$id."' value='".$data['id']."' /> \n";
					$fullProgramInfo = "<span style='font-size:12px; font-weight:bold;'>".$fullProgramData['visitors']."/".$data['capacity']."</span>";
				}
				$html .= "<td style='min-width:270px;'>";
				$html .= "<a rel='programDetail' href='../programs/process.php?id=".$data['id']."&cms=edit' title='".$data['name']."'>".$data['name']."</a>\n";
				$html .= "</td>";
				$html .= "<td>";
				$html .= $fullProgramInfo;
				$html .= "</td>";
				$html .= "</tr>\n";
			}
			$html .= "</table>\n";
		}
		return $html;
	}
	
	public function renderExportPrograms()
	{
		$programs = "";

		$progSql = "SELECT 	id,
							day,
							DATE_FORMAT(`from`, '%H:%i') AS `from`,
							DATE_FORMAT(`to`, '%H:%i') AS `to`,
							name,
							program
					FROM kk_blocks
					WHERE deleted = '0' AND program='1' AND meeting='".$this->meeting_ID."'
					ORDER BY `day`, `from` ASC";
		
		$progResult = mysql_query($progSql);
		$progRows = mysql_affected_rows();
		
		if($progRows == 0){
			$programs .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
		}
		else{
			//// prasarnicka kvuli programu raftu - resim obsazenost dohromady u dvou polozek
			$raftCountSql = "SELECT COUNT(visitor) AS raft FROM `kk_visitor-program` WHERE program='56|57'";
			$raftCountResult = mysql_query($raftCountSql);
			$raftCountData = mysql_fetch_assoc($raftCountResult);
			
			while($progData = mysql_fetch_assoc($progResult)){
				//nemoznost volit predsnemovni dikusi
				if($progData['id'] == 63) $notDisplayed = "style='display:none;'";
				//obsazenost raftu
				/*elseif($raftCountData['raft'] >= 18){
					if($progData['id'] == 86) $notDisplayed = "style='display:none;'";
					else $notDisplayed = "";
				}*/
				else $notDisplayed = "";
				$programs .= "<div ".$notDisplayed.">".$progData['day'].", ".$progData['from']." - ".$progData['to']." : ".$progData['name']."</div>\n";
				if($progData['program'] == 1) $programs .= "<div ".$notDisplayed.">".$this->getExportPrograms($progData['id'])."</div>";
				$programs .= "<br />";
			}
		}
		
		return $programs;
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
			$html_row .= "<td><img class='edit' src='".IMG_DIR."icons/edit2.gif' /></td>\n";
			$html_row .= "<td><img class='edit' src='".IMG_DIR."icons/delete2.gif' /></td>\n";
			$html_row .= "<td colspan='11' class='emptyTable'>Nejsou k dispozici žádné položky.</td>\n";
			$html_row .= "</tr>\n";
		}
		else{
			while($data = mysql_fetch_assoc($result)){			
				$html_row .= "<tr class='radek1'>\n";
				$html_row .= "<td><a href='process.php?id=".$data['id']."&cms=edit&page=programs' title='Upravit'><img class='edit' src='".IMG_DIR."icons/edit.gif' /></a></td>\n";
				$html_row .= "<td><a href='../programs/process.php?cms=program-visitors&id=".$data['id']."' title='Účastníci programu'><img class='edit' src='".IMG_DIR."icons/pdf.png' /></a></td>\n";
				$html_row .= "<td><a href=\"javascript:confirmation('?id=".$data['id']."&amp;cms=del', 'program: ".$data['name']." -> Opravdu SMAZAT tento program? Jste si jisti?')\" title='Odstranit'><img class='edit' src='".IMG_DIR."icons/delete.gif' /></a></td>\n";
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
	
	/**
	 * Get programs on registration
	 *
	 * @param	int		ID of program
	 * @param	string	disabled
	 * @return	string	html
	 */
	public function getProgramsRegistration ($id, $disabled)
	{
		$sql = "SELECT 	*
			FROM kk_programs
			WHERE block='".$id."' AND deleted='0'
			LIMIT 10";
		$result = mysql_query($sql);
		$rows = mysql_affected_rows();
	
		if($rows == 0){
			$html = "";
		}
		else{
			$html = "<div>\n";
			$html .= "<input ".$disabled." checked type='radio' name='".$id."' value='0' /> Nebudu přítomen <br />\n";
			while($data = mysql_fetch_assoc($result)){
				//// resim kapacitu programu a jeho naplneni navstevniky
				$fullProgramSql = " SELECT COUNT(visitor) AS visitors FROM `kk_visitor-program` AS visprog
									LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
									WHERE program = '".$data['id']."' AND vis.deleted = '0'";
				$fullProgramResult = mysql_query($fullProgramSql);
				$fullProgramData = mysql_fetch_assoc($fullProgramResult);
			
				// nezobrazeni programu v registraci, v adminu zaskrtavatko u programu
				if($data['display_in_reg'] == 0) $notDisplayedProg = "style='display:none;'";
				else $notDisplayedProg = "";
			
				if($fullProgramData['visitors'] >= $data['capacity']){
					$html .= "<div ".$notDisplayedProg."><input disabled type='radio' name='".$id."' value='".$data['id']."' />\n";
					$fullProgramInfo = " (NELZE ZAPSAT - kapacita programu je již naplněna!)";
				}
				else {
					$html .= "<div ".$notDisplayedProg."><input ".$disabled." type='radio' name='".$id."' value='".$data['id']."' /> \n";
					$fullProgramInfo = "";
				}
				$html .= "<a class='programLink' rel='programDetail' href='".HTTP_DIR."srazvs/detail.php?id=".$data['id']."&type=program' title='".file_get_contents(HTTP_DIR.'srazvs/detail.php?id='.$data['id'].'&type=program')."' target='_blank'>".$data['name']."</a>\n";
				$html .= $fullProgramInfo;
				$html .= "</div>\n";
			}
			$html .= "</div>\n";
		}
		return $html;
	}
	
	/**
	 * Get visitors registred on program
	 *
	 * @param	int		$programId	ID of program
	 * @return	string	html
	 */
	public function getProgramVisitors($programId)
	{
		$html = "  <div style='border-bottom:1px solid black;text-align:right;'>účastníci</div>";
		
		$html .= "<br /><a style='text-decoration:none; display:block; margin-bottom:4px;' href='?cms=program-visitors&id=".$programId."'>
      	<img style='border:none;' align='absbottom' src='".IMG_DIR."icons/pdf.png' />Účastníci programu</a>";

		$query = "SELECT vis.name AS name,
							vis.surname AS surname,
							vis.nick AS nick
					FROM kk_visitors AS vis
					LEFT JOIN `kk_visitor-program` AS visprog ON vis.id = visprog.visitor
					WHERE visprog.program = '".$programId."' AND vis.deleted = '0'";
		$result = mysql_query($query);
		$i = 1;
		while($data = mysql_fetch_assoc($result)){
			$html .= $i.". ".$data['name']." ".$data['surname']." - ".$data['nick']."<br />";
			$i++;
		}
		return $html;
	}
}