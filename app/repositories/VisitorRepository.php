<?php

namespace App\Repositories;

use App\Models\BlockModel;
use App\Models\MealModel;
use App\Models\VisitorModel;
use DateTime;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

class VisitorRepository
{

	/**
	 * @var VisitorModel
	 */
	protected $visitorModel;

	/**
	 * @var MealModel
	 */
	protected $mealModel;

	/**
	 * @var BlockModel
	 */
	protected $blockModel;

	/**
	 * @var ProgramRepository
	 */
	protected $programRepository;

	/**
	 * @param VisitorModel      $visitor
	 * @param MealModel         $meal
	 * @param BlockModel        $block
	 * @param ProgramRepository $program
	 */
	public function __construct(
		VisitorModel $visitor,
		MealModel $meal,
		BlockModel $block,
		ProgramRepository $program
	) {
		$this->setVisitorModel($visitor);
		$this->setMealModel($meal);
		$this->setBlockModel($block);
		$this->setProgramRepository($program);
	}

	/**
	 * @param int  $meetingId
	 * @return self
	 */
	public function setMeeting(int $meetingId): self
	{
		$this->getVisitorModel()->setMeetingId($meetingId);

		return $this;
	}

	/**
	 * @param  int $id
	 * @return boolean
	 */
	public function setChecked(int $id): bool
	{
		return $this->getVisitorModel()->checked($id, '1');
	}

	/**
	 * @param  int $id
	 * @return boolean
	 */
	public function setUnchecked(int $id): bool
	{
		return $this->getVisitorModel()->checked($id, '0');
	}

	/**
	 * Return visitor by id
	 *
	 * @param  int    $id
	 * @return ActiveRow
	 */
	public function findById($id)
	{
		return $this->getVisitorModel()->find($id);
	}

	/**
	 * @param  int $id
	 * @return ArrayHash
	 */
	public function findExpandedById(int $id): ArrayHash
	{
		$visitor = $this->findById($id);
		$meals = $this->getMealModel()->findByVisitorId($id);
		$programs = $this->assembleFormPrograms($id);

		return ArrayHash::from(
			array_merge(
				$visitor->toArray(),
				$meals,
				$programs
			)
		);
	}

	/**
	 * Return visitor by guid
	 *
	 * @param  string  $guid
	 * @return Nette\Database\Table\ActiveRow|bool
	 */
	public function findByGuid($guid)
	{
		return $this->getVisitorModel()->findBy('guid', $guid);
	}

	/**
	 * @param  string $guid
	 * @return array
	 */
	public function findExpandedByGuid(string $guid): array
	{
		$visitor = $this->getVisitorModel()->findByGuid($guid);
		$meals = $this->getMealModel()->findByVisitorId($visitor->id);
		$programs = $this->assembleFormPrograms($visitor->id);

		return array_merge($visitor->toArray(), $meals, $programs);
	}

	/**
	 * @param  string $value
	 * @return array
	 */
	public function findBySearch(string $value = ''): array
	{
		return $this->getVisitorModel()->setSearch($value)->all();
	}

	/**
	 * @param  int  $id
	 * @return array
	 */
	public function findRecipients($id): array
	{
		return $this->getVisitorModel()->getRecipients($id);
	}

	/**
	 * @param  array   $data
	 * @return string
	 */
	public function create($data)
	{
		$visitor = $this->filterFields($data, $this->getVisitorModel()->getColumns());
		$visitor['code'] = $this->calculateCode4Bank(
			$visitor['name'],
			$visitor['surname'],
			$visitor['birthday']->format('d. m. Y')
		);
		$meals = $this->filterFields($data, $this->getMealModel()->getColumns());
		$programs = $this->filterProgramFields($data);

		$guid = $this->getVisitorModel()->assemble($visitor, $meals, $programs, true);

		return $guid;
	}

	/**
	 * @param  integer $id
	 * @param  array   $data
	 * @return integer
	 */
	public function update($id, $values)
	{
		$visitor = $this->filterFields($values, $this->getVisitorModel()->getColumns());

		$visitor['birthday'] = $this->convertToDateTime($visitor['birthday']);

		$visitor['code'] = $this->calculateCode4Bank(
			$visitor['name'],
			$visitor['surname'],
			$visitor['birthday']->format('d. m. Y')
		);
		$meals = $this->filterFields($values, $this->getMealModel()->getColumns());
		$programs = $this->filterProgramFields($values);

		$id = $this->getVisitorModel()->modify($id, $visitor, $meals, $programs);

		return $id;
	}

