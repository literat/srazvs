<?php
/**
 * Visitor
 *
 * class for handling visitors
 *
 * @created 2012-11-07
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
class VisitorModel /* extends Component */
{
	/** @var int meeting ID */
	private $meeting_ID;
	
	/** @var string	search pattern */
	public $search;
	
	/** @var Emailer Emailer class */
	private $Emailer;
	
	/** @var Meeting Meeting class */
	public $Meeting;
	
	/** @var Meal Meals class */
	public $Meals;
	
	/** @var Program Programs class */
	public $Programs;
	
	/** @var Blocks Blocks class */
	public $Blocks;
	
	/** @var int meeting price */
	public $meeting_price;
	
	/** @var int meeting advance */
	private $meeting_advance;
	
	/**
	 * Array of database programs table columns
	 *
	 * @var array	dbColumns[]
	 */
	public $dbColumns = array();
	
	/**
	 * Array of form names
	 *
	 * @var array	formNames[]
	 */
	public $formNames = array();
	
	/** konstruktor */
	public function __construct($meeting_ID, Emailer $Emailer, MeetingModel $Meeting, MealModel $Meals, ProgramModel $Program, BlockModel $Blocks)
	{
		$this->Emailer = $Emailer;
		$this->Meeting = $Meeting;
		$this->meeting_price = $this->Meeting->getPrice('cost');
		$this->meeting_advance = $this->Meeting->getPrice('advance');
		$this->meeting_ID = $meeting_ID;
		$this->Meals = $Meals;
		$this->Programs = $Program;
		$this->Blocks = $Blocks;
		$this->dbColumns = array(
								"name",
								"surname",
								"nick",
								"birthday",
								"street",
								"city",
								"postal_code",
								"province",
								"group_num",
								"group_name",
								"troop_name",
								"bill",
								"email",
								"comment",
								"arrival",
								"departure",
								"question",
								"meeting"
							);
		$this->formNames = array("name", "description", "material", "tutor", "email", "capacity", "display_in_reg", "block", "category");
		$this->dbTable = "kk_visitors";
	}

	/**
	 * Create a new visitor
	 *
	 * @return	boolean
	 */
	public function create(array $DB_data, $meals_data, $programs_data)
	{
		$return = true;
		
		$query_key_set = "";
		$query_value_set = "";

		foreach($DB_data as $key => $value) {
			$query_key_set .= "`".$key."`,";
			$query_value_set .= "'".$value."',";
		}
		$query_key_set = substr($query_key_set, 0, -1);
		$query_value_set = substr($query_value_set, 0, -1);	

    	$query = "INSERT INTO `kk_visitors` 
     				 (".$query_key_set.", `code`,`reg_daytime`) 
     				 VALUES (".$query_value_set.", CONCAT(LEFT('".$DB_data['name']."',1),LEFT('".$DB_data['surname']."',1),SUBSTRING('".$DB_data['birthday']."',3,2)),'".date('Y-m-d H:i:s')."');";
        $result = mysql_query($query);
		
		$ID_visitor = mysql_insert_id();
		// visitor's id is empty and i must add one
		$meals_data['visitor'] = $ID_visitor;
		
		if($result){
			// gets data from database
			$program_blocks = $this->Blocks->getProgramBlocks($DB_data['meeting']);
			
			while($DB_blocks_data = mysql_fetch_assoc($program_blocks['result'])){
				// insert into binding table
				// var programs_data contains requested values in format block-id => program-id
				$query_binding = "INSERT INTO `kk_visitor-program` (`visitor`, `program`)
							   VALUES ('".$ID_visitor."', '".$programs_data[$DB_blocks_data['id']]."')";
				$result_binding = mysql_query($query_binding);

				if(!$result_binding){
					$return = "ERROR_BINDING_VISITOR_PROGRAM";
					break;
				}
			}
						
			if($return) {
				// create meals for visitor
				if(!$return = $this->Meals->create($meals_data)){
					$return = "ERROR_CREATE_MEALS";
				}
			}
		} else {
			$return = "ERROR_CREATE_VISITOR";
		}

		return $return;
	}
	
	/**
	 * Modify a visitor
	 *
	 * @param	int		$visitor_id		ID of a visitor
	 * @param	array	$db_data		Visitor's database data
	 * @param	array	$meals_data		Data of meals
	 * @param	array	$programs_data	Program's data
	 * @return	mixed					TRUE or array of errors 
	 */
	public function modify($ID_visitor, $DB_data, $meals_data, $programs_data)
	{
		// for returning specific error
		$error = array('visitor' => TRUE, 'meal' => TRUE, 'program' => TRUE);
		// preparation for query
	 	$query_set = "";
	 	foreach($DB_data as $key => $value) {
			$query_set .= "`".$key."` = '".$value."',";	
		}
	 	$query_set = substr($query_set, 0, -1);	
		
		// updating visitor data
    	$query = "UPDATE `kk_visitors` 
					SET ".$query_set."
					WHERE `id`='".$ID_visitor."' LIMIT 1";
    	$result = mysql_query($query);
		$error['visitor'] = $result;
		
		if($result){
			// change meals
			$result = $this->Meals->modify($ID_visitor, $meals_data);
			$error['meal'] = $result;

			if($result){
				// gets data from database
				$program_blocks = $this->Blocks->getProgramBlocks($DB_data['meeting']);
				// get program of visitor
				$old_program = $this->getVisitorPrograms($ID_visitor);
				// update old data to new existing
				while($DB_block_data = mysql_fetch_assoc($program_blocks['result']) and $DB_old_program_data = mysql_fetch_assoc($old_program)){
					$usr_program_query = "UPDATE `kk_visitor-program` 
									SET `program` = ".$programs_data[$DB_block_data['id']]." 
									WHERE visitor = ".$ID_visitor."
									AND id = ".$DB_old_program_data['id'].";";
					$usr_program_result = mysql_query($usr_program_query);
					$error['program'] = $usr_program_result;
				}
			}
		}
		
		// return array of errors if error exists
		if(array_search(FALSE, $error)){
			return $result;
		} else {
			return TRUE;
		}
	}
	
