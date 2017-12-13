<?php

namespace App\Components;

use App\Components\ProgramsControl;

abstract class AProgramOverviewControl extends BaseControl implements IProgramOverviewControl
{

	const TEMPLATE_NAME = '';

	/**
	 * @var BlocksByDayControl
	 */
	private $blocksByDay;

	/**
	 * @return void
	 */
	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->weekendDays = [
			'pátek',
			'sobota',
			'neděle'
		];
		$template->render();
	}

	/**
	 * @return BlocksByDayControl
	 */
	protected function createComponentBlocksByDay(): IBlocksByDayControl
	{
		return $this->blocksByDay->setMeetingId($this->getMeetingId());
	}

	/**
	 * @param  BlocksByDayControl $control
	 * @return self
	 */
	protected function setBlocksBayControl(IBlocksByDayControl $control): self
	{
		$this->blocksByDay = $control;

		return $this;
	}

}
