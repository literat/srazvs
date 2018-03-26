<?php

namespace App\Models;

use Nette\Database\Context;
use Nette\Utils\Strings;
use \Exception;
use DateTime;
use Tracy\Debugger;

/**
 * Visitor
 *
 * class for handling visitors
 *
 * @created 2012-11-07
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class VisitorModel extends BaseModel
{

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

	/**
	 * Array of database programs table columns
	 *
	 * @var array	dbColumns[]
	 */
	public $dbColumns = [];

	/**
	 * Array of form names
	 *
	 * @var array	formNames[]
	 */
	public $formNames = [];

	protected $table = 'kk_visitors';

	protected $columns = [
		'name',
		'surname',
		'nick',
		'email',
		'birthday',
		'street',
		'city',
		'postal_code',
		'group_num',
		'group_name',
		'troop_name',
		'province',
		'arrival',
		'departure',
		'comment',
		'question',
		'question2',
		'bill',
		'cost',
		'meeting',
	];

	/** konstruktor */
	public function __construct(
		MeetingModel $Meeting,
		MealModel $Meals,
		ProgramModel $Program,
		BlockModel $Blocks,
		Context $database
	) {
		$this->Meeting = $Meeting;
		$this->meeting_price = $this->Meeting->getPrice('cost');
		$this->meeting_advance = $this->Meeting->getPrice('advance');
		$this->Meals = $Meals;
		$this->Programs = $Program;
		$this->Blocks = $Blocks;
		$this->dbColumns = [
			"guid",
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
			"hash",
		];
		$this->formNames = [
			"name", "description", "material", "tutor", "email", "capacity", "display_in_reg", "block", "category"
		];
		$this->database = $database;
	}

	/**
	 * @return array
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * @param  $guid
	 * @return ActiveRow
	 */
	public function findByGuid($guid)
	{
		return $this->findBy('guid', $guid);
	}

	/**
	 * Create a new visitor
	 *
	 * @return	string
	 */
	public function assemble(array $DB_data, $meals_data, $programs_data, $returnGuid = false)
	{
		$return = true;

		if(!$DB_data['province']) {
			$DB_data['province'] = 0;
		}

		$DB_data['birthday'] = new \DateTime($DB_data['birthday']);
		$DB_data['reg_daytime'] = (new \DateTime())->format('Y-m-d H:i:s');
		$DB_data['guid'] = md5(uniqid());

		$ID_visitor = $this->database
			->table($this->getTable())
			->insert($DB_data)->id;

		// visitor's id is empty and i must add one
		$meals_data['visitor'] = $ID_visitor;

		if($ID_visitor){
			// gets data from database
			$program_blocks = $this->Blocks->getProgramBlocks($DB_data['meeting']);

			foreach($program_blocks as $DB_blocks_data) {
				$bindingsData = [
					'visitor' => $ID_visitor,
					'program' => $programs_data[$DB_blocks_data['id']],
				];
				// insert into binding table
				// var programs_data contains requested values in format block-id => program-id
				$bindingsData['guid'] = md5(uniqid());
				$result_binding = $this->database->query('INSERT INTO `kk_visitor-program`', $bindingsData);

				if(!$result_binding) {
					throw new Exception('Error while binding visitor`s program');
				}
			}

			if($return) {

				// create meals for visitor
				if(!$return = $this->Meals->create($meals_data)) {
					throw new Exception('Error while creating meals');
				}
			}
		} else {
			throw new Exception('Error while creating visitor');
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
	public function modify(int $visitorId, array $visitor, array $meals, array $programs)
	{
		$visitor['birthday'] = $this->convertToDateTime($visitor['birthday']);

		$result = $this->getDatabase()
			->table($this->getTable())
			->where('id', $visitorId)
			->update($visitor);

		// change meals
		$result = $this->Meals->updateOrCreate($visitorId, $meals);

		// gets data from database
		$programBlocks = $this->Blocks->getProgramBlocks($visitor['meeting']);

		// get program of visitor
		$oldPrograms = $this->findVisitorPrograms($visitorId);

		// update old data to new existing
		foreach($programBlocks as $programBlock) {
			// read first value from array and shift it to the end
			$oldProgram = array_shift($oldPrograms);

			$this->updateOrCreateProgram(
				$visitorId,
				(empty($oldProgram)) ? $oldProgram : $oldProgram->id,
				$programs[$programBlock->id]
			);
		}

		return $visitorId;
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
	public function modifyByGuid($guid, $visitor, $meals, $programs)
	{
		$visitor['birthday'] = $this->convertToDateTime($visitor['birthday']);

		$result = $this->database
			->table($this->getTable())
			->where('guid', $guid)
			->update($visitor);

		$visitor = $this->findByGuid($guid);

		// change meals
		$result = $this->Meals->updateOrCreate($visitor->id, $meals);

		// gets data from database
		$programBlocks = $this->Blocks->getProgramBlocks($visitor['meeting']);

		// get program of visitor
		$oldPrograms = $this->findVisitorPrograms($visitor->id);

		// update old data to new existing
		foreach($programBlocks as $programBlock) {
			// read first value from array and shift it to the end
			$oldProgram = array_shift($oldPrograms);

			$this->updateOrCreateProgram(
				$visitor->id,
				(empty($oldProgram)) ? $oldProgram : $oldProgram->id,
				$programs[$programBlock->id]
			);
		}

		return $guid;
	}

	/**
	 * @param int $visitorId
	 * @param int $oldProgramId
	 * @param int $newProgramId
	 * @return mixed
	 */
	public function updateOrCreateProgram(int $visitorId, int $oldProgramId, int $newProgramId)
	{
		$result = $this->getDatabase()
			->table('kk_visitor-program')
			->where('visitor ? AND id ?', $visitorId, $oldProgramId)
			->update([
				'program' => $newProgramId,
			]);

		if(!$result) {
			$result = $this->getDatabase()
				->table('kk_visitor-program')
				->where('visitor ? AND id ?', $visitorId, $oldProgramId)
				->insert([
					'guid'    => $this->generateGuid(),
					'visitor' => $visitorId,
					'program' => $newProgramId,
				]);
		}

		return $result;
	}

	/**
	 * Delete one or multiple record/s
	 *
	 * @param	int		ID/s of record
	 * @return	boolean
	 */
	public function delete($id)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $id)
			->update([
				'deleted' => '1'
			]);
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

		return $this->getDatabase()
			->table($this->getTable())
			->where('id', $id)
			->update($checked);
	}

	/**
	 * Get count of visitors
	 *
	 * @return  integer
	 */
	public function getCount()
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('meeting ? AND deleted ?', $this->getMeetingId(), '0')
			->count('id');
	}

	/**
	 * @param  string $search
	 * @return $this
	 */
	public function setSearch($search)
	{
		$this->search = $search;

		return $this;
	}

	/**
	 * @return string
	 */
	protected function getSearch()
	{
		return $this->search;
	}

	/**
	 * @return string
	 */
	protected function buildSearchQuery()
	{
		$search = $this->getSearch();

		$query = '';
		if($search) {
			$query = "AND (`code` REGEXP '" . $search . "'
							OR `group_num` REGEXP '" . $search . "'
							OR `name` REGEXP '" . $search . "'
							OR `surname` REGEXP '" . $search . "'
							OR `nick` REGEXP '" . $search . "'
							OR `city` REGEXP '" . $search . "'
							OR `group_name` REGEXP '" . $search . "')";
		}

		return $query;
	}

	/**
	 * Modify the visitor's bill
	 *
	 * @param	int	ID/s of visitor
	 * @param	string	type of payment (pay | advance)
	 * @return	string	error message or true
	 */
	public function payCharge($ids, $type)
	{
		$bill = $this->getBill($ids)['bill'];
		$cost = $this->Meeting->getPrice('cost');

		if($bill < $cost) {
			$newBill = ['bill' => $this->Meeting->getPrice($type)];
			$payResult = $this->getDatabase()
				->table($this->getTable())
				->where('id', $ids)
				->update($newBill);

			return $payResult;
		} else {
			throw new Exception('Charge already paid!');
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
		return $this->getDatabase()
			->table($this->getTable())
			->select('email, name, surname')
			->where('id', $ids)
			->where('deleted', '0')
			->fetchAll();
	}

	/**
	 * @param  string|DateTime $datetime
	 * @return DateTime
	 */
	protected function convertToDateTime($datetime): DateTime
	{
		if (is_string($datetime)) {
			$datetime = new DateTime($datetime);
		}

		return $datetime;
	}

	/**
	 * @return Row
	 */
	public function all()
	{
		return $this->getDatabase()
			->query('SELECT 	vis.id AS id,
								vis.guid AS guid,
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
								checked
						FROM kk_visitors AS vis
						LEFT JOIN kk_provinces AS provs ON vis.province = provs.id
						WHERE meeting = ? AND vis.deleted = ? ' . $this->buildSearchQuery() . '
						ORDER BY vis.id ASC',
						$this->getMeetingId(), '0')->fetchAll();
	}

	/**
	 * @param  array  $queryId
	 * @return string
	 */
	public function getSerializedMailAddress(array $queryId = [])
	{
		$recipientMailAddresses = '';

		$emails = $this->getDatabase()
			->table($this->getTable())
			->select('email')
			->where('id', $queryId)
			->group('email')
			->fetchAll();

		foreach($emails as $item){
			$recipientMailAddresses .= $item['email'] . ",\n";
		}

		$recipientMailAddresses = rtrim($recipientMailAddresses, "\n,");

		return $recipientMailAddresses;
	}

	/**
	 * @param  int  $id
	 * @return Visitor
	 */
	public function getBill($id)
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('bill')
			->where('id', $id)
			->fetch();
	}

	/**
	 * @param	int		ID of visitor
	 * @return	mixed	result
	 */
	public function findVisitorPrograms(int $visitorId)
	{
		return $this->getDatabase()
			->table('kk_visitor-program')
			->select('id, program')
			->where('visitor', $visitorId)
			->fetchAll();
	}

}
