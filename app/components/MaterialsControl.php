<?php

namespace App\Components;

use App\Models\ExportModel;

class MaterialsControl extends BaseControl
{

	const TEMPLATE_NAME = 'Materials';

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
	 * @return void
	 */
	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->materials = $this->getExportModel()->materials();
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
	 * @return $this
	 */
	protected function setExportModel(ExportModel $model)
	{
		$this->exportModel = $model;

		return $this;
	}

}
