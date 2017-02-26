<?php

namespace App\Components;

class ProgramOverviewControl extends BaseControl
{

	const TEMPLATE_NAME = 'ProgramOverview';

	/**
	 * @var BlocksByDayControl
	 */
	private $blocksByDay;

	/**
	 * @param BlocksByDayControl $control
	 */
	public function __construct(BlocksByDayControl $control)
	{
		$this->setBlocksBayControl($control);
	}

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
	protected function createComponentBlocksByDay()
	{
		return $this->blocksByDay->setMeetingId($this->getMeetingId());
	}

	/**
	 * @param  BlocksByDayControl $control
	 * @return $this
	 */
	protected function setBlocksBayControl(BlocksByDayControl $control)
	{
		$this->blocksByDay = $control;

		return $this;
	}

}
