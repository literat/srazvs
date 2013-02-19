<?php
/**
 * Meeting
 *
 * class for handling meeting
 *
 * @created 2012-11-09
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class Meeting extends Component
{
	/** @var int meeting ID */
	private $meetingId;
	
	/** @var array days of weekend */
	private $weekendDays = array();
	
	/** @var array of form names */
	public $form_names = array();
	
	/** @var array of database programs table columns */
	public $dbColumns = array();
	
	/** Constructor */
	public function __construct($meetingId = NULL)
	{
		$this->meetingId = $meetingId;
		$this->weekendDays = array("pátek", "sobota", "neděle");
		$this->form_names = array(
			"place",
			"start_date",
			"end_date",
			"open_reg",
			"close_reg",
			"contact",
			"email",
			"gsm",
			"cost",
			"advance",
			"numbering"
		);
		$this->dbColumns = array(
			"place",
			"start_date",
			"end_date",
			"open_reg",
			"close_reg",
			"contact",
			"email",
			"gsm",
			"cost",
			"advance",
			"numbering"
		);
		$this->dbTable = "kk_meetings";
	}
	
	/**
	 * Get meeting price
	 *
	 * @var		string	type of charge
	 * @return	int		return cost or false
	 */
	public function getPrice($type)
	{
		$query = "SELECT cost, advance FROM kk_meetings WHERE id='".$this->meetingId."' LIMIT 1";
		$result = mysql_query($query);
		$data = mysql_fetch_assoc($result);
		
		if($type == 'cost') return $data['cost'];
		elseif($type == 'advance') return $data['advance'];
		else return -1;
	}
	
	/**
	 * Render HTML Provinces <select>
	 *
	 * @param	int	ID of selected province
	 * @return	string	html <select>
	 */
	public function renderHtmlProvinceSelect($selected_province)
	{
		$html_select = "<select style='width: 195px; font-size: 10px' name='province'>\n";

		$query = "SELECT * FROM kk_provinces";
		$result = mysql_query($query);
		
		while($data = mysql_fetch_assoc($result)){
			if($data['id'] == $selected_province){
				$sel = "selected";
			}
			else $sel = "";
			$html_select .= "<option value='".$data['id']."' ".$sel.">".$data['province_name']."</option>";
		}
		
		$html_select .= "</select>\n";
				
		return $html_select;
	}

	/**
	 * Get Programs for Overview
	 *
	 * @return	string	html
	 */
	public function getPrograms($blockId)
	{
		$sql = "SELECT 	progs.id AS id,
						progs.name AS name,
						style
				FROM kk_programs AS progs
				LEFT JOIN kk_categories AS cat ON cat.id = progs.category
				WHERE block='".$blockId."' AND progs.deleted='0'
				LIMIT 10";
		$result = mysql_query($sql);
		$rows = mysql_affected_rows();
	
		if($rows == 0) $html = "";
		else{
			$html = "<table class='programs'>\n";
			$html .= " <tr>\n";
			while($data = mysql_fetch_assoc($result)){			
				$html .= "<td class='cat-".$data['style']."' style='text-align:center;'>\n";
				$html .= "<a class='program' href='../programs/process.php?id=".$data['id']."&cms=edit&page=meetings' title='".$data['name']."'>".$data['name']."</a>\n";
				$html .= "</td>\n";
			}
			$html .= " </tr>\n";
			$html .= "</table>\n";
		}
		return $html;
	}
	
	/**
	 * Render Program Overview
	 *
	 * @return	string	html
	 */
	public function renderProgramOverview()
	{
		$html = "";

		foreach($this->weekendDays as $key => $value){
			$html .= "<table class='blocks'>\n";
			$html .= " <tr>\n";
			$html .= "  <td class='day' colspan='2' >".$value."</td>\n";
			$html .= " </tr>\n";
		
			$sql = "SELECT 	blocks.id AS id,
							day,
							DATE_FORMAT(`from`, '%H:%i') AS `from`,
							DATE_FORMAT(`to`, '%H:%i') AS `to`,
							blocks.name AS name,
							program,
							style
					FROM kk_blocks AS blocks
					LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
					WHERE blocks.deleted = '0' AND day='".$value."' AND meeting='".$this->meetingId."'
					ORDER BY `from` ASC";
		
			$result = mysql_query($sql);
			$rows = mysql_affected_rows();
		
			if($rows == 0){
				$html .= "<td class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</td>\n";
			}
			else{
				while($data = mysql_fetch_assoc($result)){
					$html .= "<tr>\n";
					$html .= "<td class='time'>".$data['from']." - ".$data['to']."</td>\n";
					if($data['program'] == 1){ 
						$html .= "<td class='cat-".$data['style']."'>\n";
						$html .= "<div>\n";
						$html .= "<a class='block' href='".$GLOBALS['BLOCKDIR']."process.php?id=".$data['id']."&cms=edit&page=meetings' title='".$data['name']."'>".$data['name']."</a>\n";
						$html .= "</div>\n";
						$html .= $this->getPrograms($data['id']);
						$html .= "</td>\n";
					}
					else {
						$html .= "<td class='cat-".$data['style']."'>";
						$html .= "<a class='block' href='".$GLOBALS['BLOCKDIR']."process.php?id=".$data['id']."&cms=edit&page=meetings' title='".$data['name']."'>".$data['name']."</a>\n";
						$html .= "</td>\n";
					}
					$html .= "</tr>\n";
				}
			}
			$html .= "</table>\n";
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
		$sql = "SELECT 	id,
						place,
						DATE_FORMAT(start_date, '%d. %m. %Y') AS start_date,
						DATE_FORMAT(end_date, '%d. %m. %Y') AS end_date,
						DATE_FORMAT(open_reg, '%d. %m. %Y %H:%i:%s') AS open_reg,
						DATE_FORMAT(close_reg, '%d. %m. %Y %H:%i:%s') AS close_reg,
						contact,
						email,
						gsm
				FROM kk_meetings
				WHERE deleted = '0'
				LIMIT 30";
		$result = mysql_query($sql);
		$rows = mysql_affected_rows();
		
		$html_row = "";
		
		if($rows == 0){
			$html_row .= "<tr class='radek1'>";
			$html_row .= "<td><img class='edit' src='".$GLOBALS['ICODIR']."small/edit2.gif' /></td>\n";
			$html_row .= "<td><img class='edit' src='".$GLOBALS['ICODIR']."small/delete2.gif' /></td>\n";
			$html_row .= "<td colspan='11' class='emptyTable'>Nejsou k dispozici žádné položky.</td>";
			$html_row .= "</tr>";
		}
		else{
			while($data = mysql_fetch_assoc($result)){			
				$html_row .= "<tr class='radek1'>";
				$html_row .= "<td><a href='process.php?id=".$data['id']."&cms=edit&page=meetings' title='Upravit'><img class='edit' src='".$GLOBALS['ICODIR']."small/edit.gif' /></a></td>\n";
				$html_row .= "<td><a href=\"javascript:confirmation('?id=".$data['id']."&amp;cms=del', 'sraz: ".$data['place']." ".$data['start_date']." -> Opravdu SMAZAT tento sraz? Jste si jisti?')\" title='Odstranit'><img class='edit' src='".$GLOBALS['ICODIR']."small/delete.gif' /></a></td>\n";
				$html_row .= "<td class='text'>".$data['id']."</td>\n";
				$html_row .= "<td class='text'>".$data['place']."</td>\n";
				$html_row .= "<td class='text'>".$data['start_date']."</td>\n";
				$html_row .= "<td class='text'>".$data['end_date']."</td>\n";
				$html_row .= "<td class='text'>".$data['open_reg']."</td>\n";
				$html_row .= "<td class='text'>".$data['close_reg']."</td>\n";
				$html_row .= "<td class='text'>".$data['contact']."</td>\n";
				$html_row .= "<td class='text'>".$data['email']."</td>\n";
				$html_row .= "<td class='text'>".$data['gsm']."</td>\n";
				$html_row .= "</tr>";
			}
		}
		
		// table head
		$html_thead = "<tr>\n";
		$html_thead .= "<th></th>\n";
		$html_thead .= "<th></th>\n";
		$html_thead .= "<th class='tab1'>id</th>\n";
		$html_thead .= "<th class='tab1'>místo</th>\n";
		$html_thead .= "<th class='tab1'>začátek</th>\n";
		$html_thead .= "<th class='tab1'>konec</th>\n";
		$html_thead .= "<th class='tab1'>otevření registrace</th>\n";
		$html_thead .= "<th class='tab1'>uzavření registrace</th>\n";
		$html_thead .= "<th class='tab1'>kontakt</th>\n";
		$html_thead .= "<th class='tab1'>e-mail</th>\n";
		$html_thead .= "<th class='tab1'>telefon</th>\n";
		$html_thead .= "</tr>\n";
		
		// table foot
		$html_tfoot = $html_thead;

		// table
		$html_table = "<table id='MeetingsTable' class='list tablesorter'>\n";
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