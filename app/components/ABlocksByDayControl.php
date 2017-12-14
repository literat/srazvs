<?php

namespace App\Components;

use App\Models\BlockModel;

abstract class ABlocksByDayControl extends BaseControl implements IBlocksByDayControl
{

	const TEMPLATE_NAME = '';

	/**
	 * @var IProgramsControl
	 */
	private $programsControl;

	/**
	 * @var BlockModel
	 */
	private $blockModel;

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
		return $this->programsControl;
	}

	/**
	 * @param  IProgramsControl $control
	 * @return $this
	 */
	protected function setProgramsControl(IProgramsControl $control): IBlocksByDayControl
	{
		$this->programsControl = $control;

		return $this;
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel(): BlockModel
	{
		return  $this->blockModel;
	}

	/**
	 * @param  BlockModel $model
	 * @return $this
	 */
	protected function setBlockModel(BlockModel $model): IBlocksByDayControl
	{
		$this->blockModel = $model;

		return $this;
	}

}
