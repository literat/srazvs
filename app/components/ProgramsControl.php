<?php

namespace App\Components;

use App\Models\ProgramModel;

class ProgramsControl extends BaseControl
{

	const TEMPLATE_NAME = 'Programs';

	/**
	 * @var ProgramModel
	 */
	private $programModel;

	/**
	 * @param ProgramModel $model
	 */
	public function __construct(ProgramModel $model)
	{
		$this->setProgramModel($model);
	}

	/**
	 * @param  integer $blockId
	 * @return void
	 */
	public function render($blockId)
	{
		$programs = $this->getProgramModel()->findByBlockId($blockId);

		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->programs = $programs;
		$template->render();
	}

	/**
	 * @return ProgramModel
	 */
	protected function getProgramModel()
	{
		return $this->programModel->setMeetingId($this->getMeetingId());
	}

	/**
	 * @param  ProgramModel $model
	 * @return $this
	 */
	protected function setProgramModel(ProgramModel $model)
	{
		$this->programModel = $model;

		return $this;
	}

}