	/**
	 * Delete one or multiple record/s
	 *
	 * @param	int		ID/s of record
	 * @return	boolean 
	 */
	public function delete($id)
	{
    	$query = "UPDATE ".$this->dbTable." SET deleted = '1' WHERE id IN (".$id.")";
	    $result = mysql_query($query);
		
		return $result;
	}
	
	/**
	 * Get count of visitors
	 *
	 * @return	int	count of visitors
	 */
	public function getCount()
	{
		//// ziskam pocet ucastniku
		$query = "SELECT COUNT(id) AS visitors_count
					FROM kk_visitors
					WHERE meeting='".$this->meeting_ID."' AND deleted='0'";
		$result = mysql_query($query);
		$data = mysql_fetch_assoc($result);
		
		return $data['visitors_count'];
	}
	
	/**
	 * Set search variable
	 *
	 * @param	string	what we want to find
	 */
	public function setSearch($search)
	{
		$this->search = $search;
	}

	/**
	 * Prepare search patter for database query
	 *
	 * @param	string	what we want to find
	 * @return	string	search query for database
	 */	
	public function getSearch($search)
	{
		if($search != ""){
			$search_query = "AND (`code` REGEXP '".$search."' 
					  		OR `group_num` REGEXP '".$search."' 
							OR `name` REGEXP '".$search."' 
							OR `surname` REGEXP '".$search."'
							OR `nick` REGEXP '".$search."'
							OR `city` REGEXP '".$search."' 
							OR `group_name` REGEXP '".$search."')";
		} else $search_query = "";
		
		return $search_query;
	}
	
	/**
	 * Modify the visitor's bill
	 *
	 * @param	int	ID/s of visitor
	 * @param	string	type of payment (pay | advance)
	 * @return	string	error message or true
	 */
	public function payCharge($query_id, $type)
	{
		$billSql = "SELECT bill FROM kk_visitors WHERE id IN (".$query_id.")";
		$billResult = mysql_query($billSql);
		$billData = mysql_fetch_assoc($billResult);

		if($billData['bill'] < $this->Meeting->getPrice('cost')){
			$paySql = "UPDATE kk_visitors
					SET bill = '".$this->Meeting->getPrice($type)."'
					WHERE id IN (".$query_id.")";
			$payResult = mysql_query($paySql);
		
			if($return = $this->Emailer->sendPaymentInfo($query_id, $type)) {
				return true;
			} else {
				return $return;
			}
		} else {
			return $error = "already_paid";
		}
	}

	/**
	 * Get visitor's programs
	 *
	 * @param	int		ID of visitor
	 * @return	mixed	result
	 */	
	public function getVisitorPrograms($ID_visitor)
	{
		$query = "SELECT id, program
					FROM `kk_visitor-program`
					WHERE `visitor` = ".$ID_visitor."";
		$result = mysql_query($query);
		
		return $result;
	}
	
	/**
	 * Render program switcher for unique visitor
	 *
	 * @param	int		ID of meeting
	 * @param	int		ID of visitor
	 * @return	string	html
	 */
	public function renderProgramSwitcher($ID_meeting, $ID_visitor)
	{
		$html = "";
		
		// gets data from database
		$program_blocks = $this->Blocks->getProgramBlocks($ID_meeting);
		
		// table is empty
		if($program_blocks['rows'] == 0){
			$html .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
		} else {	
			while($DB_data = mysql_fetch_assoc($program_blocks['result'])){
				$html .= "<div>".$DB_data['day'].", ".$DB_data['from']." - ".$DB_data['to']." : ".$DB_data['name']."</div>\n";
				// rendering programs in block
				if($DB_data['program'] == 1){
					$html .= "<div>".$this->Programs->getPrograms($DB_data['id'], $ID_visitor)."</div>";
				}
				$html .= "<br />";
			}
		}
		
		return $html;
	}
	
	/**
	 * Render data in a table
	 *
	 * @return	string	html of a table
	 */
	public function getData($visitor_id = NULL)
	{
		if(isset($visitor_id)) {
			$query = "SELECT	*
						FROM kk_visitors
						WHERE id='".$visitor_id."' AND deleted = '0'
						LIMIT 1";
		} else {
			$query = "SELECT 	vis.id AS id,
								code,
								name,
								surname,
								nick, 
								email,
								group_name,
								group_num,
								city,
								province_name AS province,
								bill,
								birthday
								/*CONCAT(LEFT(name,1),LEFT(surname,1),SUBSTRING(birthday,3,2)) AS code*/
						FROM kk_visitors AS vis
						LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
						WHERE meeting='".$this->meeting_ID."' AND deleted='0' ".$this->getSearch($this->search)."
						ORDER BY vis.id ASC";
		}
				
		$result = mysql_query($query);
		$rows = mysql_affected_rows();
		
		if($rows == 0) {
			return 0;
		} else {
			return $result;
		}
	}
}