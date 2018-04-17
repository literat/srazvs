<?php

namespace App\Components;

use App\Models\ExportModel;

class RegistrationGraphControl extends BaseControl
{
	const TEMPLATE_NAME = 'RegistrationGraph';
	const GRAPH_WIDTH = 94;
	const GRAPH_HEIGHT_INIT = 0;
	const GRAPH_HEIGHT_STEP = 21.5;
	const GRAPH_HEIGHT_MIN = 310;

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

	public function render()
	{
		$exportModel = $this->getExportModel();

		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->imgDir = IMG_DIR;
		$template->graph = $exportModel->graph();
		$template->graphMax = $exportModel->graphMax();
		$template->graphWidth = static::GRAPH_WIDTH;
		$template->graphHeight = static::GRAPH_HEIGHT_INIT;
		$template->graphHeightStep = static::GRAPH_HEIGHT_STEP;
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
