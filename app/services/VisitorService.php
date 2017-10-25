<?php

namespace App\Services;

use DateTime;
use App\Models\VisitorModel;
use App\Models\MealModel;
use App\Models\BlockModel;
use App\Services\ProgramService;
use Nette\Utils\Strings;

class VisitorService
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
	 * @var ProgramService
	 */
	protected $programService;

	/**
	 * @param VisitorModel   $visitor
	 * @param MealModel      $meal
	 * @param BlockModel     $block
	 * @param ProgramService $program
	 */
	public function __construct(
		VisitorModel $visitor,
		MealModel $meal,
		BlockModel $block,
		ProgramService $program
	) {
		$this->setVisitorModel($visitor);
		$this->setMealModel($meal);
		$this->setBlockModel($block);
		$this->setProgramService($program);
	}

	/**
	 * @param  array   $data
	 * @return string
	 */
	public function create(array $data = [])
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
	public function update($id, array $data)
	{
		$visitor = $this->filterFields($data, $this->getVisitorModel()->getColumns());

		$visitor['birthday'] = $this->convertToDateTime($visitor['birthday']);

		$visitor['code'] = $this->calculateCode4Bank(
			$visitor['name'],
			$visitor['surname'],
			$visitor['birthday']->format('d. m. Y')
		);
		$meals = $this->filterFields($data, $this->getMealModel()->getColumns());
		$programs = $this->filterProgramFields($data);

		if(is_numeric($id)) {
			$id = $this->getVisitorModel()->modify($id, $visitor, $meals, $programs);
		} else {
			$id = $this->getVisitorModel()->modifyByGuid($id, $visitor, $meals, $programs);
		}

		return $id;
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
	 * @param  string $guid
	 * @return array
	 */
	public function findByGuid(string $guid): array
	{
		$visitor = $this->getVisitorModel()->findByGuid($guid);
		$meals = $this->getMealModel()->findByVisitorId($visitor->id);
		$programs = $this->getProgramService()->assembleFormPrograms($visitor->id);

		return array_merge($visitor->toArray(), $meals->toArray(), $programs);
	}

	/**
	 * @param  array  $data
	 * @param  array  $fields
	 * @return array
	 */
	protected function filterFields(array $data, array $fields)
	{
		return array_intersect_key($data, array_flip($fields));
	}

	/**
	 * @param  array $data
	 * @return array
	 */
	protected function filterProgramFields(array $data)
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
	protected function getBlockModel()
	{
		return $this->blockModel;
	}

	/**
	 * @param  BlockModel $model
	 * @return $this
	 */
	protected function setBlockModel(BlockModel $model)
	{
		$this->blockModel = $model;

		return $this;
	}

	/**
	 * @return MealModel
	 */
	protected function getMealModel()
	{
		return $this->mealModel;
	}

	/**
	 * @param  MealModel $model
	 * @return $this
	 */
	protected function setMealModel(MealModel $model)
	{
		$this->mealModel = $model;

		return $this;
	}

	/**
	 * @return VisitorModel
	 */
	protected function getVisitorModel()
	{
		return $this->visitorModel;
	}

	/**
	 * @param  VisitorModel $model
	 * @return VisitorService
	 */
	protected function setVisitorModel(VisitorModel $model): VisitorService
	{
		$this->visitorModel = $model;

		return $this;
	}

	/**
	 * @return ProgramService
	 */
	protected function getProgramService()
	{
		return $this->programService;
	}

	/**
	 * @param  ProgramService $service
	 * @return ProgramService
	 */
	protected function setProgramService(ProgramService $service): VisitorService
	{
		$this->programService = $service;

		return $this;
	}

}
