<?php

namespace App;

use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Visitor
 *
 * class for handling visitors
 *
 * @created 2012-11-07
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class VisitorModel
{
	/** @var int meeting ID */
	private $meetingId;

	/** @var string	search pattern */
	public $search;

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

	/** @var Connection database */
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
		MeetingModel $Meeting,
		MealModel $Meals,
		ProgramModel $Program,
		BlockModel $Blocks,
		$database
	) {
		$this->Meeting = $Meeting;
		$this->meeting_price = $this->Meeting->getPrice('cost');
		$this->meeting_advance = $this->Meeting->getPrice('advance');
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
		$this->database = $database;
	}

	public function setMeetingId($id)
	{
		$this->meetingId = $id;
	}

	/**
	 * Create a new visitor
	 *
	 * @return	boolean
	 */
	public function create(array $DB_data, $meals_data, $programs_data, $returnGuid = false)
	{
		$return = true;

		$DB_data['code'] =
			Strings::substring($DB_data['name'], 0, 1)
			. Strings::substring($DB_data['surname'], 0, 1)
			. Strings::substring($DB_data['birthday'], 2, 2);

		$DB_data['birthday'] = new \DateTime($DB_data['birthday']);
		$DB_data['reg_daytime'] = (new \DateTime())->format('Y-m-d H:i:s');
		$DB_data['guid'] = md5(uniqid());

		$ID_visitor = $this->database
			->table($this->dbTable)
			->insert($DB_data)->id;

		// visitor's id is empty and i must add one
		$meals_data['visitor'] = $ID_visitor;

		if($ID_visitor){
			// gets data from database
			$program_blocks = $this->Blocks->getProgramBlocks($DB_data['meeting']);

			foreach($program_blocks as $DB_blocks_data){
				$bindingsData = array(
					'visitor' => $ID_visitor,
					'program' => $programs_data[$DB_blocks_data['id']],
				);
				// insert into binding table
				// var programs_data contains requested values in format block-id => program-id
				$bindingsData['guid'] = md5(uniqid());
				$result_binding = $this->database->query('INSERT INTO `kk_visitor-program`', $bindingsData);

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
		if($returnGuid) {
			return $DB_data['guid'];
		} else {
			return $ID_visitor;
		}
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

		$DB_data['birthday'] = new \DateTime($DB_data['birthday']);

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
		foreach($programBlocks as $programBlock){
			$data = array('program' => $programs_data[$programBlock->id]);
			// read first value from array and shift it to the end
			$oldProgram = array_shift($oldPrograms);

			$result = $this->database
				->table('kk_visitor-program')
				->where('visitor ? AND id ?', $ID_visitor, (empty($oldProgram)) ? $oldProgram : $oldProgram->id)
				->update($data);
		}

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
	public function modifyByGuid($guid, $DB_data, $meals_data, $programs_data)
	{
		// for returning specific error
		$error = array('visitor' => TRUE, 'meal' => TRUE, 'program' => TRUE);

		$DB_data['code'] =
			Strings::substring($DB_data['name'], 0, 1)
			. Strings::substring($DB_data['surname'], 0, 1)
			. Strings::substring($DB_data['birthday'], 2, 2);

		$DB_data['birthday'] = new \DateTime($DB_data['birthday']);

		$result = $this->database
			->table($this->dbTable)
			->where('guid', $guid)
			->update($DB_data);

		$visitor = $this->findByGuid($guid);

		// change meals
		$result = $this->Meals->modify($visitor->id, $meals_data);
		$error['meal'] = $result;

		// gets data from database
		$programBlocks = $this->Blocks->getProgramBlocks($DB_data['meeting']);

		// get program of visitor
		$oldPrograms = $this->getVisitorPrograms($visitor->id);

		// update old data to new existing
		foreach($programBlocks as $programBlock){
			$data = array('program' => $programs_data[$programBlock->id]);
			// read first value from array and shift it to the end
			$oldProgram = array_shift($oldPrograms);

			$result = $this->database
				->table('kk_visitor-program')
				->where('visitor ? AND id ?', $visitor->id, (empty($oldProgram)) ? $oldProgram : $oldProgram->id)
				->update($data);
		}

		return $guid;
	}

	/**
	 * Delete one or multiple record/s
	 *
	 * @param	int		ID/s of record
	 * @return	boolean
	 */
	public function delete($id)
	{
		$deleted = array('deleted' => '1');

		return $this->database
			->table($this->dbTable)
			->where('id', $id)
			->update($deleted);
	}

	/**
	 * Set as checked one or multiple record/s
	 *
	 * @param	int		ID/s of record
	 * @param 	int 	0 | 1
	 * @return	boolean
	 */
	public function checked($id, $value)
	{
		$checked = ['checked' => $value];

		return $this->database
			->table($this->dbTable)
			->where('id', $id)
			->update($checked);
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
			->where('meeting ? AND deleted ?', $this->meetingId, '0')
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
		$billData = $this->database
			->table($this->dbTable)
			->select('bill')
			->where('id', $query_id)
			->fetch();

		if($billData['bill'] < $this->Meeting->getPrice('cost')){
			$bill = array('bill' => $this->Meeting->getPrice($type));
			$payResult = $this->database
				->table($this->dbTable)
				->where('id', $query_id)
				->update($bill);

			return $payResult;
		} else {
			return 'already_paid';
		}
	}

	/**
	 * Get recipients by ids
	 *
	 * @param	mixed	ID of visitor
	 * @return	mixed	result
	 */
	public function getRecipients($ids)
	{
		return $this->database
			->table($this->dbTable)
			->select('email', 'name', 'surname')
			->where('id ? AND deleted ?', $ids, 0)
			->fetchAll();
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
				$html .= "<div class='".\Tools::toCoolUrl($block['day'])."'>".$block['day'].", ".$block['from']." - ".$block['to']." : ".$block['name']."</div>\n";
				// rendering programs in block
				if($block['program'] == 1){
					$html .= "<div class='programs ".\Tools::toCoolUrl($block['day'])." ".\Tools::toCoolUrl($block['name'])."'>".$this->Programs->getPrograms($block['id'], $visitorId)."</div>";
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
						$this->meetingId, '0')->fetchAll();
		}

		if(!$data) {
			return 0;
		} else {
			return $data;
		}
	}

	/**
	 * Return visitor by id
	 *
	 * @param  int    $id
	 * @return mixed
	 */
	public function findById($id)
	{
		return $this->database
				->table($this->dbTable)
				->where('id ? AND deleted ?', $id, '0')
				->limit(1)
				->fetch();
	}

	/**
	 * Return visitor by guid
	 *
	 * @param  string  $guid
	 * @return mixed
	 */
	public function findByGuid($guid)
	{
		return $this->database
				->table($this->dbTable)
				->where('guid ? AND deleted ?', $guid, '0')
				->limit(1)
				->fetch();
	}

	/**
	 * Get visitors mail
	 *
	 * @param 	int|string 	$query_id 	id/s of visitors seperated by comma
	 * @return 	string 					e-mail addresses
	 */
	public function getMail($query_id) {
		$recipient_mails = '';

		$result = $this->database
			->table($this->dbTable)
			->select('email')
			->where('id', $query_id)
			->group('email')
			->fetchAll();

		foreach($result as $data){
			$recipient_mails .= $data['email'].",\n";
		}

		return $recipient_mails;
	}
}
