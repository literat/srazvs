<?php

namespace App\Components;

use App\Models\BlockModel;

class BlocksByDayControl extends BaseControl
{

	const TEMPLATE_NAME = 'BlocksByDay';

	/**
	 * @var ProgramsControl
	 */
	private $programs;

	/**
	 * @var BlockModel
	 */
	private $blockModel;

	/**
	 * @param BlockModel $model
	 */
	public function __construct(BlockModel $model, ProgramsControl $control)
	{
		$this->setBlockModel($model);
		$this->setProgramsControl($control);
	}

	/**
	 * @param  string $day
	 * @return void
	 */
	public function render($day)
	{
		$this->getBlockModel()->setMeetingId($this->getMeetingId());
		$blocks = $this->getBlockModel()->findByDay($day);

		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->blocks = $blocks;
		$template->render();
	}

	/**
	 * @return ProgramsControl
	 */
	protected function createComponentPrograms()
	{
		return $this->programs;
	}

	/**
	 * @param  ProgramsControl $control
	 * @return $this
	 */
	protected function setProgramsControl(ProgramsControl $control)
	{
		$this->programs = $control;

		return $this;
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel()
	{
		return  $this->blockModel;
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

}
