<?php
/**
 * Program model
 *
 * class for handling programs
 *
 * @created 2012-10-01
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class ProgramModel extends Component
{
	/** @var int meeting ID */
	private $meetingId;

	/**
	 * Array of database programs table columns
	 *
	 * @var array	DB_columns[]
	 */
	public $dbColumns = array();

	/**
	 * Array of form names
	 *
	 * @var array	form_names[]
	 */
	public $formNames = array();

	private $configuration;

	/** Constructor */
	public function __construct($meeting_id, $configuration, $database)
	{
		$this->meetingId = $meeting_id;
		$this->dbColumns = array("name", "block", "display_in_reg", "description", "tutor", "email", "category", "material", "capacity");
		$this->formNames = array("name", "description", "material", "tutor", "email", "capacity", "display_in_reg", "block", "category");
		$this->dbTable = "kk_programs";
		$this->configuration = $configuration;
		$this->database = $database;
	}

	/**
	 * Get programs
	 *
	 * @param	int		$block_id	ID of block
	 * @param	int		$visitor_id	ID of visitor
	 * @return	string				html
	 */
	public function getPrograms($blockId, $vid)
	{
		$blocks = $this->database
			->table($this->dbTable)
			->where('block ? AND deleted ?', $blockId, '0')
			->limit(10)
			->fetchAll();

		if(!$blocks){
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
			foreach($blocks as $data){
				// full program capacity with visitors
				$fullProgramData = $this->database
					->query('SELECT COUNT(visitor) AS visitors FROM `kk_visitor-program` AS visprog
							LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
							WHERE program = ? AND vis.deleted = ?',
							$data['id'], '0')->fetch();

				// if the program is checked
				$program = $this->database
					->table('kk_visitor-program')
					->where('program ? AND visitor ?', $data['id'], $vid)
					->fetch();

				if($program){
					$checked = "checked='checked'";
					$checked_flag = true;
				} else {
					$checked = "";
				}
				// if the capacity is full
				if($fullProgramData['visitors'] >= $data['capacity']){
					$html_input .= "<input id='".$data['id'].$blockId."' ".$checked." disabled type='radio' name='".$blockId."' value='".$data['id']."' />\n";
					$fullProgramInfo = " (NELZE ZAPSAT - kapacita programu je již naplněna!)";
				} else {
					$html_input .= "<input id='".$data['id'].$blockId."' ".$checked." type='radio' name='".$blockId."' value='".$data['id']."' /> \n";
					$fullProgramInfo = "";
				}
				$html_input .= '<label for="'.$data['id'].$blockId.'">'.$data['name'].'</label>';
				$html_input .= $fullProgramInfo;
				$html_input .= "<br />\n";
			}

			// pokud uz jednou bylo zaskrtnuto, nezaskrtavam znovu
			if(!$checked_flag) $checked = "checked='checked'";
			else $checked = "";

			$html .= "<input ".$checked." type='radio' name='".$blockId."' value='0' /> Nebudu přítomen <br />\n";
			$html .= $html_input;

			$html .= "</div>\n";
		}

		return $html;
	}

	public function getExportPrograms($blockId)
	{
		$exportPrograms = $this->database
			->table($this->dbTable)
			->where('block ? AND deleted ?', $blockId, '0')
			->limit(10)
			->fetchAll();

		if(!$exportPrograms) {
			$html = "";
		} else {
			$html = "<table>\n";
			foreach($exportPrograms as $data){
				$html .= "<tr>";
				//// resim kapacitu programu a jeho naplneni navstevniky
				$fullProgramData = $this->database
					->query('SELECT COUNT(visitor) AS visitors
							FROM `kk_visitor-program` AS visprog
							LEFT JOIN `kk_visitors` AS vis ON vis.id = visprog.visitor
							WHERE program = ?
							AND vis.deleted = ?',
							$data['id'], '0')->fetch();

				if($fullProgramData['visitors'] >= $data['capacity']){
					//$html .= "<input disabled type='radio' name='".$id."' value='".$data['id']."' />\n";
					$fullProgramInfo = "<span style='font-size:12px; font-weight:bold;'>".$fullProgramData['visitors']."/".$data['capacity']."</span> (kapacita programu je naplněna!)";
				}
				else {
					//$html .= "<input type='radio' name='".$id."' value='".$data['id']."' /> \n";
					$fullProgramInfo = "<span style='font-size:12px; font-weight:bold;'>".$fullProgramData['visitors']."/".$data['capacity']."</span>";
				}
				$html .= "<td style='min-width:270px;'>";
				$html .= "<a rel='programDetail' href='".PRJ_DIR."program/?id=".$data['id']."&cms=edit&page=export' title='".$data['name']."'>".$data['name']."</a>\n";
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

		$progSql = $this->database
			->table('kk_blocks')
			->select('
				id,
				day,
				DATE_FORMAT(`from`, "%H:%i") AS `from`,
				DATE_FORMAT(`to`, "%H:%i") AS `to`,
				name,
				program'
			)
			->where('deleted = ? AND program = ? AND meeting = ?', '0', '1', $this->meetingId)
			->order('day ASC, from ASC')
			->fetchAll();

		if(!$progSql){
			$programs .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
		} else {
			//// prasarnicka kvuli programu raftu - resim obsazenost dohromady u dvou polozek
			//$raftCountSql = "SELECT COUNT(visitor) AS raft FROM `kk_visitor-program` WHERE program='56|57'";
			//$raftCountResult = mysql_query($raftCountSql);
			//$raftCountData = mysql_fetch_assoc($raftCountResult);

			foreach($progSql as $progData){
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
	public function getData($program_id = NULL)
	{
		if(isset($program_id)) {
			$data = $this->database
				->table($this->dbTable)
				->where('id ? AND deleted ?', $program_id, '0')
				->limit(1)
				->fetch();
		} else {
			$data = $this->database
				->query('SELECT programs.id AS id,
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
				WHERE blocks.meeting = ? AND programs.deleted = ? AND blocks.deleted = ?
				ORDER BY programs.id ASC',
				$this->meetingId, '0', '0')->fetchAll();
		}

		return $data;
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
		$result = $this->database
			->table($this->dbTable)
			->where('block ? AND deleted ?', $id, '0')
			->limit(10)
			->fetchAll();

		if(!$result){
			$html = "";
		}
		else{
			$html = "<div>\n";
			$html .= "<input ".$disabled." checked type='radio' name='".$id."' value='0' /> Nebudu přítomen <br />\n";
			foreach($result as $data){
				//// resim kapacitu programu a jeho naplneni navstevniky
				$fullProgramData = $this->database
					->query('SELECT COUNT(visitor) AS visitors FROM `kk_visitor-program` AS visprog
							LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
							WHERE program = ? AND vis.deleted = ?',
							$data['id'], '0')->fetch();

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
	 * @return	string	html or null
	 */
	public function getProgramVisitors($programId = NULL)
	{
		if(!isset($programId)) {
			return NULL;
		} else {
			$html = "  <div style='border-bottom:1px solid black;text-align:right;'>účastníci</div>";

			$html .= "<br /><a style='text-decoration:none; display:block; margin-bottom:4px;' href='?cms=export-visitors&id=".$programId."'>
	      	<img style='border:none;' align='absbottom' src='".IMG_DIR."icons/pdf.png' />Účastníci programu</a>";

			$result = $this->database
				->query('SELECT vis.name AS name,
								vis.surname AS surname,
								vis.nick AS nick
						FROM kk_visitors AS vis
						LEFT JOIN `kk_visitor-program` AS visprog ON vis.id = visprog.visitor
						WHERE visprog.program = ? AND vis.deleted = ?',
						$programId, '0')->fetchAll();
			$i = 1;
			foreach($result as $data){
				$html .= $i.". ".$data['name']." ".$data['surname']." - ".$data['nick']."<br />";
				$i++;
			}

			return $html;
		}
	}

	public function getSelectedPrograms($visitorId) {
		$programs = "  <div style='border-bottom:1px solid black;text-align:right;'>vybrané programy</div>";

		$result = $this->database
			->query('SELECT progs.name AS prog_name,
							day,
							DATE_FORMAT(`from`, "%H:%i") AS `from`,
							DATE_FORMAT(`to`, "%H:%i") AS `to`
					FROM kk_programs AS progs
					LEFT JOIN `kk_visitor-program` AS visprog ON progs.id = visprog.program
					LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
					LEFT JOIN kk_blocks AS blocks ON progs.block = blocks.id
					WHERE vis.id = ?
					ORDER BY `day`, `from` ASC',
					$visitorId)->fetchAll();

		foreach($result as $progData){
			$programs .= $progData['day'].", ".$progData['from']." - ".$progData['to']."";
			$programs .= "<div style='padding:5px 0px 5px 20px;'>- ".$progData['prog_name']."</div>";
		}

		return $programs;
	}

	public static function getPdfPrograms($id, $vid, $database){
		$result = $database
			->table('kk_programs')
			->where('block ? AND deleted ?', $id, '0')
			->limit(10)
			->fetchAll();

		if(!$result){
			$html = "";
		} else {

			$html = "<div class='program'>\n";

			foreach($result as $data){
				$rows = $database
					->table('kk_visitor-program')
					->where('program ? AND visitor ?', $data->id, $vid)
					->fetchAll();
				if($rows == 1) $html .= $data['name'];
			}
			$html .= "</div>\n";
		}
		return $html;
	}

	public static function getProgramsLarge($id){
		$result = $this->database
			->query('SELECT progs.name AS name,
						cat.style AS style
				FROM kk_programs AS progs
				LEFT JOIN kk_categories AS cat ON cat.id = progs.category
				WHERE block = ? AND progs.deleted = ?
				LIMIT 10',
				$id, '0')->fetchAll();

		if(!$result) $html = "";
		else {
			$html = "<table>";
			$html .= " <tr>";
			foreach($result as $data){
				$html .= "<td class='category cat-".$data['style']."' >".$data['name']."</td>\n";
			}
			$html .= " </tr>\n";
			$html .= "</table>\n";
		}
		return $html;
	}

	public static function getProgramNames($block_id, $database)
	{
		$result = $database
			->table('kk_programs')
			->select('name')
			->where('block ? AND deleted ?', $block_id, '0')
			->limit(10)
			->fetchAll();

		$html = '';

		if(!$result) $html = "";
		else {
			foreach($result as $data){
				$html .= $data['name'].",\n";
			}
		}
		return $html;
	}

	public static function getDetail($id, $type, $cfg, $database)
	{
		$data = $database
			->table('kk_' . $type . 's')
			->where('id ? AND deleted ?', $id, '0')
			->limit(1)
			->fetch();

		$name = requested("name",$data['name']);
		$description = requested("description",$data['description']);
		$tutor = requested("tutor",$data['tutor']);
		$email = requested("email",$data['email']);

		if($type == "program"){
			$capacity = requested("capacity",$data['capacity']);

			$countData = $database
				->query('SELECT COUNT(visitor) AS visitors
						 FROM `kk_visitor-program` AS visprog
						 LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
						 WHERE program = ? AND vis.deleted = ?',
						 $data->id, '0')->fetch();

			$inner_html = "<tr>\n";
			$inner_html .= " <td class=\"label\">Obsazenost programu:</td>\n";
			$inner_html .= " <td class=\"text\">".$countData['visitors']."/".$capacity."</td>\n";
		    $inner_html .= "</tr>\n";
		}
		else $inner_html = "";

		$html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset='.$cfg['http-encoding'].'" /></head><body><style>td.text {text-align:left;}</style><table class="form"><tr><td class="label">Program:</td><td class="text">'.$name.'</td></tr><tr><td class="label">Popis:</td><td class="text">'.$description.'</td></tr><tr><td class="label">Lektor:</td><td class="text">'.$tutor.'</td></tr><tr><td class="label">E-mail:</td><td class="text"><a href="mailto:'.$email.'" title="e-mail">'.$email.'</a></td></tr>'.$inner_html.'</table></body></html>';

		return $html;
	}
}
