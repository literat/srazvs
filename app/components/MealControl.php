<?php

namespace App\Components;

use App\Models\ExportModel;

class MealControl extends BaseControl
{
	const TEMPLATE_NAME = 'Meal';

	/**
	 * @var ExportModel
	 */
	private $exportModel;

	/**
	 * @param ExportModel $model
	 */
	public function __construct(ExportModel $model)
	{
		$this->setExportModel($model);
	}

	/**
	 * @param  string $mealColumn
	 * @param  string $mealName
	 * @return void
	 */
	public function render($mealColumn, $mealName)
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->mealCount = $this->getExportModel()->getMealCount($mealColumn);
		$template->mealName = $mealName;
		$template->render();
	}

	/**
	 * @return ExportModel
	 */
	protected function getExportModel()
	{
		return $this->exportModel->setMeetingId($this->getMeetingId());
	}

	/**
	 * @param  ExportModel $model
	 * @return self
	 */
	protected function setExportModel(ExportModel $model): self
	{
		$this->exportModel = $model;

		return $this;
	}
}