	/**
	 * @param  integer $id
	 * @param  array   $data
	 * @return integer
	 */
	public function updateByGuid($guid, $values)
	{
		$visitor = $this->filterFields($values, $this->getVisitorModel()->getColumns());

		$visitor['birthday'] = $this->convertToDateTime($visitor['birthday']);

		$visitor['code'] = $this->calculateCode4Bank(
			$visitor['name'],
			$visitor['surname'],
			$visitor['birthday']->format('d. m. Y')
		);

		$meals = $this->filterFields($values, $this->getMealModel()->getColumns());
		$programs = $this->filterProgramFields($values);

		$guid = $this->getVisitorModel()->modifyByGuid($guid, $visitor, $meals, $programs);

		return $guid;
	}

	/**
	 * @param  integer $id
	 * @return boolean
	 */
	public function delete($id)
	{
		return $this->getVisitorModel()->delete($id);
	}

	/**
	 * Counts visitors
	 *
	 * @return int
	 */
	public function count(): int
	{
		return $this->getVisitorModel()->getCount();
	}

	/**
	 * @param  int $id
	 * @return string
	 * @throws \Exception
	 */
	public function payCostCharge($id)
	{
		return $this->getVisitorModel()->payCharge($id, 'cost');
	}

	/**
	 * @param  int $id
	 * @return string
	 * @throws \Exception
	 */
	public function payAdvanceCharge($id)
	{
		return $this->getVisitorModel()->payCharge($id, 'advance');
	}

	/**
	 * @param  string $name
	 * @param  string $surname
	 * @param  string $birthday
	 * @return string
	 */
	public function calculateCode4Bank(string $name, string $surname, string $birthday): string
	{
		return Strings::substring($name, 0, 1)
			. Strings::substring($surname, 0, 1)
			. Strings::substring($birthday, -2);
	}

	/**
	 * @param  int    $visitorId
	 * @return array
	 */
	public function assembleFormPrograms(int $visitorId): array
	{
		$visitorPrograms = $this->getVisitorModel()->findVisitorPrograms($visitorId);

		$formPrograms = [];

		foreach ($visitorPrograms as $visitorProgram) {
			if($visitorProgram->program !== 0) {
				$program = $this->getProgramRepository()->find($visitorProgram->program);
				$formPrograms['blck_' . $program->block] = $visitorProgram->program;
			}
		}

		return $formPrograms;
	}

	/**
	 * @param  array  $data
	 * @param  array  $fields
	 * @return array
	 */
	protected function filterFields($data, array $fields)
	{
		return array_intersect_key((array) $data, array_flip($fields));
	}

	/**
	 * @param  array $data
	 * @return array
	 */
	protected function filterProgramFields($data)
	{
		$blocks = $this->getBlockModel()->idsFromCurrentMeeting($data['meeting']);

		$programs = array_map(function($block) use ($data) {
			if(!array_key_exists('blck_' . $block['id'], $data)) {
				return 0;
			}

			return $data['blck_' . $block['id']];
		}, $blocks);

		return $programs;
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
	 * @return BlockModel
	 */
	protected function getBlockModel(): BlockModel
	{
		return $this->blockModel;
	}

	/**
	 * @param  BlockModel $model
	 * @return $this
	 */
	protected function setBlockModel(BlockModel $model): self
	{
		$this->blockModel = $model;

		return $this;
	}

	/**
	 * @return MealModel
	 */
	protected function getMealModel(): MealModel
	{
		return $this->mealModel;
	}

	/**
	 * @param  MealModel $model
	 * @return $this
	 */
	protected function setMealModel(MealModel $model): self
	{
		$this->mealModel = $model;

		return $this;
	}

	/**
	 * @return VisitorModel
	 */
	protected function getVisitorModel(): VisitorModel
	{
		return $this->visitorModel;
	}

	/**
	 * @param  VisitorModel $model
	 * @return self
	 */
	protected function setVisitorModel(VisitorModel $model): self
	{
		$this->visitorModel = $model;

		return $this;
	}

	/**
	 * @return ProgramRepository
	 */
	protected function getProgramRepository()
	{
		return $this->programRepository;
	}

	/**
	 * @param  ProgramRepository $repository
	 * @return self
	 */
	protected function setProgramRepository(ProgramRepository $repository): self
	{
		$this->programRepository = $repository;

		return $this;
	}

}
