<?php

namespace App\Services;

use App\Models\VisitorModel;
use App\Models\MealModel;
use App\Models\BlockModel;

class VisitorService extends BaseService
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
	 * @param VisitorModel $visitor
	 * @param MealModel    $meal
	 * @param BlockModel   $block
	 * @param Emailer      $emailer
	 */
	public function __construct(
		VisitorModel $visitor,
		MealModel $meal,
		BlockModel $block
	) {
		$this->setVisitorModel($visitor);
		$this->setMealModel($meal);
		$this->setBlockModel($block);
	}

	/**
	 * @param  array   $data
	 * @return string
	 */
	public function create(array $data = [])
	{
		$visitor = $this->filterFields($data, $this->getVisitorModel()->getColumns());
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
	 * @return VisitorModel
	 */
	protected function setVisitorModel(VisitorModel $model)
	{
		$this->visitorModel = $model;

		return $this;
	}

}
