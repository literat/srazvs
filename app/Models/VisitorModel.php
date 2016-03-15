<?php

use Nix\Utils\Tools;
use Nette\Utils\Strings;

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

	/** @var array configuration */
	public $configuration;

	/** @var array configuration */
	private $database;

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
	public function __construct(
		$meeting_ID,
		Emailer $Emailer,
		MeetingModel $Meeting,
		MealModel $Meals,
		ProgramModel $Program,
		BlockModel $Blocks,
		$configuration,
		$database
	) {
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
								"cost",
								"email",
								"comment",
								"arrival",
								"departure",
								"question",
								"question2",
								"checked",
								"meeting",
								"hash"
							);
		$this->formNames = array("name", "description", "material", "tutor", "email", "capacity", "display_in_reg", "block", "category");
		$this->dbTable = "kk_visitors";
		$this->configuration = $configuration;
		$this->database = $database;
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

		//return $return;
		return $ID_visitor;
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

		$DB_data['code'] =
			Strings::substring($DB_data['name'], 0, 1)
			. Strings::substring($DB_data['surname'], 0, 1)
			. Strings::substring($DB_data['birthday'], 2, 2);

		$result = $this->database
			->table($this->dbTable)
			->where('id', $ID_visitor)
			->update($DB_data);

		// change meals
		$result = $this->Meals->modify($ID_visitor, $meals_data);
		$error['meal'] = $result;

		// gets data from database
		$programBlocks = $this->Blocks->getProgramBlocks($DB_data['meeting']);

		// get program of visitor
		$oldPrograms = $this->getVisitorPrograms($ID_visitor);

		// update old data to new existing
		foreach($programBlocks as $key => $programBlock){
			if(!array_key_exists($key, $oldPrograms)) continue;

			$data = array('program' => $programs_data[$programBlock->id]);
			$result = $this->database
				->table('kk_visitor-program')
				->where('visitor ? AND id ?', $ID_visitor, $oldPrograms[$key]->id)
				->update($data);
		}

		return $ID_visitor;
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
	 * Set as checked one or multiple record/s
	 *
	 * @param	int		ID/s of record
	 * @return	boolean
	 */
	public function checked($id)
	{
		$query = "UPDATE ".$this->dbTable." SET checked = '1' WHERE id IN (".$id.")";
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
		$visitorsCount = $this->database
			->table($this->dbTable)
			->where('meeting ? AND deleted ?', $this->meeting_ID, '0')
			->count('id');

		return $visitorsCount;
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
	public function getVisitorPrograms($visitorId)
	{
		$result = $this->database
			->table('kk_visitor-program')
			->select('id, program')
			->where('visitor', $visitorId)
			->fetchAll();

		return $result;
	}

	/**
	 * Render program switcher for unique visitor
	 *
	 * @param	int		ID of meeting
	 * @param	int		ID of visitor
	 * @return	string	html
	 */
	public function renderProgramSwitcher($meetingId, $visitorId)
	{
		$html = "";

		// gets data from database
		$programBlocks = $this->Blocks->getProgramBlocks($meetingId);

		// table is empty
		if(!$programBlocks){
			$html .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
		} else {
			foreach($programBlocks as $block){
				$html .= "<div class='".Tools::toCoolUrl($block['day'])."'>".$block['day'].", ".$block['from']." - ".$block['to']." : ".$block['name']."</div>\n";
				// rendering programs in block
				if($block['program'] == 1){
					$html .= "<div class='programs ".Tools::toCoolUrl($block['day'])." ".Tools::toCoolUrl($block['name'])."'>".$this->Programs->getPrograms($block['id'], $visitorId)."</div>";
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
			$data = $this->database
				->table($this->dbTable)
				->where('id ? AND deleted ?', $visitor_id, '0')
				->limit(1)
				->fetch();
		} else {
			$data = $this->database->query('SELECT 	vis.id AS id,
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
								cost,
								birthday,
								/*CONCAT(LEFT(name,1),LEFT(surname,1),SUBSTRING(birthday,3,2)) AS code*/
								checked
						FROM kk_visitors AS vis
						LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
						WHERE meeting = ? AND deleted = ? ' . $this->getSearch($this->search) . '
						ORDER BY vis.id ASC',
						$this->meeting_ID, '0')->fetchAll();
		}

		if(!$data) {
			return 0;
		} else {
			return $data;
		}
	}

	/**
	 * Get visitors mail
	 *
	 * @param 	int|string 	$query_id 	id/s of visitors seperated by comma
	 * @return 	string 					e-mail addresses
	 */
	public function getMail($query_id) {
		$recipient_mails = '';

		$query = "SELECT email FROM kk_visitors WHERE id IN (".$query_id.") GROUP BY email";
		$query_result = mysql_query($query);
		while($data = mysql_fetch_assoc($query_result)){
			$recipient_mails .= $data['email'].",\n";
		}

		return $recipient_mails;
	}
}
