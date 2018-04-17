<?php

namespace App\Models;

use DateTime;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

class VisitorModel extends BaseModel
{

	/**
	 * @var string
	 */
	public $search;

	/**
	 * @var MeetingModel
	 */
	public $meetingModel;

	/**
	 * @var MealModel
	 */
	public $mealModel;

	/**
	 * @var ProgramModel
	 */
	public $programModel;

	/**
	 * @var BlockModel
	 */
	public $blocksModel;

	/**
	 * @var int
	 */
	public $meetingPrice;

	/**
	 * @var array
	 */
	public $dbColumns = [];

	/**
	 * @var array
	 */
	public $formNames = [];

	/**
	 * @var string
	 */
	protected $table = 'kk_visitors';

	/**
	 * @var array
	 */
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

	/**
	 * @var int
	 */
	protected $meetingAdvance;

	public function __construct(
		MeetingModel $meetingModel,
		MealModel $mealModel,
		ProgramModel $programModel,
		BlockModel $blockModel,
		Context $database
	) {
		$this->meetingModel = $meetingModel;
		$this->meetingPrice = $this->meetingModel->getPrice('cost');
		$this->meetingAdvance = $this->meetingModel->getPrice('advance');
		$this->mealModel = $mealModel;
		$this->programModel = $programModel;
		$this->blocksModel = $blockModel;
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

	public function getColumns(): array
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
	 * Create a new visitor.
	 *
	 * @return string
	 */
	public function assemble(array $dbData, $mealsData, $programsData, $returnGuid = false)
	{
		$return = true;

		if (!$dbData['province']) {
			$dbData['province'] = 0;
		}

		$dbData['birthday'] = new \DateTime($dbData['birthday']);
		$dbData['reg_daytime'] = (new \DateTime())->format('Y-m-d H:i:s');
		$dbData['guid'] = md5(uniqid());

		$visitorId = $this->database
			->table($this->getTable())
			->insert($dbData)->id;

		// visitor's id is empty and i must add one
		$mealsData['visitor'] = $visitorId;

		if ($visitorId) {
			// gets data from database
			$programBlocks = $this->blocksModel->getProgramBlocks($dbData['meeting']);

			foreach ($programBlocks as $dbBlocksData) {
				$bindingsData = [
					'visitor' => $visitorId,
					'program' => $programsData[$dbBlocksData['id']],
				];
				// insert into binding table
				// var programsData contains requested values in format block-id => program-id
				$bindingsData['guid'] = md5(uniqid());
				$resultBinding = $this->database->query('INSERT INTO `kk_visitor-program`', $bindingsData);

				if (!$resultBinding) {
					throw new \Exception('Error while binding visitor`s program');
				}
			}

			if ($return) {
				// create meals for visitor
				if (!$return = $this->mealModel->create($mealsData)) {
					throw new \Exception('Error while creating meals');
				}
			}
		} else {
			throw new \Exception('Error while creating visitor');
		}

		//return $return;
		if ($returnGuid) {
			return $dbData['guid'];
		} else {
			return $visitorId;
		}
	}

	/**
	 * Modify a visitor.
	 *
	 * @param  int   $visitorId ID of a visitor
	 * @param  array $visitor   Visitor's database data
	 * @param  array $meals     Data of meals
	 * @param  array $programs  Program's data
	 * @return mixed TRUE or array of errors
	 */
	public function modify(int $visitorId, array $visitor, array $meals, array $programs)
	{
		$visitor['birthday'] = $this->convertToDateTime($visitor['birthday']);

		$result = $this->getDatabase()
			->table($this->getTable())
			->where('id', $visitorId)
			->update($visitor);

		// change meals
		$result = $this->mealModel->updateOrCreate($visitorId, $meals);

		// gets data from database
		$programBlocks = $this->blocksModel->getProgramBlocks($visitor['meeting']);

		// get program of visitor
		$oldPrograms = $this->findVisitorPrograms($visitorId);

		// update old data to new existing
		foreach ($programBlocks as $programBlock) {
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
	 * Modify a visitor.
	 *
	 * @param  int   $guid     ID of a visitor
	 * @param  array $visitor  Visitor's database data
	 * @param  array $meals    Data of meals
	 * @param  array $programs Program's data
	 * @return mixed TRUE or array of errors
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
		$result = $this->mealModel->updateOrCreate($visitor->id, $meals);

		// gets data from database
		$programBlocks = $this->blocksModel->getProgramBlocks($visitor['meeting']);

		// get program of visitor
		$oldPrograms = $this->findVisitorPrograms($visitor->id);

		// update old data to new existing
		foreach ($programBlocks as $programBlock) {
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
	 * @param  int   $visitorId
	 * @param  int   $oldProgramId
	 * @param  int   $newProgramId
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

		if (!$result) {
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
	 * Delete one or multiple record/s.
	 *
	 * @param  int     $id ID/s of record
	 * @return boolean
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
	 * Set as checked one or multiple record/s.
	 *
	 * @param  int     $id    ID/s of record
	 * @param  int     $value 0 | 1
	 * @return boolean
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
	 * Get count of visitors.
	 *
	 * @return integer
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
	 * @return self
	 */
	public function setSearch(string $search): self
	{
		$this->search = $search;

		return $this;
	}

	/**
	 * @return string
	 */
	protected function getSearch(): string
	{
		return $this->search;
	}

	/**
	 * @return string
	 */
	protected function buildSearchQuery(): string
	{
		$search = $this->getSearch();

		$query = '';
		if ($search) {
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
	 * Modify the visitor's bill.
	 *
	 * @param  int    $ids  ID/s of visitor
	 * @param  string $type type of payment (pay | advance)
	 * @return string error message or true
	 */
	public function payCharge($ids, $type)
	{
		$bill = $this->getBill($ids)['bill'];
		$cost = $this->meetingModel->getPrice('cost');

		if ($bill < $cost) {
			$newBill = ['bill' => $this->meetingModel->getPrice($type)];
			$payResult = $this->getDatabase()
				->table($this->getTable())
				->where('id', $ids)
				->update($newBill);

			return $payResult;
		} else {
			throw new \Exception('Charge already paid!');
		}
	}

	/**
	 * Get recipients by ids.
	 *
	 * @param  mixed $ids ID of visitor
	 * @return mixed
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

	public function all(): ActiveRow
	{
		return $this->getDatabase()
			->query(
				'SELECT vis.id AS id,
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
				$this->getMeetingId(),
				'0'
			)->fetchAll();
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

		foreach ($emails as $item) {
			$recipientMailAddresses .= $item['email'] . ",\n";
		}

		$recipientMailAddresses = rtrim($recipientMailAddresses, "\n,");

		return $recipientMailAddresses;
	}

	public function getBill(int $id): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->select('bill')
			->where('id', $id)
			->fetch();
	}

	/**
	 * @param  int   $visitorId ID of visitor
	 * @return mixed
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
